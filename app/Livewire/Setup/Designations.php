<?php

namespace App\Livewire\Setup;

use App\Models\designations as ModelsDesignations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Designations extends Component
{
    public $designations;
    public $designation = [
        'name' => '',
        'code' => '',
    ];
    public function mount()
    {
        $this->designations = ModelsDesignations::with('added_by')->get();
    }

    public function storeDesignation()
    {
        //validation
        $this->validate([
            'designation.name' => 'required|string|max:255',
            'designation.code' => 'required|string|max:255',
        ]);
        //save
        
        $this->designation['added_by'] = Auth::user()->id;
        ModelsDesignations::create($this->designation);
        $this->designations = ModelsDesignations::with('added_by')->get();
        session()->flash('success', 'Added successfully!');
    }

   

    public function render()
    {
        return view('livewire.setup.designations');
    }
}
