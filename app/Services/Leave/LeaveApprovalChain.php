<?php

namespace App\Services\Leave;

use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The leave approval chain (level 1 → 2 → 3 …), shared by the Leave Approval page and
 * the notification bell so both always agree on who must act next.
 *
 * Several levels can share one level_order; each is scoped to departments through
 * approval_mapping_departments, and a mapping with no departments applies company-wide.
 */
class LeaveApprovalChain
{
    public const PENDING_STATUSES = ['awaiting', 'pending', 'active'];

    protected ?Collection $levelGroups = null;

    protected ?Collection $mappings = null;

    /**
     * Leave approval levels grouped by level_order, lowest first.
     */
    public function levelGroups(): Collection
    {
        return $this->levelGroups ??= approvalleveltodocument::where('document_type', 'Leave')
            ->where('is_active', true)
            ->with('approval_level')
            ->get()
            ->pluck('approval_level')
            ->filter()
            ->groupBy('level_order')
            ->sortKeys()
            ->values();
    }

    /** All active approver mappings, loaded once per request. */
    protected function mappings(): Collection
    {
        return $this->mappings ??= approvalleveltoemployee::where('is_active', true)
            ->with(['employee', 'departments:id'])
            ->get();
    }

    /**
     * Employees assigned to $levelId whose department coverage includes $departmentId.
     */
    public function approversForLevel(?string $levelId, ?string $departmentId): Collection
    {
        if (! $levelId) {
            return collect();
        }

        return $this->mappings()
            ->where('approval_level_id', $levelId)
            ->filter(fn ($mapping) => $mapping->appliesToDepartment($departmentId))
            ->map(fn ($mapping) => $mapping->employee)
            ->filter()
            ->values();
    }

    /**
     * Among levels sharing one order, those that actually have an approver for $departmentId.
     */
    public function levelsForDepartment(Collection $levelGroup, ?string $departmentId): Collection
    {
        return $levelGroup->filter(
            fn ($level) => $this->approversForLevel($level->id, $departmentId)->isNotEmpty()
        )->values();
    }

    /**
     * The next level awaiting a decision, or null once rejected or fully approved.
     * If no level at an order covers the requester's department, the leave is stuck there.
     */
    public function nextLevel(Employeeleaves $leave)
    {
        $levelGroups = $this->levelGroups();
        if ($levelGroups->isEmpty()) {
            return null;
        }

        $departmentId = $leave->employee->department_id ?? null;
        $decisions = $leave->relationLoaded('approvalnote')
            ? $leave->approvalnote
            : leaverequestapproval::where('leave_request_id', $leave->id)->get();

        if ($decisions->where('status', 'rejected')->isNotEmpty()) {
            return null;
        }

        $approvedLevelIds = $decisions->where('status', 'approved')->pluck('approval_level_id');

        foreach ($levelGroups as $levelGroup) {
            $applicable = $this->levelsForDepartment($levelGroup, $departmentId);

            if ($applicable->isEmpty()) {
                $alreadyApproved = $levelGroup->contains(fn ($level) => $approvedLevelIds->contains($level->id));

                return $alreadyApproved ? null : $levelGroup->first();
            }

            if (! $applicable->contains(fn ($level) => $approvedLevelIds->contains($level->id))) {
                return $applicable->first();
            }
        }

        return null;
    }

    /**
     * Status after a decision: next level pending → Awaiting/Active, none left → approved.
     */
    public function statusAfterDecision(Employeeleaves $leave, string $decision): string
    {
        if ($decision === 'rejected') {
            return 'Rejected';
        }

        // The decision was just recorded: never reuse a previously loaded list of decisions
        $leave->unsetRelation('approvalnote');

        $levelGroups = $this->levelGroups();
        $next = $levelGroups->isEmpty() ? null : $this->nextLevel($leave);

        if (! $next) {
            return 'approved';
        }

        return $levelGroups->first()->contains(fn ($level) => $level->id === $next->id) ? 'Awaiting' : 'Active';
    }

    /**
     * The user's approver mappings as [['approval_level_id' => …, 'department_ids' => […]], …].
     *
     * @return array<int, array{approval_level_id: string, department_ids: array<int, string>}>
     */
    public function mappingsFor(User $user): array
    {
        $employee = Employee::where('user_id', $user->id)->first();
        if (! $employee) {
            return [];
        }

        return $this->mappings()
            ->where('employee_id', $employee->id)
            ->map(fn ($mapping) => [
                'approval_level_id' => $mapping->approval_level_id,
                'department_ids' => $mapping->departments->pluck('id')->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Whether a set of mappings may act on $levelId for a requester in $departmentId.
     */
    public static function mappingsCover(array $mappings, ?string $levelId, ?string $departmentId): bool
    {
        if (! $levelId) {
            return false;
        }

        foreach ($mappings as $mapping) {
            if ($mapping['approval_level_id'] === $levelId
                && (empty($mapping['department_ids']) || in_array($departmentId, $mapping['department_ids'], true))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Leave requests whose next level is one the user approves, newest first.
     * Each returned leave carries ->nextApprovalLevel.
     */
    public function pendingFor(User $user): Collection
    {
        $mappings = $this->mappingsFor($user);
        if ($mappings === []) {
            return collect();
        }

        return Employeeleaves::with(['employee', 'leave', 'approvalnote'])
            ->whereRaw('LOWER(status) IN (?, ?, ?)', self::PENDING_STATUSES)
            ->orderByDesc('created_at')
            ->get()
            ->filter(function (Employeeleaves $leave) use ($mappings) {
                $next = $this->nextLevel($leave);
                $leave->nextApprovalLevel = $next;

                return $next && self::mappingsCover($mappings, $next->id, $leave->employee->department_id ?? null);
            })
            ->values();
    }
}
