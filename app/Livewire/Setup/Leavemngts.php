<?php

namespace App\Livewire\Setup;

use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Leavemngts extends Component
{
    public $leavetypes, $leavetype, $days, $gender, $status= true, $description, $name;
    
    public $leave_id;
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
            $leave = leaves::findOrFail($id);
            $this->leave_id = $id;
            $this->name = $leave->name;
            $this->description = $leave->description;
            $this->days = $leave->days;
            $this->gender = $leave->gender;
        } else {
            $this->reset(['name', 'leave_id', 'description', 'days', 'gender']);
        }
    }

    public function listdata()
    {
        $this->leavetypes = leaves::all();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:leaves,name'],            
            'description' => ['required', 'string', 'max:255'],
            'days' => ['required', 'numeric', 'max:255'],
            'gender' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->leave_id) {
            $shift = leaves::findOrFail($this->leave_id);
            $shift->update(['name' => $this->name,  'description' => $this->description, 'days' => $this->days,  'gender' => $this->gender]);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            leaves::create([
                'name' => $this->name,
                'description' => $this->description,
                'days' => $this->days,
                'status' => $this->status,
                'gender' => $this->gender,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'leave_id',  'description', 'days', 'status', 'gender']);
    }

    public function delete($uuid)
    {
        $shift = leaves::findOrFail($uuid);
        $shift->delete();
        $this->listdata();
        session()->flash('success', 'Data deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.leavemngts');
    }
}
