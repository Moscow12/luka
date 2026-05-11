<?php

namespace App\Livewire\StorageAndSupply;

use App\Models\Subdepartment;
use Livewire\Component;

class SubdepartmentSelector extends Component
{
    public $subdepartments = [];
    public $selectedSubdepartmentId;
    public $showModal = false;
    public $searchSubdepartment = '';

    public function mount()
    {
        $this->loadSubdepartments();
        $this->selectedSubdepartmentId = session('storage_supply_subdepartment_id');

        // Show modal if no subdepartment selected
        if (!$this->selectedSubdepartmentId) {
            $this->showModal = true;
        }
    }

    public function loadSubdepartments()
    {
        $query = Subdepartment::whereHas('department', function ($query) {
            $query->whereIn('nature_of_department', ['Storage And Supply', 'Pharmacy']);
        })
        ->with('department');

        // Apply search filter
        if ($this->searchSubdepartment) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->searchSubdepartment}%")
                  ->orWhereHas('department', function($dq) {
                      $dq->where('name', 'like', "%{$this->searchSubdepartment}%");
                  });
            });
        }

        $this->subdepartments = $query->orderBy('name')->get();
    }

    public function updatedSearchSubdepartment()
    {
        $this->loadSubdepartments();
    }

    public function selectSubdepartment($subdepartmentId)
    {
        $this->selectedSubdepartmentId = $subdepartmentId;
        session(['storage_supply_subdepartment_id' => $subdepartmentId]);
        $this->showModal = false;

        $this->dispatch('success', 'Subdepartment selected successfully!');

        // Dispatch browser event to reload the page
        $this->dispatch('subdepartment-changed');
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        // Only allow closing if subdepartment is already selected
        if ($this->selectedSubdepartmentId) {
            $this->showModal = false;
        } else {
            $this->dispatch('error', 'You must select a store/subdepartment to continue.');
        }
    }

    public function getSelectedSubdepartmentProperty()
    {
        if ($this->selectedSubdepartmentId) {
            return Subdepartment::find($this->selectedSubdepartmentId);
        }
        return null;
    }

    public function render()
    {
        return view('livewire.storage-and-supply.subdepartment-selector');
    }
}
