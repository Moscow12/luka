<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketStatus;
use App\Models\departments;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\Tickets\TicketAccess;
use App\Services\Tickets\TicketActionException;
use App\Services\Tickets\TicketMetricsService;
use App\Services\Tickets\TicketService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * One list for the four PRD views: mine | assigned | department | all.
 */
class TicketList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $scope = 'mine';

    #[Url]
    public $bucket = '';

    #[Url]
    public $search = '';

    #[Url]
    public $filterStatus = '';

    public $filterPriority = '';

    public $filterCategory = '';

    #[Url]
    public $filterDepartment = '';

    public $dateFrom = '';

    public $dateTo = '';

    // Quick close / reopen
    public $actionTicketId = null;

    public $actionType = null;

    public $rating = 5;

    public $remarks = '';

    public function mount(string $scope = 'mine')
    {
        abort_unless(in_array($scope, ['mine', 'assigned', 'department', 'all'], true), 404);
        $this->scope = $scope;

        $user = Auth::user();

        if ($scope === 'department') {
            abort_unless(TicketAccess::isAdmin($user) || TicketAccess::supervisedDepartmentIds($user) !== [], 403);
        }

        if ($scope === 'all') {
            abort_unless(TicketAccess::isAdmin($user), 403);
        }
    }

    public function updating($name)
    {
        if (in_array($name, ['bucket', 'search', 'filterStatus', 'filterPriority', 'filterCategory', 'filterDepartment', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function setBucket($bucket)
    {
        $this->bucket = $this->bucket === $bucket ? '' : $bucket;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['bucket', 'search', 'filterStatus', 'filterPriority', 'filterCategory', 'filterDepartment', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function openAction($ticketId, $type)
    {
        $this->resetErrorBag();
        $this->actionTicketId = $ticketId;
        $this->actionType = $type;
        $this->rating = 5;
        $this->remarks = '';
    }

    public function closeAction()
    {
        $this->reset(['actionTicketId', 'actionType', 'remarks']);
    }

    public function submitAction(TicketService $service)
    {
        $ticket = TicketAccess::scopeVisible(Ticket::query(), Auth::user())->findOrFail($this->actionTicketId);

        $this->validate($this->actionType === 'close'
            ? ['rating' => ['required', 'integer', 'between:1,5'], 'remarks' => ['nullable', 'string', 'max:2000']]
            : ['remarks' => ['required', 'string', 'max:2000']]);

        try {
            $this->actionType === 'close'
                ? $service->close($ticket, Auth::user(), (int) $this->rating, $this->remarks ?: null)
                : $service->reopen($ticket, Auth::user(), $this->remarks);

            session()->flash('success', "Ticket {$ticket->ticket_number} ".($this->actionType === 'close' ? 'closed.' : 'reopened.'));
        } catch (TicketActionException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->closeAction();
    }

    protected function baseQuery(): Builder
    {
        $user = Auth::user();
        $employee = TicketAccess::employeeFor($user);
        $query = Ticket::query();

        return match ($this->scope) {
            'mine' => $query->where('created_by', $user->id),
            'assigned' => $employee ? $query->where('assignee_employee_id', $employee->id) : $query->whereRaw('1 = 0'),
            'department' => $query->whereIn('department_id', $this->departmentScopeIds()),
            'all' => $query,
        };
    }

    /**
     * Departments shown in the department view: those the user supervises (all of them for an
     * admin who supervises none), narrowed by the department filter.
     *
     * @return array<int, string>
     */
    protected function departmentScopeIds(): array
    {
        $user = Auth::user();
        $ids = TicketAccess::supervisedDepartmentIds($user);

        if ($ids === [] && TicketAccess::isAdmin($user)) {
            $ids = departments::pluck('id')->all();
        }

        return $this->filterDepartment && in_array($this->filterDepartment, $ids, true) ? [$this->filterDepartment] : $ids;
    }

    protected function applyBucket(Builder $query, ?string $bucket): void
    {
        match ($bucket) {
            'new' => $query->where('status', TicketStatus::Assigned->value),
            'in_progress' => $query->whereIn('status', [TicketStatus::Accepted->value, TicketStatus::InProgress->value, TicketStatus::Reopened->value]),
            'pending' => $query->where('status', TicketStatus::PendingResponse->value),
            'overdue' => $query->overdue(),
            'unassigned' => $query->where('status', TicketStatus::Open->value)->whereNull('assignee_employee_id'),
            'active' => $query->whereIn('status', TicketStatus::activeValues()),
            'awaiting_closure' => $query->where('status', TicketStatus::Resolved->value),
            default => null,
        };
    }

    protected function bucketCounts(Builder $base): array
    {
        $buckets = match ($this->scope) {
            'assigned' => ['new' => 'New / Unaccepted', 'in_progress' => 'In Progress', 'overdue' => 'Overdue', 'pending' => 'Pending Response'],
            'department' => ['unassigned' => 'Unassigned queue', 'active' => 'Active', 'overdue' => 'Overdue', 'awaiting_closure' => 'Awaiting closure'],
            default => ['active' => 'Active', 'awaiting_closure' => 'Resolved – awaiting closure', 'overdue' => 'Overdue'],
        };

        $counts = [];
        foreach ($buckets as $key => $label) {
            $q = clone $base;
            $this->applyBucket($q, $key);
            $counts[$key] = ['label' => $label, 'count' => $q->count()];
        }

        return $counts;
    }

    public function render(TicketMetricsService $metrics)
    {
        $user = Auth::user();
        $base = $this->baseQuery();
        $buckets = $this->bucketCounts(clone $base);

        $query = (clone $base)
            ->with(['category:id,name', 'department:id,name', 'assignee:id,first_name,last_name', 'creator:id,first_name,surname', 'latestPublicMessage.user:id,first_name,surname'])
            ->with(['participants' => fn ($q) => $q->where('user_id', $user->id)])
            ->withMax(['messages as last_message_at' => fn ($q) => $q->where('type', 'public')], 'created_at');

        $this->applyBucket($query, $this->bucket);

        $query->when($this->search, fn (Builder $q, $s) => $q->where(fn (Builder $w) => $w->where('ticket_number', 'like', "%{$s}%")->orWhere('subject', 'like', "%{$s}%")))
            ->when($this->filterStatus, fn (Builder $q, $s) => $q->where('status', $s))
            ->when($this->filterPriority, fn (Builder $q, $p) => $q->where('priority', $p))
            ->when($this->filterCategory, fn (Builder $q, $c) => $q->where('category_id', $c))
            ->when($this->scope === 'all' && $this->filterDepartment, fn (Builder $q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->dateFrom, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($this->dateTo, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d));

        $tickets = $query
            ->orderByRaw("CASE status WHEN 'closed' THEN 2 WHEN 'cancelled' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->paginate(15);

        $workload = $this->scope === 'department'
            ? $metrics->workload(['department_ids' => $this->departmentScopeIds()])['assignees']
            : null;

        $departmentOptions = match ($this->scope) {
            'all' => departments::orderBy('name')->get(['id', 'name']),
            'department' => departments::whereIn('id', TicketAccess::supervisedDepartmentIds($user) ?: departments::pluck('id')->all())->orderBy('name')->get(['id', 'name']),
            default => collect(),
        };

        return view('livewire.tickets.ticket-list', [
            'tickets' => $tickets,
            'buckets' => $buckets,
            'workload' => $workload,
            'categories' => TicketCategory::orderBy('name')->get(['id', 'name']),
            'departmentOptions' => $departmentOptions,
            'statuses' => TicketStatus::options(),
            'title' => match ($this->scope) {
                'mine' => ['My Tickets', 'Tickets you created'],
                'assigned' => ['Assigned to Me', 'Tickets where you are the accountable assignee'],
                'department' => ['Department Tickets', 'Queue, workload and bottlenecks for the departments you supervise'],
                'all' => ['All Tickets', 'Organisation-wide ticket workflow'],
            },
            'canCreate' => $user->isSuperAdmin() || $user->can('create-tickets'),
        ]);
    }
}
