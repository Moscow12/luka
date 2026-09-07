<?php

namespace App\Livewire\Setup;

use App\Models\departments as ModelsDepartments;
use App\Models\DepartmentStore;
use App\Models\facilitylocation;
use Livewire\Component;

class StoreManagement extends Component
{
    public $stores;

    public $departmentList;

    public $locationList;

    public $search = '';

    public $name;

    public $department_id;

    public $location_id;

    public $status = 'active';

    public $store_id;

    public $modalMode = 'create'; // or 'edit'

    public $showModal = false;

    public function mount()
    {
        $this->listdata();
        $this->departmentList = ModelsDepartments::orderBy('name')->get();
        $this->locationList = facilitylocation::orderBy('name')->get();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $store = DepartmentStore::findOrFail($id);
            $this->store_id = $id;
            $this->name = $store->name;
            $this->department_id = $store->department_id;
            $this->location_id = $store->location_id;
            $this->status = $store->status;
        } else {
            $this->reset(['name', 'store_id', 'department_id', 'location_id']);
            $this->status = 'active';
        }
    }

    public function listdata()
    {
        $this->stores = DepartmentStore::with(['department', 'location'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhereHas('department', function ($dq) {
                            $dq->where('name', 'like', '%'.$this->search.'%');
                        });
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
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'location_id' => ['required', 'exists:facilitylocations,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if ($this->modalMode === 'edit' && $this->store_id) {
            $store = DepartmentStore::findOrFail($this->store_id);
            $store->update([
                'name' => $this->name,
                'department_id' => $this->department_id,
                'location_id' => $this->location_id,
                'status' => $this->status,
            ]);
            $this->listdata();
            session()->flash('success', 'Store updated successfully!');
        } else {
            DepartmentStore::create([
                'name' => $this->name,
                'department_id' => $this->department_id,
                'location_id' => $this->location_id,
                'status' => $this->status,
            ]);
            $this->listdata();
            session()->flash('success', 'Store added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'store_id', 'department_id', 'location_id']);
    }

    public function delete($uuid)
    {
        $store = DepartmentStore::findOrFail($uuid);
        $store->delete();
        $this->listdata();
        session()->flash('success', 'Store deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.store-management');
    }
}
