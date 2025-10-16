<?php

namespace App\Livewire\Setup;

use App\Models\designations as ModelsDesignations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Designations extends Component
{
    
    public $designations;
    public $name;
    public $code;
    public $designation;
    public $designation_id;
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
            $designation = ModelsDesignations::findOrFail($id);
            $this->designation_id = $id;
            $this->name = $designation->name;
            $this->code = $designation->code;

        } else {
            $this->reset(['name', 'designation_id', 'code']);
        }
    }

    public function listdata()
    {
        $this->designations = ModelsDesignations::with('added_by')->get();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:designations,name'],
            'code' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->designation_id) {
            $designation = ModelsDesignations::findOrFail($this->designation_id);
            $designation->update(['name' => $this->name, 'code' => $this->code]);
            $this->listdata();
            session()->flash('success', 'Designation updated successfully!');
        } else {
            ModelsDesignations::create([
                'name' => $this->name, 
                'code' => $this->code, 
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Designation added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'designation_id', 'code']);
    }

    public function delete($uuid)
    {
        $designation = ModelsDesignations::findOrFail($uuid);
        $designation->delete();
        $this->listdata();
        session()->flash('success', 'Designation deleted successfully!');
    }

   

    public function render()
    {
        return view('livewire.setup.designations');
    }
}
