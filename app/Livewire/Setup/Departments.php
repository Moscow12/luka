<?php

namespace App\Livewire\Setup;

use App\Models\departments as ModelsDepartments;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Departments extends Component
{
    public $departments;
    public $name;
    public $description;
    public $department;
    public $department_id;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function mount()
    {
        $this->listdepts();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $department = ModelsDepartments::findOrFail($id);
            $this->department_id = $id;
            $this->name = $department->name;
            $this->description = $department->description;
        } else {
            $this->reset(['name', 'department_id', 'description']);
        }
    }

    public function listdepts()
    {
        $this->departments = ModelsDepartments::with('added_by')->get();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->department_id) {
            $department = ModelsDepartments::findOrFail($this->department_id);
            $department->update(['name' => $this->name, 'description' => $this->description]);
            $this->listdepts();
            session()->flash('success', 'Department updated successfully!');
        } else {
            ModelsDepartments::create([
                'name' => $this->name, 
                'description' => $this->description, 
                'added_by' => Auth::user()->id
            ]);
            $this->listdepts();
            session()->flash('success', 'Department added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'department_id']);
    }

    public function delete($uuid)
    {
        $department = ModelsDepartments::findOrFail($uuid);
        $department->delete();
        session()->flash('success', 'Department deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.departments');
    }
}
