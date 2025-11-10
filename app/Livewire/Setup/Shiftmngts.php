<?php

namespace App\Livewire\Setup;

use App\Models\shifts;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Shiftmngts extends Component
{
    use WithPagination;

    // Form properties
    public $name;

    public $description;

    public $start_time;

    public $end_time;

    public $status = 'active';

    public $count_early;

    public $count_late;

    public $shift_id;

    // UI State
    public $modalMode = 'create'; // 'create' or 'edit'

    public $showModal = false;

    // Filters
    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    public $perPage = 10;

    // Sort
    public $sortField = 'name';

    public $sortDirection = 'asc';

    public function mount()
    {
        // Initialize component
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $shift = shifts::findOrFail($id);
            $this->shift_id = $id;
            $this->name = $shift->name;
            $this->description = $shift->description;
            $this->start_time = $shift->start_time;
            $this->end_time = $shift->end_time;
            $this->status = $shift->status;
            $this->count_early = $shift->count_early;
            $this->count_late = $shift->count_late;
        } else {
            $this->reset([
                'name',
                'description',
                'shift_id',
                'start_time',
                'end_time',
                'status',
                'count_early',
                'count_late',
            ]);
            $this->status = 'active';
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset([
            'name',
            'description',
            'shift_id',
            'start_time',
            'end_time',
            'status',
            'count_early',
            'count_late',
            'modalMode',
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'in:active,inactive'],
            'count_early' => ['required', 'integer', 'min:0', 'max:999'],
            'count_late' => ['required', 'integer', 'min:0', 'max:999'],
        ];

        // Add unique validation for name, excluding current shift on edit
        if ($this->modalMode === 'edit' && $this->shift_id) {
            $rules['name'][] = 'unique:shifts,name,'.$this->shift_id;
        } else {
            $rules['name'][] = 'unique:shifts,name';
        }

        $this->validate($rules);

        try {
            if ($this->modalMode === 'edit' && $this->shift_id) {
                // Update existing shift
                $shift = shifts::findOrFail($this->shift_id);
                $shift->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'status' => $this->status,
                    'count_early' => $this->count_early,
                    'count_late' => $this->count_late,
                ]);

                session()->flash('success', 'Shift updated successfully!');
            } else {
                // Create new shift
                shifts::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'status' => $this->status,
                    'count_early' => $this->count_early,
                    'count_late' => $this->count_late,
                    'added_by' => Auth::user()->id,
                ]);

                session()->flash('success', 'Shift created successfully!');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $shift = shifts::findOrFail($id);

            // Check if shift is being used in rosters
            $rosterCount = $shift->rosters()->count();

            if ($rosterCount > 0) {
                session()->flash('error', "Cannot delete shift. It is being used in {$rosterCount} roster(s).");

                return;
            }

            $shift->delete();
            session()->flash('success', 'Shift deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while deleting the shift.');
        }
    }

    public function toggleStatus($id)
    {
        try {
            $shift = shifts::findOrFail($id);
            $shift->update([
                'status' => $shift->status === 'active' ? 'inactive' : 'active',
            ]);

            session()->flash('success', 'Shift status updated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while updating the status.');
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    private function getShiftsQuery()
    {
        return shifts::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $shifts = $this->getShiftsQuery()->paginate($this->perPage);

        $summary = [
            'total' => shifts::count(),
            'active' => shifts::where('status', 'active')->count(),
            'inactive' => shifts::where('status', 'inactive')->count(),
        ];

        return view('livewire.setup.shiftmngts', [
            'shifttypes' => $shifts,
            'summary' => $summary,
        ]);
    }
}
