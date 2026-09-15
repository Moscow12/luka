<?php

namespace App\Livewire\Setup;

use App\Models\TerminationReason;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TerminationReasons extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $name = '';

    public $description = '';

    public $type = 'both';

    public $is_active = true;

    public $editingId = null;

    public $showModal = false;

    public $search = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:termination_reasons,name,'.$this->editingId,
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:termination,non_renewal,both',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();

        if ($id) {
            $reason = TerminationReason::findOrFail($id);
            $this->editingId = $id;
            $this->name = $reason->name;
            $this->description = $reason->description;
            $this->type = $reason->type;
            $this->is_active = $reason->is_active;
        } else {
            $this->reset(['name', 'description', 'type', 'is_active', 'editingId']);
            $this->type = 'both';
            $this->is_active = true;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'description', 'type', 'is_active', 'editingId']);
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $reason = TerminationReason::findOrFail($this->editingId);
            $reason->update([
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Termination reason updated successfully.');
        } else {
            TerminationReason::create([
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'is_active' => $this->is_active,
                'added_by' => Auth::id(),
            ]);
            session()->flash('success', 'Termination reason added successfully.');
        }

        $this->closeModal();
    }

    public function toggleStatus($id)
    {
        $reason = TerminationReason::findOrFail($id);
        $reason->update(['is_active' => ! $reason->is_active]);
        session()->flash('success', 'Status updated successfully.');
    }

    public function delete($id)
    {
        $reason = TerminationReason::findOrFail($id);

        // Check if it's being used
        if ($reason->contractRequests()->exists()) {
            session()->flash('error', 'Cannot delete this reason as it is being used by contract requests.');

            return;
        }

        $reason->delete();
        session()->flash('success', 'Termination reason deleted successfully.');
    }

    public function render()
    {
        $reasons = TerminationReason::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.setup.termination-reasons', [
            'reasons' => $reasons,
        ]);
    }
}
