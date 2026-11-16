<?php

namespace App\Livewire\Setup;

use App\Livewire\Concerns\ManagesRecordStatus;
use App\Models\designations as ModelsDesignations;
use App\Support\RecordUsage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Designations extends Component
{
    use ManagesRecordStatus;

    public $designations;

    public $search = '';

    public $name;

    public $code;

    public $designation;

    public $designation_id;

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
            $designation = ModelsDesignations::findOrFail($id);
            $this->designation_id = $id;
            $this->name = $designation->name;
            $this->code = $designation->code;

        } else {
            $this->reset(['name', 'designation_id', 'code']);
        }
    }

    public function listdata()
    {
        $this->designations = ModelsDesignations::with('added_by')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->get();
    }

    public function updatedSearch()
    {
        $this->listdata();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:designations,name,'.$this->designation_id],
            'code' => ['required', 'string', 'max:255'],
        ]);

        if ($this->modalMode === 'edit' && $this->designation_id) {
            $designation = ModelsDesignations::findOrFail($this->designation_id);
            $designation->update(['name' => $this->name, 'code' => $this->code]);
            $this->listdata();
            session()->flash('success', 'Designation updated successfully!');
        } else {
            ModelsDesignations::create([
                'name' => $this->name,
                'code' => $this->code,
                'added_by' => Auth::user()->id,
            ]);
            $this->listdata();
            session()->flash('success', 'Designation added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'designation_id', 'code']);
    }

    /** Deletes only a designation nothing refers to; one in use is deactivated instead. */
    public function delete($uuid)
    {
        $designation = ModelsDesignations::findOrFail($uuid);
        $this->deleteUnlessInUse($designation, "Designation {$designation->name}");
        $this->listdata();
    }

    public function toggleStatus($uuid)
    {
        $designation = ModelsDesignations::findOrFail($uuid);
        $this->toggleRecordStatus($designation, "Designation {$designation->name}");
        $this->listdata();
    }

    public function render()
    {
        return view('livewire.setup.designations', [
            'usedIds' => RecordUsage::usedIds('designations', $this->designations->pluck('id')),
        ]);
    }
}
