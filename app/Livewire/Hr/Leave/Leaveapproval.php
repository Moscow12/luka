<?php

namespace App\Livewire\Hr\Leave;

use App\Models\Employeeleaves;
use App\Models\leaverequestapproval;
use App\Services\Leave\LeaveApprovalChain;
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
        $this->userApprovalLevels = $this->chain()->mappingsFor(Auth::user());
    }

    /**
     * Whether the current user may act on $levelId for a leave raised in $departmentId.
     */
    protected function userCanApproveLevel(?string $levelId, ?string $departmentId): bool
    {
        return LeaveApprovalChain::mappingsCover($this->userApprovalLevels, $levelId, $departmentId);
    }

    protected function resolveApproversForLevel(?string $levelId, ?string $departmentId): \Illuminate\Support\Collection
    {
        return $this->chain()->approversForLevel($levelId, $departmentId);
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
     * The next level awaiting a decision (see LeaveApprovalChain::nextLevel()).
     */
    public function getCurrentApprovalLevel(Employeeleaves $leave)
    {
        return $this->chain()->nextLevel($leave);
    }

    public function determineLeaveStatus(Employeeleaves $leave, string $approvalStatus): string
    {
        return $this->chain()->statusAfterDecision($leave, $approvalStatus);
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
                    $departmentId = $leave->employee->department_id ?? null;
                    $leave->nextApprovalLevel = $next;
                    $leave->nextApprovers = $this->resolveApproversForLevel($next->id, $departmentId);
                    $leave->canUserApprove = $this->userCanApproveLevel($next->id, $departmentId);

                    return $leave->canUserApprove;
                })->values();

                $pendingLeaves = $this->paginateCollection($candidates, $perPage);
            } else {
                $pendingLeaves = $leavesQuery->paginate($perPage);

                $pendingLeaves->getCollection()->transform(function ($leave) {
                    $leave->canUserApprove = false;
                    $leave->nextApprovers = collect();
                    $leave->nextApprovalLevel = $this->getCurrentApprovalLevel($leave);

                    if ($leave->nextApprovalLevel) {
                        $departmentId = $leave->employee->department_id ?? null;
                        $leave->nextApprovers = $this->resolveApproversForLevel($leave->nextApprovalLevel->id, $departmentId);
                        $leave->canUserApprove = $this->userCanApproveLevel($leave->nextApprovalLevel->id, $departmentId);
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
        return $this->chain()->pendingFor(Auth::user())->count();
    }

    /** One chain per request, so approver mappings load once. */
    protected function chain(): LeaveApprovalChain
    {
        return app(LeaveApprovalChain::class);
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
