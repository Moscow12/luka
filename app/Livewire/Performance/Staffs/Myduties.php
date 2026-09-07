<?php

namespace App\Livewire\Performance\Staffs;

use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
use App\Models\StoreOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Myduties extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $employee;

    public $hasEmployeeRecord = false;

    // Filters
    public $filterStatus = '';

    public $filterPriority = '';

    // Modal states
    public $showDetailModal = false;

    public $showUpdateModal = false;

    // Selected duty for viewing/updating
    public $selectedDuty = null;

    // Update form fields
    public $actual_achievement;

    public $achievement_notes;

    public $update_status;

    // Order item remarks (for duties linked to a store order)
    public $itemRemarks = [];

    public function mount()
    {
        $this->employee = Employee::where('user_id', Auth::id())->first();

        if ($this->employee) {
            $this->hasEmployeeRecord = true;
        }
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterPriority()
    {
        $this->resetPage();
    }

    public function viewDuty($dutyId)
    {
        $this->selectedDuty = EmployeeAssignedDuty::where('id', $dutyId)
            ->where('employee_id', $this->employee->id)
            ->with(['assignedBy', 'reviewedBy', 'storeOrder.items.item', 'storeOrder.department'])
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedDuty = null;
    }

    public function openUpdateModal($dutyId)
    {
        $this->resetErrorBag();
        $this->selectedDuty = EmployeeAssignedDuty::where('id', $dutyId)
            ->where('employee_id', $this->employee->id)
            ->with(['storeOrder.items.item'])
            ->firstOrFail();

        $this->actual_achievement = $this->selectedDuty->actual_achievement;
        $this->achievement_notes = $this->selectedDuty->achievement_notes;
        $this->update_status = $this->selectedDuty->status;

        if ($this->selectedDuty->store_order_id && $this->selectedDuty->storeOrder) {
            $this->itemRemarks = $this->selectedDuty->storeOrder->items
                ->mapWithKeys(fn ($item) => [$item->id => $item->remarks])
                ->toArray();
        } else {
            $this->itemRemarks = [];
        }

        $this->showUpdateModal = true;
    }

    public function closeUpdateModal()
    {
        $this->showUpdateModal = false;
        $this->selectedDuty = null;
        $this->itemRemarks = [];
        $this->reset(['actual_achievement', 'achievement_notes', 'update_status']);
    }

    public function updateProgress()
    {
        $duty = EmployeeAssignedDuty::where('id', $this->selectedDuty->id)
            ->where('employee_id', $this->employee->id)
            ->firstOrFail();

        if ($duty->store_order_id) {
            $this->saveItemRemarks($duty);

            return;
        }

        $this->validate([
            'actual_achievement' => ['nullable', 'numeric', 'min:0'],
            'achievement_notes' => ['nullable', 'string'],
            'update_status' => ['required', 'in:assigned,in_progress,completed'],
        ]);

        try {
            DB::beginTransaction();

            // Calculate score based on target and achievement
            $score = null;
            if ($duty->target_value && $duty->target_value > 0 && $this->actual_achievement) {
                $score = min(($this->actual_achievement / $duty->target_value) * 100, 100);
            }

            $duty->update([
                'actual_achievement' => $this->actual_achievement,
                'achievement_notes' => $this->achievement_notes,
                'status' => $this->update_status,
                'score' => $score,
            ]);

            DB::commit();

            session()->flash('success', 'Progress updated successfully!');
            $this->closeUpdateModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    protected function saveItemRemarks(EmployeeAssignedDuty $duty)
    {
        $this->validate([
            'itemRemarks' => ['array'],
            'itemRemarks.*' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->itemRemarks as $itemId => $remarks) {
                StoreOrderItem::where('id', $itemId)
                    ->where('store_order_id', $duty->store_order_id)
                    ->update(['remarks' => $remarks]);
            }

            $duty->update([
                'status' => 'completed',
                'achievement_notes' => $this->achievement_notes,
            ]);

            DB::commit();

            session()->flash('success', 'Remarks saved successfully!');
            $this->closeUpdateModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function getStatistics()
    {
        if (! $this->employee) {
            return [
                'total' => 0,
                'assigned' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'high_priority' => 0,
            ];
        }

        return [
            'total' => EmployeeAssignedDuty::where('employee_id', $this->employee->id)->where('is_active', true)->count(),
            'assigned' => EmployeeAssignedDuty::where('employee_id', $this->employee->id)->where('status', 'assigned')->where('is_active', true)->count(),
            'in_progress' => EmployeeAssignedDuty::where('employee_id', $this->employee->id)->where('status', 'in_progress')->where('is_active', true)->count(),
            'completed' => EmployeeAssignedDuty::where('employee_id', $this->employee->id)->where('status', 'completed')->where('is_active', true)->count(),
            'high_priority' => EmployeeAssignedDuty::where('employee_id', $this->employee->id)->whereIn('priority', ['high', 'urgent'])->where('is_active', true)->count(),
        ];
    }

    public function render()
    {
        $duties = collect();

        if ($this->employee) {
            $query = EmployeeAssignedDuty::where('employee_id', $this->employee->id)
                ->where('is_active', true)
                ->with(['assignedBy']);

            if ($this->filterStatus) {
                $query->where('status', $this->filterStatus);
            }

            if ($this->filterPriority) {
                $query->where('priority', $this->filterPriority);
            }

            $duties = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                ->orderBy('end_date', 'asc')
                ->paginate(10);
        }

        return view('livewire.performance.staffs.myduties', [
            'duties' => $duties,
            'statistics' => $this->getStatistics(),
        ]);
    }
}
