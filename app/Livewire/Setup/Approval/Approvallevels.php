<?php

namespace App\Livewire\Setup\Approval;

use App\Models\approvallevel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Approvallevels extends Component
{
    public $search = '';
    public $approval_level_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $name, $description, $level_order, $is_active = true;

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $level = approvallevel::findOrFail($id);
            $this->approval_level_id = $id;
            $this->name = $level->name;
            $this->description = $level->description;
            $this->level_order = $level->level_order;
            $this->is_active = $level->is_active;
        } else {
            $this->reset(['approval_level_id', 'name', 'description', 'level_order']);
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:approvallevels,name' : 'unique:approvallevels,name,' . $this->approval_level_id],
            'description' => ['nullable', 'string'],
            'level_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ]);

        if ($this->modalMode === 'edit' && $this->approval_level_id) {
            $level = approvallevel::findOrFail($this->approval_level_id);
            $level->update([
                'name' => $this->name,
                'description' => $this->description,
                'level_order' => $this->level_order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Approval level updated successfully!');
        } else {
            approvallevel::create([
                'name' => $this->name,
                'description' => $this->description,
                'level_order' => $this->level_order,
                'is_active' => $this->is_active,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Approval level added successfully!');
        }

        $this->showModal = false;
        $this->reset(['approval_level_id', 'name', 'description', 'level_order']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $level = approvallevel::findOrFail($uuid);
        $level->delete();
        session()->flash('success', 'Approval level deleted successfully!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $approvallevels = approvallevel::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('level_order')
            ->paginate(10);
        return view('livewire.setup.approval.approvallevels', ['approvallevels' => $approvallevels]);
    }
}
