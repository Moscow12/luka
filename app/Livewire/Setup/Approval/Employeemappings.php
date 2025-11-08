<?php

namespace App\Livewire\Setup\Approval;

use App\Models\approvallevel;
use App\Models\approvalleveltoemployee;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Employeemappings extends Component
{
    public $search = '';
    public $approval_level_employee_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $approval_level_id, $employee_id, $is_active = true;
    public $approvallevels = [];
    public $employees = [];

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $emp = approvalleveltoemployee::findOrFail($id);
            $this->approval_level_employee_id = $id;
            $this->approval_level_id = $emp->approval_level_id;
            $this->employee_id = $emp->employee_id;
            $this->is_active = $emp->is_active;
        } else {
            $this->reset(['approval_level_employee_id', 'approval_level_id', 'employee_id']);
            $this->is_active = true;
        }
    }

    public function save()
    {
        $this->validate([
            'approval_level_id' => ['required', 'exists:approvallevels,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'is_active' => ['boolean'],
        ]);

        if ($this->modalMode === 'edit' && $this->approval_level_employee_id) {
            $emp = approvalleveltoemployee::findOrFail($this->approval_level_employee_id);
            $emp->update([
                'approval_level_id' => $this->approval_level_id,
                'employee_id' => $this->employee_id,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Employee approval mapping updated successfully!');
        } else {
            approvalleveltoemployee::create([
                'approval_level_id' => $this->approval_level_id,
                'employee_id' => $this->employee_id,
                'is_active' => $this->is_active,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Employee approval mapping added successfully!');
        }

        $this->showModal = false;
        $this->reset(['approval_level_employee_id', 'approval_level_id', 'employee_id']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($uuid)
    {
        $emp = approvalleveltoemployee::findOrFail($uuid);
        $emp->delete();
        session()->flash('success', 'Employee approval mapping deleted successfully!');
    }

    public function mount()
    {
        $this->approvallevels = approvallevel::where('is_active', true)->orderBy('level_order')->get();
        $this->employees = Employee::where('status', 'Active')->get();
    }

    public function render()
    {
        $employeeMappings = approvalleveltoemployee::with(['approval_level', 'employee'])
            ->paginate(10);
        return view('livewire.setup.approval.employeemappings', ['employeeMappings' => $employeeMappings]);
    }
}
