<?php

namespace App\Livewire\Setup\Finance;

use App\Models\Deduction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Deductions extends Component
{
    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $deduction_id, $name, $type = 'fixed', $deduction_value, $applies_to, $is_active = true, $description;

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $deduction = Deduction::findOrFail($id);
            $this->deduction_id = $id;
            $this->name = $deduction->name;
            $this->type = $deduction->type;
            $this->deduction_value = $deduction->deduction_value;
            $this->applies_to = $deduction->applies_to;
            $this->is_active = $deduction->is_active;
            $this->description = $deduction->description;
        } else {
            $this->reset(['deduction_id', 'name', 'type', 'deduction_value', 'applies_to', 'is_active', 'description']);
            $this->type = 'fixed';
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:deductions,name' : 'unique:deductions,name,' . $this->deduction_id],
            'type' => ['required', 'in:fixed,percentage'],
            'deduction_value' => ['required', 'numeric', 'min:0'],
            'applies_to' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->modalMode === 'edit' && $this->deduction_id) {
            $deduction = Deduction::findOrFail($this->deduction_id);
            $deduction->update([
                'name' => $this->name,
                'type' => $this->type,
                'deduction_value' => $this->deduction_value,
                'applies_to' => $this->applies_to,
                'is_active' => $this->is_active,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Deduction updated successfully!');
        } else {
            Deduction::create([
                'name' => $this->name,
                'type' => $this->type,
                'deduction_value' => $this->deduction_value,
                'applies_to' => $this->applies_to,
                'is_active' => $this->is_active,
                'description' => $this->description,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Deduction added successfully!');
        }

        $this->showModal = false;
        $this->reset(['deduction_id', 'name', 'type', 'deduction_value', 'applies_to', 'is_active', 'description']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $deduction = Deduction::findOrFail($uuid);
        $deduction->delete();
        session()->flash('success', 'Deduction deleted successfully!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $deductions = Deduction::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.finance.deductions', ['deductions' => $deductions]);
    }
}
