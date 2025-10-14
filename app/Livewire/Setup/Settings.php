<?php

namespace App\Livewire\Setup;

use App\Models\Workstations;
use Livewire\Component;

class Settings extends Component
{
    public $workstations;
    public function mount()
    {
        $this->workstations = Workstations::all();
    }
    public function render()
    {
        return view('livewire.setup.settings');
    }
}
