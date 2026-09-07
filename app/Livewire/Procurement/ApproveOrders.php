<?php

namespace App\Livewire\Procurement;

use App\Models\approvallevel;
use App\Models\approvalleveltodocument;
use App\Models\approvalleveltoemployee;
use App\Models\departments as Department;
use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ApproveOrders extends Component
{
    use WithPagination;

    public $activeTab = 'pending';

    public $search = '';

    public $filterDepartment = '';

    public $filterDateFrom = '';

    public $filterDateTo = '';

    // Tiered approval (Setup > Approval Configuration, document type: Purchase)
    public $employee;

    public $myApprovalLevels;

    public $canFirstApprove = false;

    public $canFinalApprove = false;

    public $viewingOrder = null;

    public $showViewModal = false;

    public $rejectingOrderId = null;

    public $rejection_reason = '';

    // Assign to employee
    public $assigningOrderId = null;

    public $assignEmployeeId = '';

    public $assignEmployeeSearch = '';

    public $assignPriority = 'medium';

    public $assignDueDate = '';

    public $assignNotes = '';

    public function mount()
    {
        $this->filterDateFrom = now()->startOfMonth()->toDateString();
        $this->filterDateTo = now()->endOfMonth()->toDateString();
        $this->loadApprovalLevels();
    }

    /**
     * Determine which approval tier(s) the current user holds for the
     * "Purchase" document type (Setup > Approval Configuration). The lowest
     * level_order acts on submitted->review; any higher level_order acts on
     * review->approved. Super admins hold both tiers.
     */
    public function loadApprovalLevels()
    {
        if (Auth::user()->isSuperAdmin()) {
            $this->canFirstApprove = true;
            $this->canFinalApprove = true;

            return;
        }

        $this->employee = Employee::where('user_id', Auth::id())->first();

        if (! $this->employee) {
            return;
        }

        $assignedLevelIds = approvalleveltoemployee::where('employee_id', $this->employee->id)
            ->where('is_active', true)
            ->pluck('approval_level_id');

        // All approval levels mapped to the Purchase document type, ranked by level_order.
        $purchaseLevelIds = approvalleveltodocument::where('document_type', StoreOrder::APPROVAL_DOCUMENT_TYPE)
            ->where('is_active', true)
            ->pluck('approval_level_id');

        $purchaseLevelOrders = approvallevel::whereIn('id', $purchaseLevelIds)
            ->where('is_active', true)
            ->orderBy('level_order')
            ->pluck('level_order');

        if ($purchaseLevelOrders->isEmpty()) {
            return;
        }

        $lowestLevelOrder = $purchaseLevelOrders->first();

        $this->myApprovalLevels = approvallevel::whereIn('id', $purchaseLevelIds)
            ->whereIn('id', $assignedLevelIds)
            ->where('is_active', true)
            ->orderBy('level_order')
            ->get();

        $this->canFirstApprove = $this->myApprovalLevels->contains('level_order', $lowestLevelOrder);
        $this->canFinalApprove = $this->myApprovalLevels->contains(fn ($level) => $level->level_order > $lowestLevelOrder);
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterDepartment']);
        $this->filterDateFrom = now()->startOfMonth()->toDateString();
        $this->filterDateTo = now()->endOfMonth()->toDateString();
        $this->resetPage();
    }

    public function openViewModal($id)
    {
        $this->viewingOrder = StoreOrder::with([
            'items.item.category',
            'department',
            'requestedBy',
            'approvedBy',
            'assignedDuties.employee',
        ])->findOrFail($id);

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingOrder = null;
    }

    public function approve($id)
    {
        $order = StoreOrder::findOrFail($id);

        if ($order->canFirstApprove()) {
            if (! $this->canFirstApprove) {
                session()->flash('error', 'You are not authorized to review this order.');

                return;
            }

            $order->update([
                'status' => StoreOrder::STATUS_REVIEW,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            session()->flash('success', 'Order '.$order->order_number.' moved to review.');

            return;
        }

        if ($order->canFinalApprove()) {
            if (! $this->canFinalApprove) {
                session()->flash('error', 'You are not authorized to give final approval for this order.');

                return;
            }

            $order->update([
                'status' => StoreOrder::STATUS_APPROVED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            session()->flash('success', 'Order '.$order->order_number.' approved successfully!');

            return;
        }

        session()->flash('error', 'This order cannot be approved.');
    }

    public function openRejectModal($id)
    {
        $this->rejectingOrderId = $id;
        $this->rejection_reason = '';
        $this->resetErrorBag();
    }

    public function closeRejectModal()
    {
        $this->rejectingOrderId = null;
        $this->rejection_reason = '';
    }

    public function reject()
    {
        $this->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $order = StoreOrder::findOrFail($this->rejectingOrderId);

        $authorized = ($order->canFirstApprove() && $this->canFirstApprove)
            || ($order->canFinalApprove() && $this->canFinalApprove);

        if (! $authorized) {
            session()->flash('error', 'You are not authorized to reject this order.');
            $this->closeRejectModal();

            return;
        }

        $order->update([
            'status' => StoreOrder::STATUS_REJECTED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $this->rejection_reason,
        ]);

        session()->flash('success', 'Order '.$order->order_number.' rejected.');
        $this->closeRejectModal();
    }

    public function getAssignableEmployeesProperty()
    {
        $term = trim($this->assignEmployeeSearch);

        return Employee::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('last_name', 'like', '%'.$term.'%')
                        ->orWhere('employee_no', 'like', '%'.$term.'%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%'.$term.'%']);
                });
            })
            ->orderBy('first_name')
            ->limit(50)
            ->get();
    }

    public function getSelectedAssignEmployeeProperty()
    {
        return $this->assignEmployeeId ? Employee::find($this->assignEmployeeId) : null;
    }

    public function openAssignModal($id)
    {
        $this->assigningOrderId = $id;
        $this->assignEmployeeId = '';
        $this->assignEmployeeSearch = '';
        $this->assignPriority = 'medium';
        $this->assignDueDate = '';
        $this->assignNotes = '';
        $this->resetErrorBag();
    }

    public function closeAssignModal()
    {
        $this->assigningOrderId = null;
        $this->assignEmployeeId = '';
        $this->assignEmployeeSearch = '';
        $this->assignNotes = '';
    }

    public function selectAssignEmployee($employeeId)
    {
        $this->assignEmployeeId = $employeeId;
        $this->assignEmployeeSearch = '';
    }

    public function assignDuty()
    {
        $this->validate([
            'assignEmployeeId' => ['required', 'exists:employees,id'],
            'assignPriority' => ['required', 'in:low,medium,high,urgent'],
            'assignDueDate' => ['nullable', 'date'],
            'assignNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = StoreOrder::findOrFail($this->assigningOrderId);

        EmployeeAssignedDuty::create([
            'employee_id' => $this->assignEmployeeId,
            'store_order_id' => $order->id,
            'duty_name' => 'Review items for order '.$order->order_number,
            'description' => trim(
                'Add remarks on the requested items for store order '.$order->order_number
                .($this->assignNotes ? '. '.$this->assignNotes : '')
            ),
            'kpi_type' => 'qualitative',
            'measurement_type' => 'boolean',
            'start_date' => now()->toDateString(),
            'end_date' => $this->assignDueDate ?: null,
            'priority' => $this->assignPriority,
            'status' => 'assigned',
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        session()->flash('success', 'Order '.$order->order_number.' assigned for remarks.');
        $this->closeAssignModal();
    }

    protected function baseQuery()
    {
        return StoreOrder::query()
            ->with(['department', 'items', 'requestedBy', 'approvedBy'])
            ->when($this->filterDepartment, fn ($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterDateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->filterDateTo))
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('order_description', 'like', '%'.$this->search.'%');
            });
    }

    protected function itemsQuery()
    {
        return StoreOrderItem::query()
            ->whereHas('storeOrder', function ($q) {
                $q->where('status', StoreOrder::STATUS_REVIEW)
                    ->when($this->filterDepartment, fn ($sq) => $sq->where('department_id', $this->filterDepartment))
                    ->when($this->filterDateFrom, fn ($sq) => $sq->whereDate('created_at', '>=', $this->filterDateFrom))
                    ->when($this->filterDateTo, fn ($sq) => $sq->whereDate('created_at', '<=', $this->filterDateTo))
                    ->when($this->search, function ($sq) {
                        $sq->where('order_number', 'like', '%'.$this->search.'%')
                            ->orWhere('order_description', 'like', '%'.$this->search.'%');
                    });
            })
            ->with(['item', 'storeOrder.department']);
    }

    public function render()
    {
        $departments = Department::orderBy('name')->get();

        if ($this->activeTab === 'requisition') {
            $items = $this->itemsQuery()
                ->join('store_orders', 'store_orders.id', '=', 'store_order_items.store_order_id')
                ->orderBy('store_orders.created_at', 'desc')
                ->select('store_order_items.*')
                ->paginate(15);

            return view('livewire.procurement.approve-orders', [
                'orders' => collect(),
                'items' => $items,
                'departments' => $departments,
                'pendingCount' => $this->pendingCount(),
                'reviewCount' => $this->reviewCount(),
            ]);
        }

        $status = match ($this->activeTab) {
            'approved' => StoreOrder::STATUS_APPROVED,
            'rejected' => StoreOrder::STATUS_REJECTED,
            default => StoreOrder::STATUS_SUBMITTED,
        };

        $orders = $this->baseQuery()
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.procurement.approve-orders', [
            'orders' => $orders,
            'items' => collect(),
            'departments' => $departments,
            'pendingCount' => $this->pendingCount(),
            'reviewCount' => $this->reviewCount(),
        ]);
    }

    protected function pendingCount(): int
    {
        return StoreOrder::where('status', StoreOrder::STATUS_SUBMITTED)->count();
    }

    protected function reviewCount(): int
    {
        return StoreOrder::where('status', StoreOrder::STATUS_REVIEW)->count();
    }
}
