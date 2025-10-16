<?php

namespace App\Livewire\Setup;

use App\Models\Jobtitle;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Jobtitles extends Component
{
    public $jobtitles;
    public $name;
    public $description;
    public $jobtitle;
    public $jobtitle_id;
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
            $jobtitle = Jobtitle::findOrFail($id);
            $this->jobtitle_id = $id;
            $this->name = $jobtitle->name;
            $this->description = $jobtitle->description;

        } else {
            $this->reset(['name', 'jobtitle_id', 'description']);
        }
    }

    public function listdata()
    {
        $this->jobtitles = Jobtitle::all();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:jobtitles,name'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->jobtitle_id) {
            $jobtitle = Jobtitle::findOrFail($this->jobtitle_id);
            $jobtitle->update(['name' => $this->name, 'description' => $this->description]);
            $this->listdata();
            session()->flash('success', 'Job Title updated successfully!');
        } else {
            Jobtitle::create([
                'name' => $this->name, 
                'description' => $this->description, 
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Job Title added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'jobtitle_id']);
    }

    public function delete($uuid)
    {
        $jobtitle = Jobtitle::findOrFail($uuid);
        $jobtitle->delete();
        $this->listdata();
        session()->flash('success', 'Job Title deleted successfully!');
    }
    public function render()
    {
        return view('livewire.setup.jobtitles');
    }
}
