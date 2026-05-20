<?php

namespace App\Livewire\Setup\Location;

use App\Models\street as ModelsStreet;
use App\Models\wards;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Street extends Component
{
    public $regions, $search = '', $districts, $wards;
    public $region_id, $ward_id;
    public $name;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public $country_id;
    public function mount()
    {
        $this->listdata();
    }

    #[On('geodata-synced')]
    public function refreshAfterSync()
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
            $ward = wards::findOrFail($id);
            $this->ward_id = $id;
            $this->name = $ward->name;
            $this->ward_id = $ward->ward_id;
            $this->wards = wards::where('id', $ward->ward_id)->get();

        } else {
            $this->wards = wards::all();
            $this->reset(['name', 'ward_id']);
        }
    }
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:streets,name'],
            'ward_id' => ['required',  'max:255'],
        ]);
        if ($this->modalMode === 'edit' && $this->ward_id) {
            $Street = ModelsStreet::findOrFail($this->ward_id);
            $Street->update(['name' => $this->name, 'ward_id' => $this->ward_id]);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            ModelsStreet::create([
                'name' => $this->name,
                'ward_id' => $this->ward_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name', 'ward_id']);
    }
    public function listdata()
    {
        
        $this->wards = wards::all();
    }

    public function delete($uuid)
    {
        $street = ModelsStreet::findOrFail($uuid);
        $street->delete();
        $this->listdata();
        session()->flash('success', 'District deleted successfully!');
    }

    public function render()
    {
        $streets = ModelsStreet::query()
            ->when($this->ward_id, fn($q) => $q->where('ward_id', $this->ward_id))
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.location.street', ['streets' => $streets]);
    }
}
