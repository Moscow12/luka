<?php

namespace App\Livewire\Setup;

use App\Models\shifts;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Shiftmngts extends Component
{
    public $shifttypes;
    public $name;
    public $description;
    public $start_time;
    public $end_time;
    public $status ="Active";
    public $count_early;
    public $count_late;
    public $shift;
    public $shift_id;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function mount()
    {
        $this->listdata();
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
            $this->start_time = $shift->start_time;
            $this->end_time = $shift->end_time;
            $this->status = $shift->status;
            $this->count_early = $shift->count_early;
            $this->count_late = $shift->count_late;
        } else {
            $this->reset(['name', 'shift_id', 'start_time', 'end_time', 'status', 'count_early', 'count_late']);
        }
    }

    public function listdata()
    {
        $this->shifttypes = shifts::all();
    }

    public function save()
    {
        
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:shifts,name'],            
            'start_time' => ['required',  'max:255'],
            'end_time' => ['required',  'max:255'],
            'count_early' => ['required', 'string', 'max:255'],
            'count_late' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->shift_id) {
            $shift = shifts::findOrFail($this->shift_id);
            $shift->update(['name' => $this->name,  'start_time' => $this->start_time, 'end_time' => $this->end_time, 'status' => $this->status, 'count_early' => $this->count_early, 'count_late' => $this->count_late]);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            shifts::create([
                'name' => $this->name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'status' => $this->status,
                'count_early' => $this->count_early,
                'count_late' => $this->count_late,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'shift_id',  'start_time', 'end_time', 'count_early', 'count_late']);
    }

    public function delete($uuid)
    {
        $shift = shifts::findOrFail($uuid);
        $shift->delete();
        $this->listdata();
        session()->flash('success', 'Data deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.shiftmngts');
    }
}
