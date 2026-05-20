<?php

namespace App\Livewire\Setup\Location;

use App\Models\districts;
use App\Models\regions;
use App\Models\wards;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Ward extends Component
{
     public $regions, $search = '', $districts;
    public $district_id, $region_id, $ward_id;
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
            $this->district_id = $ward->district_id;
            $this->districts = districts::where('id', $ward->region_id)->get();

        } else {
            $this->districts = districts::all();
            $this->reset(['name', 'district_id',  'district_id']);
        }
    }
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:wards,name'],
            'district_id' => ['required',  'max:255'],
        ]);
        if ($this->modalMode === 'edit' && $this->district_id) {
            $ward = wards::findOrFail($this->district_id);
            $ward->update(['name' => $this->name, 'district_id' => $this->district_id]);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            wards::create([
                'name' => $this->name,
                'district_id' => $this->district_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name', 'district_id']);
    }
    public function listdata()
    {
        
        $this->regions = regions::all();
        $this->districts = districts::all();
    }

    public function delete($uuid)
    {
        $district = wards::findOrFail($uuid);
        $district->delete();
        $this->listdata();
        session()->flash('success', 'Data deleted successfully!');
    }
    public function render()
    {
        $wards = wards::query()
            ->when($this->district_id, fn($q) => $q->where('district_id', $this->district_id))
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.location.ward', ['wards' => $wards]);
    }
}
