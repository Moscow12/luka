<?php

namespace App\Livewire\Setup;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use App\Models\street;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Workstation extends Component
{
    public $workstations, $countries =[], $regions =[], $districts =[], $wards =[], $streets =[];
    public $name;
    public $address;
    public $workst;
    public $workstation_id;
    public $phone_number;
    public $tin_number;
    public $email_address;
    public $region_id;
    public $district_id;
    public $country_id;
    public $postal_code;
    public $physical_address;
    public $ward_id, $street_id;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function mount()
    {
        $this->countries = countries::all();
        $this->regions = regions::where('code', '!=', 'TZ')->get();

        $this->listdata();
    }
    // When a region is selected
    public function updatedRegionId($region_id)
    {
        $this->districts = districts::where('region_id', $region_id)->orderBy('name')->get();
        $this->wards = [];
        $this->streets = [];

        // Reset selections below this level
        $this->district_id = null;
        $this->ward_id = null;
        $this->street_id = null;
    }
     // When a district is selected
    public function updatedDistrictId($districtId)
    {

        $this->wards = wards::where('district_id', $districtId)->orderBy('name')->get();
        $this->streets = [];

        // Reset selections below this level
        $this->ward_id = null;
        $this->street_id = null;
    }

    public function updateDistricts()
    {        
        if ($this->region_id) {
            $this->districts = districts::where('region_id', $this->region_id)->get();  
        } else {
            $this->districts = collect(); // Clear districts if no region is selected
        }
    }

    public function updatewards()
    {
        if ($this->district_id) {
            $this->wards = wards::where('district_id', $this->district_id)->get();
        } else {
            $this->wards = collect(); // Clear wards if no district is selected
        }
    }

    // When a ward is selected
    public function updatestree()
    {
        if ($this->ward_id) {
            $this->streets = street::where('ward_id', $this->ward_id)->orderBy('name')->get();
        } else {
            $this->streets = collect(); // Clear streets if no district is selected
        }  
        // Reset street selection
        $this->street_id = null;
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $workst = workstations::findOrFail($id);
            $this->workstation_id = $id;
            $this->name = $workst->name;
            $this->address = $workst->address;
            $this->phone_number = $workst->phone_number;
            $this->tin_number = $workst->tin_number;
            $this->email_address = $workst->email_address;
            $this->region_id = $workst->region_id;
            $this->district_id = $workst->district_id;
            $this->country_id = $workst->country_id;
            $this->postal_code = $workst->postal_code;
            $this->physical_address = $workst->physical_address;
            $this->ward_id = $workst->ward_id;

        } else {
            $this->reset(['name', 'workstation_id', 'address', 'phone_number', 'tin_number', 'email_address', 'region_id', 'district_id', 'country_id', 'postal_code', 'physical_address', 'ward_id']);
        }
    }

    public function listdata()
    {
        $this->workstations = workstations::with('added_by')->get();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:workstations,name'],
            'address' => ['required', 'string', 'max:255'],
            'phone_number' => ['required'],
            'tin_number' => ['required'],
            'email_address' => ['required', 'email'],
            'region_id' => ['required',  'max:255'],
            'district_id' => ['required',  'max:255'],
            'country_id' => ['required',  'max:255'],
            'postal_code' => ['required', 'string', 'max:255'],
            'physical_address' => ['required', 'string', 'max:255'],
            'ward_id' => ['required', 'max:255'],

        ]);

        if ($this->modalMode === 'edit' && $this->workstation_id) {
            $workst = workstations::findOrFail($this->workstation_id);
            $workst->update(['workstation_name' => $this->name, 'address' => $this->address, 'phone_number' => $this->phone_number, 'tin_number' => $this->tin_number, 'email_address' => $this->email_address, 'region_id' => $this->region_id, 'district_id' => $this->district_id, 'country_id' => $this->country_id, 'postal_code' => $this->postal_code, 'physical_address' => $this->physical_address, 'ward_id' => $this->ward_id]);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            workstations::create([
                'workstation_name' => $this->name, 
                'address' => $this->address, 
                'phone_number' => $this->phone_number, 
                'tin_number' => $this->tin_number, 
                'email_address' => $this->email_address, 
                'region_id' => $this->region_id, 
                'district_id' => $this->district_id, 
                'country_id' => $this->country_id, 
                'postal_code' => $this->postal_code, 
                'physical_address' => $this->physical_address, 
                'ward_id' => $this->ward_id,
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'workstation_id', 'address', 'phone_number', 'tin_number', 'email_address', 'region_id', 'district_id', 'country_id', 'postal_code', 'physical_address', 'ward_id']);
    }

    public function delete($uuid)
    {
        $workst = workstations::findOrFail($uuid);
        $workst->delete();
        $this->listdata();
        session()->flash('success', 'Data deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.workstation');
    }
}
