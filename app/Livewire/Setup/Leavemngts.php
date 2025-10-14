<?php

namespace App\Livewire\Setup;

use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Leavemngts extends Component
{
    public $leavetypes, $leavetype;
    public function mount()
    {
        $this->leavetypes = Leaves::with('added_by')->get();
    }

    public function storeLeaveType()
    {
        //validation
        $this->validate([
            'leavetype.name' => 'required|string|max:255',
            'leavetype.description' => 'required|string|max:255',
            'leavetype.days' => 'required|string|max:255',
            'leavetype.gender' => 'required|string|max:255',
        ]);
        //save
        $this->leavetype['added_by'] = Auth::user()->id;
        Leaves::create($this->leavetype);
        $this->leavetypes = Leaves::with('added_by')->get();
        session()->flash('success', 'Added successfully!');
    }
    public function render()
    {
        return view('livewire.setup.leavemngts');
    }
}
