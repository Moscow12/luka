<?php

namespace App\Livewire\Setup\Location;

use App\Models\countries;
use App\Models\regions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Region extends Component
{
    public $search = '', $country_id, $countries;
    public $region_id;

    public $name;
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
            $region = regions::findOrFail($id);
            $this->region_id = $id;
            $this->name = $region->name;
        } else {
            $this->reset(['name', 'region_id']);
        }
    }
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:regions,name'],
            'country_id' => ['required',  'max:255'],
        ]);
        if ($this->modalMode === 'edit' && $this->region_id) {
            $region = regions::findOrFail($this->region_id);
            $region->update(['name' => $this->name, 'country_id' => $this->country_id]);
            $this->listdata();
            session()->flash('success', 'Region updated successfully!');
        } else {
            regions::create([
                'name' => $this->name,
                'country_id' => $this->country_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Region added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name', 'region_id']);
    }
    public function listdata()
    {
        $this->countries = countries::get();
    }
    public function delete($uuid)
    {
        $region = regions::findOrFail($uuid);
        $region->delete();
        $this->listdata();
        session()->flash('success', 'Region deleted successfully!');
    }
    public function render()
    {
        $regions = regions::where('name', 'like', '%' . $this->search . '%')->paginate(10);
        return view('livewire.setup.location.region', ['regions' => $regions]);
    }
}
