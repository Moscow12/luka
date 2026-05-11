<?php

namespace App\Livewire\StorageAndSupply\Grn;

use Livewire\Component;

class GrnWithoutLpo extends Component
{
    public function render()
    {
        // This component now simply delegates to the list component
        return view('livewire.storage-and-supply.grn.grn-without-lpo');
    }
}
