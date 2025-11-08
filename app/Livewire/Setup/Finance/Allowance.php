<?php

namespace App\Livewire\Setup\Finance;

use App\Models\allowances;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Allowance extends Component
{
    public $search = '';
    public $allowance_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $name, $type = 'fixed', $allowance_value, $taxable = false, $is_active = true, $description;

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $allowance = allowances::findOrFail($id);
            $this->allowance_id = $id;
            $this->name = $allowance->name;
            $this->type = $allowance->type;
            $this->allowance_value = $allowance->allowance_value;
            $this->taxable = $allowance->taxable;
            $this->is_active = $allowance->is_active;
            $this->description = $allowance->description;
        } else {
            $this->reset(['allowance_id', 'name', 'type', 'allowance_value', 'taxable', 'is_active', 'description']);
            $this->type = 'fixed';
            $this->taxable = false;
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:allowances,name' : 'unique:allowances,name,' . $this->allowance_id],
            'type' => ['required', 'in:fixed,percentage'],
            'allowance_value' => ['required', 'numeric', 'min:0'],
            'taxable' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->modalMode === 'edit' && $this->allowance_id) {
            $allowance = allowances::findOrFail($this->allowance_id);
            $allowance->update([
                'name' => $this->name,
                'type' => $this->type,
                'allowance_value' => $this->allowance_value,
                'taxable' => $this->taxable,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Allowance updated successfully!');
        } else {
            allowances::create([
                'name' => $this->name,
                'type' => $this->type,
                'allowance_value' => $this->allowance_value,
                'taxable' => $this->taxable,
                'is_active' => $this->is_active,
                'description' => $this->description,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Allowance added successfully!');
        }

        $this->showModal = false;
        $this->reset(['allowance_id', 'name', 'type', 'allowance_value', 'taxable', 'is_active', 'description']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $allowance = allowances::findOrFail($uuid);
        $allowance->delete();
        session()->flash('success', 'Allowance deleted successfully!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $allowances = allowances::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.finance.allowance', ['allowances' => $allowances]);
    }
}
