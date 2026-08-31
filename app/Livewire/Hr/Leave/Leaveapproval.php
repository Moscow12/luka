<?php

namespace App\Livewire\Hr\Leave;

use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\Employee;
use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Leaveapproval extends Component
{
    use WithPagination;

    public $statusFilter = 'Awaiting';

    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $selectedLeave = null;

    public $showModal = false;

    public $actionType = '';

    public $comments = '';

    public $userApprovalLevels = [];

    public $viewedLeave = null;

    public $showHistoryModal = false;

    // Datatable controls
    public $perPage = 10;

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    protected $paginationTheme = 'bootstrap';

    protected array $sortable = [
        'created_at' => 'created_at',
        'start_date' => 'start_date',
        'end_date' => 'end_date',
        'days' => 'days',
        'status' => 'status',
    ];

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, $this->sortable)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Default to the current month's leave requests.
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->endOfMonth()->toDateString();

        $this->loadUserApprovalLevels();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function clearDateFilter()
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function loadUserApprovalLevels()
    {
        $employee = Employee::where('user_id', Auth::id())->first();

        if ($employee) {
            // Each mapping grants a level plus the set of departments it covers.
            // An empty department set means the mapping applies company-wide
            // (e.g. level 3/4 approvers who sign off for every department).
            $this->userApprovalLevels = approvalleveltoemployee::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->with('departments:id')
                ->get()
                ->map(fn ($mapping) => [
                    'approval_level_id' => $mapping->approval_level_id,
                    'department_ids' => $mapping->departments->pluck('id')->toArray(),
                ])
                ->toArray();
        }
    }

    /**
     * Whether the current user is allowed to act on the given approval level for a leave
     * request raised by someone in $departmentId. A mapping with no departments picked
     * applies company-wide (level 3/4); otherwise it only covers its listed departments
     * (level 1 = one department, level 2 = several).
     */
    protected function userCanApproveLevel(?string $levelId, ?string $departmentId): bool
    {
        if (! $levelId) {
            return false;
        }

        foreach ($this->userApprovalLevels as $mapping) {
            if ($mapping['approval_level_id'] !== $levelId) {
                continue;
            }

            if (empty($mapping['department_ids']) || in_array($departmentId, $mapping['department_ids'], true)) {
                return true;
            }
        }

        return false;
    }

    public function openApproveModal($leaveId)
    {
        $this->selectedLeave = Employeeleaves::with(['employee', 'leave'])->findOrFail($leaveId);
        $this->actionType = 'approve';
        $this->comments = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openRejectModal($leaveId)
    {
        $this->selectedLeave = Employeeleaves::with(['employee', 'leave'])->findOrFail($leaveId);
        $this->actionType = 'reject';
        $this->comments = '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function viewApprovalHistory($leaveId)
    {
        $this->viewedLeave = Employeeleaves::with([
            'employee',
            'leave',
            'approvalnote.approval_level',
            'approvalnote.approver',
        ])->findOrFail($leaveId);
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->viewedLeave = null;
    }

    /**
     * Approve or reject the selected leave. A comment is required either way.
     */
    public function submitDecision()
    {
        if (! in_array($this->actionType, ['approve', 'reject'])) {
            session()->flash('error', 'No action selected.');

            return;
        }

        $verb = $this->actionType === 'approve' ? 'approval' : 'rejection';

        $this->validate([
            'comments' => 'required|string|min:5|max:500',
        ], [
            'comments.required' => "Please provide a comment for the {$verb}.",
            'comments.min' => 'Comment must be at least 5 characters.',
            'comments.max' => 'Comment cannot exceed 500 characters.',
        ]);

        if (! $this->selectedLeave) {
            session()->flash('error', 'No leave request selected.');

            return;
        }

        $currentLevel = $this->getCurrentApprovalLevel($this->selectedLeave);

        if (! $currentLevel) {
            session()->flash('error', 'No approval level found or leave already processed.');
            $this->closeModal();

            return;
        }

        if (! $this->userCanApproveLevel($currentLevel->id, $this->selectedLeave->employee->department_id ?? null)) {
            session()->flash('error', "You do not have permission to {$this->actionType} at this level.");
            $this->closeModal();

            return;
        }

        $decision = $this->actionType === 'approve' ? 'approved' : 'rejected';

        // Record the decision with the approver's comment.
        leaverequestapproval::create([
            'leave_request_id' => $this->selectedLeave->id,
            'approval_level_id' => $currentLevel->id,
            'approver_id' => Auth::id(),
            'status' => $decision,
            'comments' => $this->comments,
            'approved_at' => now(),
        ]);

        // Update leave status.
        $newStatus = $this->determineLeaveStatus($this->selectedLeave, $decision);
        $this->selectedLeave->update(['status' => $newStatus]);

        session()->flash('success', $this->actionType === 'approve'
            ? 'Leave request has been approved successfully!'
            : 'Leave request has been rejected.');

        $this->closeModal();
        $this->dispatch('refreshNotifications');
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedLeave = null;
        $this->actionType = '';
        $this->comments = '';
        $this->resetValidation();
    }

    /**
     * The ordered chain of approval levels (level 1, 2, 3, 4...) configured for Leave.
     */
    protected function getApprovalLevelsForLeave(): \Illuminate\Support\Collection
    {
        return approvalleveltodocument::where('document_type', 'Leave')
            ->where('is_active', true)
            ->with('approval_level')
            ->get()
            ->pluck('approval_level')
            ->filter()
            ->sortBy('level_order')
            ->values();
    }

    /**
     * The next level in the chain awaiting a decision, walking level 1 -> 2 -> 3 -> 4...
     * in order. Returns null once the leave is rejected or every level has approved.
     */
    public function getCurrentApprovalLevel(Employeeleaves $leave)
    {
        $approvalLevels = $this->getApprovalLevelsForLeave();

        if ($approvalLevels->isEmpty()) {
            return null;
        }

        $existingApprovals = leaverequestapproval::where('leave_request_id', $leave->id)
            ->with('approval_level')
            ->get();

        // If any level rejected, the chain stops (already rejected).
        if ($existingApprovals->where('status', 'rejected')->isNotEmpty()) {
            return null;
        }

        // Walk the chain in order; the first level without an approval is next up.
        foreach ($approvalLevels as $level) {
            $alreadyApproved = $existingApprovals->where('approval_level_id', $level->id)
                ->where('status', 'approved')
                ->isNotEmpty();

            if (! $alreadyApproved) {
                return $level;
            }
        }

        return null; // All levels approved
    }

    /**
     * Move the leave to the next stage once a level decides. Approving a level advances
     * the leave to whichever level comes next in the chain (or fully "approved" once the
     * last configured level signs off); rejecting at any level stops the chain.
     */
    public function determineLeaveStatus(Employeeleaves $leave, string $approvalStatus): string
    {
        if ($approvalStatus === 'rejected') {
            return 'Rejected';
        }

        $approvalLevels = $this->getApprovalLevelsForLeave();

        if ($approvalLevels->isEmpty()) {
            return 'approved';
        }

        // Re-evaluate the chain now that the latest decision has been recorded: if there's
        // still a next level waiting, the leave is Active/Awaiting; otherwise it's fully approved.
        $nextLevel = $this->getCurrentApprovalLevel($leave);

        if (! $nextLevel) {
            return 'approved';
        }

        return $nextLevel->id === $approvalLevels->first()->id ? 'Awaiting' : 'Active';
    }

    public function render()
    {
        $canApprove = ! empty($this->userApprovalLevels);
        $pendingLeaves = collect();
        $myPendingCount = 0;

        if ($canApprove) {
            // Count leaves currently awaiting THIS user's approval level (ignores search/date filters
            // so the badge reflects the true outstanding workload).
            $myPendingCount = $this->countLeavesPendingForUser();

            $sortColumn = $this->sortable[$this->sortField] ?? 'created_at';
            $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

            $leavesQuery = Employeeleaves::with(['employee', 'leave', 'approvalnote.approval_level', 'approvalnote.approver'])
                ->orderBy($sortColumn, $sortDirection);

            // Filter by employee name / number
            if (trim($this->search) !== '') {
                $term = '%'.trim($this->search).'%';
                $leavesQuery->whereHas('employee', function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('employee_no', 'like', $term)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]);
                });
            }

            // Filter by date range — when the leave request was submitted (created_at).
            if ($this->dateFrom) {
                $leavesQuery->whereDate('created_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $leavesQuery->whereDate('created_at', '<=', $this->dateTo);
            }

            // Filter by status
            if ($this->statusFilter === 'Awaiting') {
                $leavesQuery->where(function ($q) {
                    $q->whereRaw('LOWER(status) IN (?, ?, ?)', ['awaiting', 'pending', 'active']);
                });
            } elseif ($this->statusFilter === 'Active') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['active']);
            } elseif ($this->statusFilter === 'Approved') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['approved']);
            } elseif ($this->statusFilter === 'Rejected') {
                $leavesQuery->whereRaw('LOWER(status) = ?', ['rejected']);
            }

            $perPage = max(5, (int) $this->perPage);

            // For the Pending tab, restrict the visible list to leaves whose NEXT approval level
            // is one of the user's assigned levels.
            if ($this->statusFilter === 'Awaiting') {
                $candidates = $leavesQuery->get()->filter(function ($leave) {
                    $next = $this->getCurrentApprovalLevel($leave);
                    if (! $next) {
                        return false;
                    }
                    $leave->nextApprovalLevel = $next;
                    $leave->canUserApprove = $this->userCanApproveLevel($next->id, $leave->employee->department_id ?? null);

                    return $leave->canUserApprove;
                })->values();

                $pendingLeaves = $this->paginateCollection($candidates, $perPage);
            } else {
                $pendingLeaves = $leavesQuery->paginate($perPage);

                $pendingLeaves->getCollection()->transform(function ($leave) {
                    $leave->canUserApprove = false;
                    $leave->nextApprovalLevel = $this->getCurrentApprovalLevel($leave);

                    if ($leave->nextApprovalLevel) {
                        $leave->canUserApprove = $this->userCanApproveLevel($leave->nextApprovalLevel->id, $leave->employee->department_id ?? null);
                    }

                    return $leave;
                });
            }
        }

        return view('livewire.hr.leave.leaveapproval', [
            'pendingLeaves' => $pendingLeaves,
            'canApprove' => $canApprove,
            'myPendingCount' => $myPendingCount,
        ]);
    }

    protected function countLeavesPendingForUser(): int
    {
        return Employeeleaves::whereRaw('LOWER(status) IN (?, ?, ?)', ['awaiting', 'pending', 'active'])
            ->with('employee')
            ->get()
            ->filter(function ($leave) {
                $next = $this->getCurrentApprovalLevel($leave);

                return $next && $this->userCanApproveLevel($next->id, $leave->employee->department_id ?? null);
            })
            ->count();
    }

    protected function paginateCollection(\Illuminate\Support\Collection $items, int $perPage): \Illuminate\Pagination\LengthAwarePaginator
    {
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
