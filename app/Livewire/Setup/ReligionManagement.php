<?php

namespace App\Livewire\Setup;

use App\Models\regions;
use App\Models\religions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReligionManagement extends Component
{
    public $religions;
    public $name;
    public $description;
    public $religion;
    public $religion_id;
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
            $religion = religions::findOrFail($id);
            $this->religion_id = $id;
            $this->name = $religion->name;
            $this->description = $religion->description;
        } else {
            $this->reset(['name', 'religion_id', 'description']);
        }
    }

    public function listdata()
    {
        $this->religions = religions::all();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:religions,name'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->religion_id) {
            $religion = religions::findOrFail($this->religion_id);
            $religion->update(['name' => $this->name, 'description' => $this->description]);
            $this->listdata();
            session()->flash('success', 'Religion updated successfully!');
        } else {
            religions::create([
                'name' => $this->name,
                'description' => $this->description,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Religion added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'religion_id']);
    }

    public function delete($uuid)
    {
        $religion = religions::findOrFail($uuid);
        $religion->delete();
        session()->flash('success', 'Religion deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.religion-management');
    }
}
