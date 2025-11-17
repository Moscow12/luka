<?php

namespace App\Livewire\Performance\AssignedDuties;

use App\Models\EmployeeAssignedDuty;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAssignedDuties extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';
    public $employeeFilter = '';
    public $priorityFilter = '';
    public $statusFilter = '';

    // Modal States
    public $showModal = false;
    public $modalMode = 'create';

    // Selected Records
    public $selectedDuty = null;

    // Duty Form Fields
    public $duty_id;
    public $employee_id;
    public $duty_name;
    public $description;
    public $kpi_type = 'quantitative';
    public $measurement_type = 'numeric';
    public $weight;
    public $target_value;
    public $target_unit;
    public $start_date;
    public $end_date;
    public $priority = 'medium';
    public $status = 'assigned';
    public $scoring_criteria;

    // Pagination reset on search/filter changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmployeeFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        //
    }

    // Duty CRUD Operations
    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->showModal = true;
        $this->resetDutyForm();
        $this->kpi_type = 'quantitative';
        $this->measurement_type = 'numeric';
        $this->priority = 'medium';
        $this->status = 'assigned';
    }

    public function openEditModal($dutyId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->showModal = true;

        $duty = EmployeeAssignedDuty::findOrFail($dutyId);
        $this->duty_id = $duty->id;
        $this->employee_id = $duty->employee_id;
        $this->duty_name = $duty->duty_name;
        $this->description = $duty->description;
        $this->kpi_type = $duty->kpi_type;
        $this->measurement_type = $duty->measurement_type;
        $this->weight = $duty->weight;
        $this->target_value = $duty->target_value;
        $this->target_unit = $duty->target_unit;
        $this->start_date = $duty->start_date ? $duty->start_date->format('Y-m-d') : null;
        $this->end_date = $duty->end_date ? $duty->end_date->format('Y-m-d') : null;
        $this->priority = $duty->priority;
        $this->status = $duty->status;
        $this->scoring_criteria = $duty->scoring_criteria;
    }

    public function save()
    {
        $this->validate($this->getValidationRules());

        try {
            DB::beginTransaction();

            if ($this->modalMode === 'edit' && $this->duty_id) {
                $duty = EmployeeAssignedDuty::findOrFail($this->duty_id);
                $duty->update([
                    'employee_id' => $this->employee_id,
                    'duty_name' => $this->duty_name,
                    'description' => $this->description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'scoring_criteria' => $this->scoring_criteria,
                ]);
                session()->flash('success', 'Assigned Duty updated successfully!');
            } else {
                EmployeeAssignedDuty::create([
                    'employee_id' => $this->employee_id,
                    'duty_name' => $this->duty_name,
                    'description' => $this->description,
                    'kpi_type' => $this->kpi_type,
                    'measurement_type' => $this->measurement_type,
                    'weight' => $this->weight,
                    'target_value' => $this->target_value,
                    'target_unit' => $this->target_unit,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'priority' => $this->priority,
                    'status' => $this->status,
                    'scoring_criteria' => $this->scoring_criteria,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                session()->flash('success', 'Assigned Duty created successfully!');
            }

            DB::commit();
            $this->showModal = false;
            $this->resetDutyForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function delete($dutyId)
    {
        try {
            $duty = EmployeeAssignedDuty::findOrFail($dutyId);

            // Check if duty is completed
            if ($duty->status === 'completed') {
                session()->flash('error', 'Cannot delete a completed duty!');
                return;
            }

            DB::beginTransaction();
            $duty->delete();
            DB::commit();

            session()->flash('success', 'Assigned Duty deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function updateStatus($dutyId, $status)
    {
        try {
            $duty = EmployeeAssignedDuty::findOrFail($dutyId);

            // Validate status
            if (!in_array($status, ['assigned', 'in_progress', 'completed', 'cancelled'])) {
                session()->flash('error', 'Invalid status!');
                return;
            }

            DB::beginTransaction();
            $duty->update([
                'status' => $status,
            ]);
            DB::commit();

            session()->flash('success', 'Status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Validation Rules
    protected function getValidationRules()
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'duty_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'kpi_type' => ['required', 'in:qualitative,quantitative'],
            'measurement_type' => ['required', 'in:numeric,boolean,percentage,rating_scale'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:assigned,in_progress,completed,cancelled'],
            'scoring_criteria' => ['nullable', 'string'],
        ];
    }

    // Helper Methods
    protected function resetDutyForm()
    {
        $this->reset([
            'duty_id',
            'employee_id',
            'duty_name',
            'description',
            'kpi_type',
            'measurement_type',
            'weight',
            'target_value',
            'target_unit',
            'start_date',
            'end_date',
            'priority',
            'status',
            'scoring_criteria'
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetDutyForm();
    }

    public function render()
    {
        // Build query with eager loading
        $dutiesQuery = EmployeeAssignedDuty::query()
            ->with(['employee.user', 'assignedBy']);

        // Apply search filter
        if ($this->search) {
            $dutiesQuery->where(function ($query) {
                $query->where('duty_name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('employee.user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply employee filter
        if ($this->employeeFilter) {
            $dutiesQuery->where('employee_id', $this->employeeFilter);
        }

        // Apply priority filter
        if ($this->priorityFilter) {
            $dutiesQuery->where('priority', $this->priorityFilter);
        }

        // Apply status filter
        if ($this->statusFilter) {
            $dutiesQuery->where('status', $this->statusFilter);
        }

        // Order by priority and latest
        $dutiesQuery->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc');

        // Paginate
        $duties = $dutiesQuery->paginate(10);

        // Calculate statistics
        $totalDuties = EmployeeAssignedDuty::count();
        $inProgressDuties = EmployeeAssignedDuty::where('status', 'in_progress')->count();
        $highPriorityDuties = EmployeeAssignedDuty::whereIn('priority', ['high', 'urgent'])->count();
        $completedDuties = EmployeeAssignedDuty::where('status', 'completed')->count();

        // Get employees for filters
        $employees = Employee::with('user')->get();

        return view('livewire.performance.assigned-duties.manage-assigned-duties', [
            'duties' => $duties,
            'totalDuties' => $totalDuties,
            'inProgressDuties' => $inProgressDuties,
            'highPriorityDuties' => $highPriorityDuties,
            'completedDuties' => $completedDuties,
            'employees' => $employees,
        ]);
    }
}
