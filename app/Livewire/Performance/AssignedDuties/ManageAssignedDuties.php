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

    public $priorityFilter = '';

    public $statusFilter = '';

    public $perPage = 10;

    // Employee Tasks Modal (date-filtered view of one employee's duties)
    public $showEmployeeTasksModal = false;

    public $viewingEmployeeId = null;

    public $employeeTaskFilterDateFrom = '';

    public $employeeTaskFilterDateTo = '';

    // Approve task (score + comment)
    public $approvingDutyId = null;

    public $approval_score = '';

    public $approval_comments = '';

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
        $this->reset(['search', 'priorityFilter', 'statusFilter']);
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
                'weight' => $this->dutyForm['weight'] !== '' ? $this->dutyForm['weight'] : 0,
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

    public function openEmployeeTasksModal($employeeId)
    {
        $this->viewingEmployeeId = $employeeId;
        $this->employeeTaskFilterDateFrom = '';
        $this->employeeTaskFilterDateTo = '';
        $this->showEmployeeTasksModal = true;
    }

    public function closeEmployeeTasksModal()
    {
        $this->showEmployeeTasksModal = false;
        $this->viewingEmployeeId = null;
        $this->employeeTaskFilterDateFrom = '';
        $this->employeeTaskFilterDateTo = '';
    }

    public function getViewingEmployeeTasksProperty()
    {
        if (! $this->viewingEmployeeId) {
            return collect();
        }

        return EmployeeAssignedDuty::where('employee_id', $this->viewingEmployeeId)
            ->when($this->employeeTaskFilterDateFrom, fn ($q) => $q->whereDate('start_date', '>=', $this->employeeTaskFilterDateFrom))
            ->when($this->employeeTaskFilterDateTo, fn ($q) => $q->whereDate('start_date', '<=', $this->employeeTaskFilterDateTo))
            ->with('reviewedBy')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getViewingEmployeeProperty()
    {
        return $this->viewingEmployeeId ? Employee::find($this->viewingEmployeeId) : null;
    }

    public function openApproveModal($dutyId)
    {
        $duty = EmployeeAssignedDuty::findOrFail($dutyId);

        if ($duty->status !== 'completed') {
            session()->flash('error', 'Only completed tasks can be approved.');

            return;
        }

        if ($duty->reviewed_at !== null) {
            session()->flash('error', 'This task has already been approved.');

            return;
        }

        $this->resetErrorBag();
        $this->approvingDutyId = $dutyId;
        $this->approval_score = '';
        $this->approval_comments = '';
    }

    public function closeApproveModal()
    {
        $this->approvingDutyId = null;
        $this->approval_score = '';
        $this->approval_comments = '';
        $this->resetErrorBag();
    }

    public function submitApproval()
    {
        $this->validate([
            'approval_score' => ['nullable', 'integer', 'min:1', 'max:10'],
            'approval_comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $duty = EmployeeAssignedDuty::findOrFail($this->approvingDutyId);

        if ($duty->status !== 'completed') {
            session()->flash('error', 'Only completed tasks can be approved.');
            $this->closeApproveModal();

            return;
        }

        if ($duty->reviewed_at !== null) {
            session()->flash('error', 'This task has already been approved.');
            $this->closeApproveModal();

            return;
        }

        $duty->update([
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'approval_score' => $this->approval_score !== '' ? $this->approval_score : null,
            'review_comments' => $this->approval_comments !== '' ? $this->approval_comments : null,
        ]);

        session()->flash('success', 'Task approved successfully!');
        $this->closeApproveModal();
    }

    public function render()
    {
        // Build the employee-grouped duty summary, scoped by the active priority/status filters
        // so the counts and progress bar reflect what's actually being filtered for.
        $dutyFilter = function ($q) {
            $q->when($this->priorityFilter, fn ($sq) => $sq->where('priority', $this->priorityFilter))
                ->when($this->statusFilter, fn ($sq) => $sq->where('status', $this->statusFilter));
        };

        $employeeSummaries = Employee::query()
            ->withCount([
                'assignedDuties as total_tasks' => $dutyFilter,
                'assignedDuties as completed_tasks' => function ($q) use ($dutyFilter) {
                    $dutyFilter($q);
                    $q->where('status', 'completed');
                },
            ])
            ->having('total_tasks', '>', 0)
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('first_name')
            ->paginate($this->perPage);

        // Calculate statistics
        $totalDuties = EmployeeAssignedDuty::count();
        $inProgressDuties = EmployeeAssignedDuty::where('status', 'in_progress')->count();
        $highPriorityDuties = EmployeeAssignedDuty::whereIn('priority', ['high', 'urgent'])->count();
        $completedDuties = EmployeeAssignedDuty::where('status', 'completed')->count();

        return view('livewire.performance.assigned-duties.manage-assigned-duties', [
            'employeeSummaries' => $employeeSummaries,
            'totalDuties' => $totalDuties,
            'inProgressDuties' => $inProgressDuties,
            'highPriorityDuties' => $highPriorityDuties,
            'completedDuties' => $completedDuties,
        ]);
    }
}
