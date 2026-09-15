<?php

namespace App\Livewire\Setup\Location;

use App\Models\countries;
use Livewire\Component;
use Livewire\WithPagination;

class Nations extends Component
{
    use WithPagination;
    public $perPage = 10;
    public $name;
    public $code;
    public $country;
    public $country_id;
    public $search='';
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
            $country = countries::findOrFail($id);
            $this->country_id = $id;
            $this->name = $country->name;
            $this->code = $country->code;

        } else {
            $this->reset(['name', 'country_id', 'code']);
        }
    }

    public function listdata()
    {
        // pagenate countries
        
    }
    public function render()
    {

        $countries = countries::where('name', 'like', '%' . $this->search . '%')->paginate(50);
        return view('livewire.setup.location.nations', ['countries' => $countries]);
    }
}
