<?php

namespace App\Livewire\Hr\Roster;

use App\Models\departments;
use App\Models\Employee;
use App\Models\employeeroster;
use App\Models\shifts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Editroster extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    #[Url]
    public $filterDepartment = '';

    #[Url]
    public $filterEmployee = '';

    #[Url]
    public $filterShift = '';

    #[Url]
    public $filterStatus = '';

    #[Url]
    public $filterDateFrom = '';

    #[Url]
    public $filterDateTo = '';

    public $search = '';
    public $perPage = 20;

    // Edit Modal
    public $showEditModal = false;
    public $editingRosterId = null;
    public $editShiftId = '';
    public $editStatus = '';
    public $editShiftType = '';
    public $editNotes = '';
    public $editRosterDate = '';

    // Bulk Edit
    public $selectedRosters = [];
    public $selectAll = false;
    public $showBulkEditModal = false;
    public $bulkShiftId = '';
    public $bulkStatus = '';
    public $bulkShiftType = '';

    // Delete Confirmation
    public $showDeleteModal = false;
    public $deletingRosterId = null;

    public function mount()
    {
        // Default to current month
        $this->filterDateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
        $this->selectedRosters = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRosters = $this->getRostersQuery()->pluck('id')->toArray();
        } else {
            $this->selectedRosters = [];
        }
    }

    public function resetFilters()
    {
        $this->reset([
            'filterDepartment',
            'filterEmployee',
            'filterShift',
            'filterStatus',
            'search',
        ]);
        $this->filterDateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->selectedRosters = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function openEditModal($rosterId)
    {
        $this->resetErrorBag();
        $roster = employeeroster::findOrFail($rosterId);

        $this->editingRosterId = $rosterId;
        $this->editShiftId = $roster->shift_id;
        $this->editStatus = $roster->status;
        $this->editShiftType = $roster->shift_type;
        $this->editNotes = $roster->notes;
        $this->editRosterDate = $roster->roster_date;

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['editingRosterId', 'editShiftId', 'editStatus', 'editShiftType', 'editNotes', 'editRosterDate']);
    }

    public function saveRoster()
    {
        $this->validate([
            'editShiftId' => 'required|exists:shifts,id',
            'editStatus' => 'required|in:scheduled,completed,cancelled,no_show',
            'editShiftType' => 'required|in:regular,overtime,special',
            'editNotes' => 'nullable|string|max:500',
            'editRosterDate' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $roster = employeeroster::findOrFail($this->editingRosterId);

            // Check if date changed and new date conflicts
            if ($roster->roster_date !== $this->editRosterDate) {
                $exists = employeeroster::where('employee_id', $roster->employee_id)
                    ->where('roster_date', $this->editRosterDate)
                    ->where('id', '!=', $roster->id)
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'A roster already exists for this employee on the selected date.');
                    return;
                }
            }

            $roster->update([
                'shift_id' => $this->editShiftId,
                'status' => $this->editStatus,
                'shift_type' => $this->editShiftType,
                'notes' => $this->editNotes,
                'roster_date' => $this->editRosterDate,
            ]);

            DB::commit();

            session()->flash('success', 'Roster updated successfully.');
            $this->closeEditModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function openDeleteModal($rosterId)
    {
        $this->deletingRosterId = $rosterId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingRosterId = null;
    }

    public function deleteRoster()
    {
        try {
            DB::beginTransaction();

            $roster = employeeroster::findOrFail($this->deletingRosterId);
            $roster->delete();

            DB::commit();

            session()->flash('success', 'Roster deleted successfully.');
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function openBulkEditModal()
    {
        if (empty($this->selectedRosters)) {
            session()->flash('error', 'Please select at least one roster to edit.');
            return;
        }

        $this->resetErrorBag();
        $this->bulkShiftId = '';
        $this->bulkStatus = '';
        $this->bulkShiftType = '';
        $this->showBulkEditModal = true;
    }

    public function closeBulkEditModal()
    {
        $this->showBulkEditModal = false;
        $this->reset(['bulkShiftId', 'bulkStatus', 'bulkShiftType']);
    }

    public function saveBulkEdit()
    {
        // At least one field must be filled
        if (empty($this->bulkShiftId) && empty($this->bulkStatus) && empty($this->bulkShiftType)) {
            session()->flash('error', 'Please select at least one field to update.');
            return;
        }

        try {
            DB::beginTransaction();

            $updateData = [];

            if (!empty($this->bulkShiftId)) {
                $updateData['shift_id'] = $this->bulkShiftId;
            }

            if (!empty($this->bulkStatus)) {
                $updateData['status'] = $this->bulkStatus;
            }

            if (!empty($this->bulkShiftType)) {
                $updateData['shift_type'] = $this->bulkShiftType;
            }

            employeeroster::whereIn('id', $this->selectedRosters)->update($updateData);

            DB::commit();

            $count = count($this->selectedRosters);
            session()->flash('success', "Successfully updated {$count} roster(s).");

            $this->closeBulkEditModal();
            $this->selectedRosters = [];
            $this->selectAll = false;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedRosters)) {
            session()->flash('error', 'Please select at least one roster to delete.');
            return;
        }

        try {
            DB::beginTransaction();

            $count = count($this->selectedRosters);
            employeeroster::whereIn('id', $this->selectedRosters)->delete();

            DB::commit();

            session()->flash('success', "Successfully deleted {$count} roster(s).");

            $this->selectedRosters = [];
            $this->selectAll = false;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function getRostersQuery()
    {
        return employeeroster::query()
            ->with(['employee.department', 'employee.designation', 'shift', 'addedBy'])
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->filterShift, fn($q) => $q->where('shift_id', $this->filterShift))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('roster_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('roster_date', '<=', $this->filterDateTo))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('middle_name', 'like', '%' . $this->search . '%')
                        ->orWhere('employee_no', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('roster_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function getStatistics()
    {
        $baseQuery = employeeroster::query()
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('roster_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('roster_date', '<=', $this->filterDateTo));

        return [
            'total' => (clone $baseQuery)->count(),
            'scheduled' => (clone $baseQuery)->where('status', 'scheduled')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'no_show' => (clone $baseQuery)->where('status', 'no_show')->count(),
        ];
    }

    public function render()
    {
        $rosters = $this->getRostersQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();
        $shifts = shifts::where('status', 'active')->orderBy('name')->get();
        $employees = Employee::when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        return view('livewire.hr.roster.editroster', [
            'rosters' => $rosters,
            'departments' => $departments,
            'shifts' => $shifts,
            'employees' => $employees,
            'statistics' => $this->getStatistics(),
        ]);
    }
}
