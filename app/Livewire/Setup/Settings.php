<?php

namespace App\Livewire\Setup;

use App\Models\countries;
use App\Models\Workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    public $workstations, $countries =[], $region =[], $district =[];
    public $warkstation =[
        'name' => '',
        'ward_id' => '',
        'address' => '',
        'phone_number' => '',
        'tin_number' => '',
        'email_address' => '',
        'region_id' => '',
        'district_id' => '',
        'country_id' => '',
        'postal_code' => '',
        'physical_address' => '',
    ];
    public function mount()
    {
        $this->workstations = Workstations::all();
        $this->countries = countries::all();
    }

    public function storeWorkstation()
    {
        //validation
        $this->validate([
            'workstation.name' => 'required|string|max:255',
            'workstation.location' => 'required|string|max:255',
            'workstation.phone_number' => 'required|string|max:255',
            'workstation.tin_number' => 'required|string|max:255',
            'workstation.physical_address' => 'required|string|max:255',
            'workstation.region_id' => 'required|string|max:255',
            'workstation.district_id' => 'required|string|max:255',
            'workstation.ward_id' => 'required|string|max:255',
            'workstation.country_id' => 'required|string|max:255',
            'workstation.postal_code' => 'required|string|max:255',
        ]);
        //save
        $this->warkstation['added_by'] = Auth::user()->id;
        Workstations::create($this->warkstation);
        $this->workstations = Workstations::all();
        session()->flash('success', 'Added successfully!');
    }
    public function render()
    {
        return view('livewire.setup.settings');
    }
}
