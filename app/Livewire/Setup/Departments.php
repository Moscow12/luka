<?php

namespace App\Livewire\Setup;

use App\Models\approvallevel;
use App\Models\departments as ModelsDepartments;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Departments extends Component
{
    public $departments;

    public $approvalLevels;

    public $search = '';

    public $name;

    public $description;

    public $approval_level_id;

    public $department;

    public $department_id;

    public $modalMode = 'create'; // or 'edit'

    public $showModal = false;

    public function mount()
    {
        $this->listdata();
        $this->approvalLevels = approvallevel::where('is_active', true)->orderBy('level_order')->get();
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
            $this->approval_level_id = $department->approval_level_id;

        } else {
            $this->reset(['name', 'department_id', 'description', 'approval_level_id']);
        }
    }

    public function listdata()
    {
        $this->departments = ModelsDepartments::with(['added_by', 'approval_level'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->get();
    }

    public function updatedSearch()
    {
        $this->listdata();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,'.$this->department_id],
            'description' => ['required', 'string', 'max:255'],
            'approval_level_id' => ['nullable', 'exists:approvallevels,id'],
        ]);

        if ($this->modalMode === 'edit' && $this->department_id) {
            $department = ModelsDepartments::findOrFail($this->department_id);
            $department->update([
                'name' => $this->name,
                'description' => $this->description,
                'approval_level_id' => $this->approval_level_id,
            ]);
            $this->listdata();
            session()->flash('success', 'Department updated successfully!');
        } else {
            ModelsDepartments::create([
                'name' => $this->name,
                'description' => $this->description,
                'approval_level_id' => $this->approval_level_id,
                'added_by' => Auth::user()->id,
            ]);
            $this->listdata();
            session()->flash('success', 'Department added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'department_id', 'approval_level_id']);
    }

    public function delete($uuid)
    {
        $department = ModelsDepartments::findOrFail($uuid);
        $department->delete();
        $this->listdata();
        session()->flash('success', 'Department deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.departments');
    }
}
