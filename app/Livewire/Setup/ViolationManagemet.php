<?php

namespace App\Livewire\Setup;

use App\Models\violations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViolationManagemet extends Component
{
    
    public $violations;
    public $search = '';
    public $violation_type;
    public $description;
    public $violation;
    public $violation_id;
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
            $violation = violations::findOrFail($id);
            $this->violation_id = $id;
            $this->violation_type = $violation->violation_type;
            $this->description = $violation->description;
        } else {
            $this->reset(['violation_type', 'violation_id', 'description']);
        }
    }

    public function listdata()
    {
        $this->violations = violations::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('violation_type', 'like', '%'.$this->search.'%')
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
            'violation_type' => ['required', 'string', 'max:255', 'unique:violations,violation_type'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->violation_id) {
            $violation = violations::findOrFail($this->violation_id);
            $violation->update(['violation_type' => $this->violation_type, 'description' => $this->description]);
            $this->listdata();
            session()->flash('success', 'violation updated successfully!');
        } else {
            violations::create([
                'violation_type' => $this->violation_type,
                'description' => $this->description,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }

        $this->showModal = false;
        $this->reset(['violation_type', 'violation_id']);
    }

    public function delete($uuid)
    {
        $violation = violations::findOrFail($uuid);
        $violation->delete();
        session()->flash('success', 'Data deleted successfully!');
    }

    

    public function render()
    {
        return view('livewire.setup.violation-managemet');
    }
}
