<?php

namespace App\Livewire\Setup\Location;

use App\Models\districts;
use App\Models\regions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class District extends Component
{
    public $regions, $search = '';
    public $district_id, $region_id;
    public $name;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public $country_id;
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
            $district = districts::findOrFail($id);
            $this->district_id = $id;
            $this->name = $district->name;
            $this->region_id = $district->region_id;
            $this->regions = regions::where('id', $district->region_id)->get();

        } else {
            $this->regions = regions::all();
            $this->reset(['name', 'district_id',  'region_id']);
        }
    }
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:districts,name'],
            'region_id' => ['required',  'max:255'],
        ]);
        if ($this->modalMode === 'edit' && $this->district_id) {
            $district = districts::findOrFail($this->district_id);
            $district->update(['name' => $this->name, 'region_id' => $this->region_id]);
            $this->listdata();
            session()->flash('success', 'District updated successfully!');
        } else {
            districts::create([
                'name' => $this->name,
                'region_id' => $this->region_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'District added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name', 'district_id']);
    }
    public function listdata()
    {
        
        $this->regions = regions::all();
    }

    public function delete($uuid)
    {
        $district = districts::findOrFail($uuid);
        $district->delete();
        $this->listdata();
        session()->flash('success', 'District deleted successfully!');
    }

    public function render()
    {
        $districts = districts::query()
            ->when($this->region_id, fn($q) => $q->where('region_id', $this->region_id))
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        
        return view('livewire.setup.location.district', ['districts' => $districts]);
    }
}
