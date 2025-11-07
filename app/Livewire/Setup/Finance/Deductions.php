<?php

namespace App\Livewire\Setup\Finance;

use App\Models\Deductions as ModelsDeductions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Deductions extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $deduction_id, $name, $modepercentage="false",$Mode='yes', $Deduction_Type, $Amount, $Description, $is_next_of_kin;

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $deduction = ModelsDeductions::findOrFail($id);
            $this->name = $deduction->name;
            $this->modepercentage = $deduction->modepercentage;
            $this->Deduction_Type = $deduction->Deduction_Type;
            $this->Mode = $deduction->Mode;
            $this->Amount = $deduction->Amount;
            $this->Description = $deduction->Description;
        } else {
            $this->reset(['name', 'deduction_id', 'modepercentage', 'Deduction_Type', 'Amount', 'Description']);
        }
    }
    
    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'Deduction_Type' => ['required', 'string', 'max:255'],
            'Mode' => ['required', 'string', 'max:255'],
            'Amount' => ['required', 'string', 'max:255'],
            'Description' => ['nullable', 'string'],

        ]);

        if ($this->modalMode === 'edit' && $this->deduction_id) {
            $deduction = ModelsDeductions::findOrFail($this->deduction_id);
            $deduction->update(['name' => $this->name, 'modepercentage' => $this->modepercentage,'Mode' => $this->Mode, 'Deduction_Type' => $this->Deduction_Type, 'Amount' => $this->Amount, 'Description' => $this->Description]);
            $this->listdata();
            session()->flash('success', 'Deduction updated successfully!');
        } else {
             $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:deductions,name'],
             ]);
            ModelsDeductions::create([
                'name' => $this->name,
                'modepercentage' => $this->modepercentage ? 'true' : 'false',
                'Deduction_Type' => $this->Deduction_Type,
                'Mode' => $this->Mode,
                'Amount' => $this->Amount,
                'Description' => $this->Description,
                'added_by' => Auth::user()->id

            ]);
            session()->flash('success', 'Deduction added successfully!');
        }
        $this->showModal = false;
        $this->reset(['name',  'deduction_id','Mode', 'modepercentage', 'Deduction_Type', 'Amount', 'Description']);
    }
    public function delete($uuid)
    {
        $deduction = ModelsDeductions::findOrFail($uuid);
        $deduction->delete();
        $this->listdata();
        session()->flash('success', 'Deduction deleted successfully!');
    }

    public function mount()
    {
        $this->listdata();
    }

    public function listdata()
    {
        //
    }
    public function render()
    {
        $deductions = ModelsDeductions::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.finance.deductions', ['deductions' => $deductions]);
    }
}
