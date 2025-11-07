<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\{workstations, departments, Employee, Employeecontracts, Jobtitle};
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Contracts extends Component
{
    use WithFileUploads;

    public $search = '';
    public $modalMode = 'create';
    public $showModal = false;
    public $employee_id, $workstation_id, $department_id, $position_id, $workstations, $departments, $positions, $editmode = false;
    public $first_name, $middle_name, $last_name, $gender, $getfullname, $age, $email, $editUrl;
    public $contract_type, $start_date, $expire_date, $expirenotification, $description, $attachment, $contract_id, $contracts=[];
    public $canAddNewContract = true;
    public $activeContractMessage = '';
    public function mount($id = null)
    {
        $staff = Employee::findOrFail($id);
        $this->employee_id = $id;

        $this->first_name = $staff->first_name;
        $this->middle_name = $staff->middle_name;
        $this->last_name = $staff->last_name;
        $this->getfullname = $staff->getFullName();
        $this->age = $staff->getAgeAttribute();
        $this->gender = $staff->gender;
        $this->email = $staff->email;
        $this->editUrl = route('hr.editstaff', $id);
        $this->workstations = workstations::all();
        $this->departments = departments::all();
        $this->positions = Jobtitle::all();
        $this->listdata();
    }

    public function save()
    {

        $this->validate([
            'workstation_id' => 'required',
            'department_id' => 'required',
            'position_id' => 'required',
            'contract_type' => 'required',
            'start_date' => 'required',
            'expire_date' => 'required',
            'expirenotification' => 'required',
            'description' => 'required',
            'attachment' =>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // max 2MB
        ]);
        // store attachment in storage
        $paths = null;
        if ($this->attachment) {
            $paths = $this->attachment->store('contracts', 'public');
        }
        // save contract / edit contract if editmode is on / create new contract if editmode is off
        if ($this->editmode) {
            $contract = Employeecontracts::findOrFail($this->contract_id);
            $contract->update([
                'workstation_id' => $this->workstation_id,
                'department_id' => $this->department_id,
                'position_id' => $this->position_id,
                'contract_type' => $this->contract_type,
                'start_date' => $this->start_date,
                'expire_date' => $this->expire_date,
                'expirenotification' => $this->expirenotification,
                'description' => $this->description,
                'attachment' => $paths ?? $contract->attachment,
            ]);
            session()->flash('success', 'Contract updated successfully!');
        } else {

            Employeecontracts::create([
                'employee_id' => $this->employee_id,
                'workstation_id' => $this->workstation_id,
                'department_id' => $this->department_id,
                'position_id' => $this->position_id,
                'contract_type' => $this->contract_type,
                'start_date' => $this->start_date,
                'expire_date' => $this->expire_date,
                'expirenotification' => $this->expirenotification,
                'description' => $this->description,
                'attachment' => $paths,
                'added_by' => Auth::user()->id
            ]);
            session()->flash('success', 'Contract added successfully!');
        }

        $this->editmode = false;
    }
    public function listdata()
    {
        $this->contracts = Employeecontracts::where('employee_id', $this->employee_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->checkCanAddNewContract();
    }

    /**
     * Check if user can add a new contract
     */
    public function checkCanAddNewContract()
    {
        $activeContract = Employeecontracts::where('employee_id', $this->employee_id)
            ->where('status', 'active')
            ->first();

        if (!$activeContract) {
            $this->canAddNewContract = true;
            $this->activeContractMessage = '';
            return;
        }

        // Allow new contract if current is expired, suspended, or about to expire
        if ($activeContract->isExpired()) {
            $this->canAddNewContract = true;
            $this->activeContractMessage = 'Previous contract has expired. You can add a new contract.';
        } elseif ($activeContract->isSuspended()) {
            $this->canAddNewContract = true;
            $this->activeContractMessage = 'Current contract is suspended. You can add a new contract.';
        } elseif ($activeContract->isAboutToExpire()) {
            $this->canAddNewContract = true;
            $this->activeContractMessage = 'Current contract is expiring soon. You can add a new contract.';
        } else {
            $this->canAddNewContract = false;
            $this->activeContractMessage = 'Employee has an active contract. New contracts can only be added when the current contract is expired, suspended, or about to expire.';
        }
    }

    public function delete($uuid)
    {
        $contract = Employeecontracts::findOrFail($uuid);
        $contract->delete();
        $this->listdata();
        session()->flash('success', 'Contract deleted successfully!');
    }
    public function openModal($mode = 'create', $id = null)
    {
        // Check if user can add new contract
        if ($mode === 'create' && !$this->canAddNewContract) {
            session()->flash('error', $this->activeContractMessage);
            return;
        }

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $contract = Employeecontracts::findOrFail($id);
            $this->contract_id = $id;
            $this->workstation_id = $contract->workstation_id;
            $this->department_id = $contract->department_id;
            $this->position_id = $contract->position_id;
            $this->contract_type = $contract->contract_type;
            $this->start_date = $contract->start_date;
            $this->expire_date = $contract->expire_date;
            $this->expirenotification = $contract->expirenotification;
            $this->description = $contract->description;
            $this->attachment = $contract->attachment;
        } else {
            $this->reset(['workstation_id', 'department_id', 'position_id', 'contract_type', 'start_date', 'expire_date', 'expirenotification', 'description', 'attachment']);
        }
    }

    /**
     * Suspend a contract
     */
    public function suspendContract($id)
    {
        $contract = Employeecontracts::findOrFail($id);
        $contract->update(['status' => 'suspended']);
        $this->listdata();
        session()->flash('success', 'Contract suspended successfully!');
    }

    /**
     * Activate a contract
     */
    public function activateContract($id)
    {
        // First, set all other active contracts to suspended
        Employeecontracts::where('employee_id', $this->employee_id)
            ->where('status', 'active')
            ->update(['status' => 'suspended']);

        $contract = Employeecontracts::findOrFail($id);
        $contract->update(['status' => 'active']);
        $this->listdata();
        session()->flash('success', 'Contract activated successfully!');
    }
    public function render()
    {
        return view('livewire.hr.staffs.contracts');
    }
}
