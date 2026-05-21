<?php

namespace App\Livewire\Setup;

use App\Models\Leaves;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Leavemngts extends Component
{
    public $leavetypes;

    public $leavetype;

    public $days;

    public $gender;

    public $status = 'active';

    public $description;

    public $name;

    public $require_document = false;

    public $paid = false;

    public $leave_id;

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
            $leave = leaves::findOrFail($id);
            $this->leave_id = $id;
            $this->name = $leave->name;
            $this->description = $leave->description;
            $this->days = $leave->days;
            $this->gender = $leave->gender;
            $this->require_document = (bool) $leave->require_document;
            $this->paid = (bool) $leave->paid;
        } else {
            $this->reset(['name', 'leave_id', 'description', 'days', 'gender', 'require_document', 'paid']);
        }
    }

    public function listdata()
    {
        $this->leavetypes = leaves::all();
    }

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'days' => ['required', 'numeric', 'max:255'],
            'gender' => ['required', 'string', 'max:255'],
        ];

        // Add unique validation only for create or when name changes
        if ($this->modalMode === 'create') {
            $rules['name'][] = 'unique:leaves,name';
        } elseif ($this->modalMode === 'edit' && $this->leave_id) {
            $rules['name'][] = 'unique:leaves,name,'.$this->leave_id;
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'days' => $this->days,
            'gender' => $this->gender,
            'require_document' => (bool) $this->require_document,
            'paid' => (bool) $this->paid,
        ];

        if ($this->modalMode === 'edit' && $this->leave_id) {
            $shift = leaves::findOrFail($this->leave_id);
            $shift->update($data);
            $this->listdata();
            session()->flash('success', 'Data updated successfully!');
        } else {
            $data['status'] = $this->status;
            $data['added_by'] = Auth::user()->id;
            leaves::create($data);
            $this->listdata();
            session()->flash('success', 'Data added successfully!');
        }

        $this->showModal = false;
        $this->reset(['name', 'leave_id', 'description', 'days', 'status', 'gender', 'require_document', 'paid']);
    }

    public function delete($uuid)
    {
        $user = Auth::user();

        if (! $user->can('manage-leave') && ! $user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to delete leave types.');

            return;
        }

        $shift = leaves::findOrFail($uuid);
        $shift->delete();
        $this->listdata();
        session()->flash('success', 'Data deleted successfully!');
    }

    public function render()
    {
        return view('livewire.setup.leavemngts');
    }
}
