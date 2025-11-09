<?php

namespace App\Livewire\Hr\Roster;

use App\Models\departments;
use App\Models\employeeroster;
use App\Models\shifts;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Viewroster extends Component
{
    use WithPagination;

    // Filters
    #[Url]
    public $rosterDate = '';

    #[Url]
    public $department = '';

    #[Url]
    public $shift = '';

    #[Url]
    public $status = '';

    #[Url]
    public $shiftType = '';

    #[Url]
    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    // UI State
    public $showFilters = false;

    public $perPage = 20;

    // Modal state for editing
    public $editingRosterId = null;

    public $editNotes = '';

    public $editStatus = '';

    public function mount()
    {
        // Set default date to today
        $this->rosterDate = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRosterDate()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
    }

    public function updatingShift()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingShiftType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'search',
            'department',
            'shift',
            'status',
            'shiftType',
        ]);
        $this->rosterDate = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function editRoster($rosterId)
    {
        $roster = employeeroster::find($rosterId);

        if ($roster) {
            $this->editingRosterId = $rosterId;
            $this->editNotes = $roster->notes;
            $this->editStatus = $roster->status;
        }
    }

    public function updateRoster()
    {
        $this->validate([
            'editStatus' => 'required|in:scheduled,completed,cancelled,no_show',
            'editNotes' => 'nullable|string|max:500',
        ]);

        $roster = employeeroster::find($this->editingRosterId);

        if ($roster) {
            $roster->update([
                'status' => $this->editStatus,
                'notes' => $this->editNotes,
            ]);

            session()->flash('success', 'Roster updated successfully');
            $this->closeEditModal();
        }
    }

    public function closeEditModal()
    {
        $this->reset(['editingRosterId', 'editNotes', 'editStatus']);
    }

    public function deleteRoster($rosterId)
    {
        $roster = employeeroster::find($rosterId);

        if ($roster) {
            $roster->delete();
            session()->flash('success', 'Roster deleted successfully');
        }
    }

    private function getRostersQuery()
    {
        return employeeroster::query()
            ->with(['employee.department', 'employee.designation', 'shift'])
            ->when($this->rosterDate, function ($query) {
                $query->whereDate('roster_date', $this->rosterDate);
            })
            ->when($this->dateFrom && ! $this->rosterDate, function ($query) {
                $query->whereDate('roster_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo && ! $this->rosterDate, function ($query) {
                $query->whereDate('roster_date', '<=', $this->dateTo);
            })
            ->when($this->department, function ($query) {
                $query->where('department_id', $this->department);
            })
            ->when($this->shift, function ($query) {
                $query->where('shift_id', $this->shift);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->shiftType, function ($query) {
                $query->where('shift_type', $this->shiftType);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('roster_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    private function getSummary()
    {
        $query = $this->getRostersQuery();

        return [
            'total' => $query->count(),
            'scheduled' => (clone $query)->where('status', 'scheduled')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    public function render()
    {
        $rosters = $this->getRostersQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();
        $shifts = shifts::where('status', 'active')->orderBy('name')->get();
        $summary = $this->getSummary();

        return view('livewire.hr.roster.viewroster', [
            'rosters' => $rosters,
            'departments' => $departments,
            'shifts' => $shifts,
            'summary' => $summary,
        ]);
    }
}
