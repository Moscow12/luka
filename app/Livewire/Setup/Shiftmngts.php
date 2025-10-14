<?php

namespace App\Livewire\Setup;

use App\Models\shifts;
use Livewire\Component;

class Shiftmngts extends Component
{
    public $shifts;
    public function mount()
    {
        $this->shifts = shifts::all();
    }

    public function render()
    {
        return view('livewire.setup.shiftmngts');
    }
}
