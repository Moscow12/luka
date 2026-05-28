<?php

namespace App\Livewire\Performance\AssignedDuties;

use App\Models\Employee;
use App\Models\EmployeeAssignedDuty;
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

    public $perPage = 10;

    // Modal States
    public $showModal = false;

    public $modalMode = 'create';

    public $editingDutyId = null;

    // Duty Form Fields
    public $dutyForm = [
        'employee_id' => '',
        'duty_name' => '',
        'description' => '',
        'kpi_type' => 'quantitative',
        'measurement_type' => 'numeric',
        'weight' => '',
        'target_value' => '',
        'target_unit' => '',
        'start_date' => '',
        'end_date' => '',
        'priority' => 'medium',
        'status' => 'assigned',
        'scoring_criteria' => '',
    ];

    // Searchable employee picker (modal)
    public $dutyEmployeeSearch = '';

    public $showDutyEmployeeDropdown = false;

    public function getFilteredDutyEmployeesProperty()
    {
        $term = trim($this->dutyEmployeeSearch);

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

    public function getSelectedDutyEmployeeProperty()
    {
        $id = $this->dutyForm['employee_id'] ?? null;

        return $id ? Employee::find($id) : null;
    }

    public function selectDutyEmployee($id)
    {
        $this->dutyForm['employee_id'] = $id;
        $this->dutyEmployeeSearch = '';
        $this->showDutyEmployeeDropdown = false;
    }

    public function clearDutyEmployee()
    {
        $this->dutyForm['employee_id'] = '';
        $this->dutyEmployeeSearch = '';
        $this->showDutyEmployeeDropdown = true;
    }

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

    public function resetFilters()
    {
        $this->reset(['search', 'employeeFilter', 'priorityFilter', 'statusFilter']);
        $this->resetPage();
    }

    // Duty CRUD Operations
    public function createDuty()
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->editingDutyId = null;
        $this->dutyForm = [
            'employee_id' => '',
            'duty_name' => '',
            'description' => '',
            'kpi_type' => 'quantitative',
            'measurement_type' => 'numeric',
            'weight' => '',
            'target_value' => '',
            'target_unit' => '',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => '',
            'priority' => 'medium',
            'status' => 'assigned',
            'scoring_criteria' => '',
        ];
        $this->showModal = true;
    }

    public function editDuty($dutyId)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'edit';
        $this->editingDutyId = $dutyId;

        $duty = EmployeeAssignedDuty::findOrFail($dutyId);
        $this->dutyForm = [
            'employee_id' => $duty->employee_id,
            'duty_name' => $duty->duty_name,
            'description' => $duty->description,
            'kpi_type' => $duty->kpi_type,
            'measurement_type' => $duty->measurement_type,
            'weight' => $duty->weight,
            'target_value' => $duty->target_value,
            'target_unit' => $duty->target_unit,
            'start_date' => $duty->start_date ? $duty->start_date->format('Y-m-d') : '',
            'end_date' => $duty->end_date ? $duty->end_date->format('Y-m-d') : '',
            'priority' => $duty->priority,
            'status' => $duty->status,
            'scoring_criteria' => $duty->scoring_criteria,
        ];
        $this->showModal = true;
    }

    public function saveDuty()
    {
        $rules = [
            'dutyForm.employee_id' => 'required|exists:employees,id',
            'dutyForm.duty_name' => 'required|string|max:255',
            'dutyForm.description' => 'nullable|string',
            'dutyForm.kpi_type' => 'required|in:qualitative,quantitative',
            'dutyForm.measurement_type' => 'required|in:numeric,boolean,percentage,rating_scale',
            'dutyForm.weight' => 'nullable|numeric|min:0|max:100',
            'dutyForm.target_value' => 'nullable|numeric',
            'dutyForm.target_unit' => 'nullable|string|max:100',
            'dutyForm.start_date' => 'nullable|date',
            'dutyForm.end_date' => 'nullable|date|after_or_equal:dutyForm.start_date',
            'dutyForm.priority' => 'required|in:low,medium,high,urgent',
            'dutyForm.status' => 'required|in:assigned,in_progress,completed,cancelled',
            'dutyForm.scoring_criteria' => 'nullable|string',
        ];

        $messages = [
            'dutyForm.employee_id.required' => 'Please select an employee.',
            'dutyForm.duty_name.required' => 'Please enter the duty name.',
            'dutyForm.kpi_type.required' => 'Please select a KPI type.',
            'dutyForm.priority.required' => 'Please select a priority.',
            'dutyForm.status.required' => 'Please select a status.',
            'dutyForm.end_date.after_or_equal' => 'End date must be after or equal to start date.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $data = [
                'employee_id' => $this->dutyForm['employee_id'],
                'duty_name' => $this->dutyForm['duty_name'],
                'description' => $this->dutyForm['description'],
                'kpi_type' => $this->dutyForm['kpi_type'],
                'measurement_type' => $this->dutyForm['measurement_type'],
                'weight' => $this->dutyForm['weight'] ?: null,
                'target_value' => $this->dutyForm['target_value'] ?: null,
                'target_unit' => $this->dutyForm['target_unit'],
                'start_date' => $this->dutyForm['start_date'] ?: null,
                'end_date' => $this->dutyForm['end_date'] ?: null,
                'priority' => $this->dutyForm['priority'],
                'status' => $this->dutyForm['status'],
                'scoring_criteria' => $this->dutyForm['scoring_criteria'],
            ];

            if ($this->modalMode === 'edit' && $this->editingDutyId) {
                $duty = EmployeeAssignedDuty::findOrFail($this->editingDutyId);
                $duty->update($data);
                session()->flash('success', 'Assigned duty updated successfully!');
            } else {
                $data['assigned_by'] = Auth::id();
                $data['assigned_at'] = now();
                $data['is_active'] = true;
                EmployeeAssignedDuty::create($data);
                session()->flash('success', 'Duty assigned successfully!');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingDutyId = null;
        $this->dutyEmployeeSearch = '';
        $this->showDutyEmployeeDropdown = false;
        $this->resetErrorBag();
    }

    public function deleteDuty($dutyId)
    {
        try {
            $duty = EmployeeAssignedDuty::findOrFail($dutyId);

            // Check if duty is completed
            if ($duty->status === 'completed') {
                session()->flash('error', 'Cannot delete a completed duty.');

                return;
            }

            DB::beginTransaction();
            $duty->delete();
            DB::commit();

            session()->flash('success', 'Assigned duty deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function updateStatus($dutyId, $status)
    {
        try {
            $duty = EmployeeAssignedDuty::findOrFail($dutyId);

            if (! in_array($status, ['assigned', 'in_progress', 'completed', 'cancelled'])) {
                session()->flash('error', 'Invalid status.');

                return;
            }

            DB::beginTransaction();
            $duty->update(['status' => $status]);
            DB::commit();

            session()->flash('success', 'Status updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Build query with eager loading
        $dutiesQuery = EmployeeAssignedDuty::query()
            ->with(['employee', 'assignedBy']);

        // Apply search filter
        if ($this->search) {
            $dutiesQuery->where(function ($query) {
                $query->where('duty_name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhereHas('employee', function ($q) {
                        $q->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%');
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
        $duties = $dutiesQuery->paginate($this->perPage);

        // Calculate statistics
        $totalDuties = EmployeeAssignedDuty::count();
        $inProgressDuties = EmployeeAssignedDuty::where('status', 'in_progress')->count();
        $highPriorityDuties = EmployeeAssignedDuty::whereIn('priority', ['high', 'urgent'])->count();
        $completedDuties = EmployeeAssignedDuty::where('status', 'completed')->count();

        // Get employees for filters
        $employees = Employee::orderBy('first_name')->get();

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
