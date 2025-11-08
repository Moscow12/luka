<?php

namespace App\Livewire\Hr\Staffs;

use App\Models\allowances;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\ContractAllowance;
use App\Models\ContractDeduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Salary extends Component
{
    public  $deductions, $is_hourly, $contract_id, $contacts, $allowances;
    public $employee_id;
    public $first_name, $middle_name, $last_name, $gender, $getfullname,$photo, $age, $email, $editUrl;
    public $salary;
    public $selectedAllowances = [];
    public $selectedDeductions = [];
    public $totalAllowances = 0;
    public $totalDeductions = 0;
    public $activeContract;

    public function mount($id=null)
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
        $this->photo = $staff->photo;
        $this->contacts = Employeecontracts::where('employee_id', $this->employee_id)->where('status', 'active')->get();
        $this->editUrl = route('hr.editstaff', $id);

        $this->allowances = allowances::where('is_active', true)->get();
        $this->deductions = Deduction::where('is_active', true)->get();

        // Get active contract salary
        if ($this->contacts->first()) {
            $this->salary = $this->contacts->first()->base_salary;
            $this->contract_id = $this->contacts->first()->id;
        }

        $this->loadContractData();
    }

    public function loadContractData()
    {
        if (!$this->contract_id) {
            return;
        }

        $this->activeContract = Employeecontracts::with(['contractAllowances.allowance', 'contractDeductions.deduction'])
            ->find($this->contract_id);

        if ($this->activeContract) {
            $this->calculateTotals();
            $this->loadSelectedItems();
        }
    }

    public function loadSelectedItems()
    {
        if (!$this->activeContract) {
            return;
        }

        $this->selectedAllowances = $this->activeContract->contractAllowances
            ->where('is_active', true)
            ->pluck('allowance_id')
            ->toArray();

        $this->selectedDeductions = $this->activeContract->contractDeductions
            ->where('is_active', true)
            ->pluck('deduction_id')
            ->toArray();
    }

    public function calculateTotals()
    {
        if (!$this->activeContract) {
            return;
        }

        $this->totalAllowances = 0;
        $this->totalDeductions = 0;

        foreach ($this->activeContract->contractAllowances->where('is_active', true) as $contractAllowance) {
            $allowance = $contractAllowance->allowance;
            if ($allowance) {
                $amount = $contractAllowance->amount_override ?? (
                    $allowance->type === 'percentage'
                        ? ($this->salary * $allowance->allowance_value / 100)
                        : $allowance->allowance_value
                );
                $this->totalAllowances += $amount;
            }
        }

        foreach ($this->activeContract->contractDeductions->where('is_active', true) as $contractDeduction) {
            $deduction = $contractDeduction->deduction;
            if ($deduction) {
                $amount = $contractDeduction->amount_override ?? (
                    $deduction->type === 'percentage'
                        ? ($this->salary * $deduction->deduction_value / 100)
                        : $deduction->deduction_value
                );
                $this->totalDeductions += $amount;
            }
        }
    }

    public function saveSalary()
    {
        $this->validate([
            'salary' => 'required|numeric|min:0',
            'selectedAllowances' => 'array',
            'selectedDeductions' => 'array',
        ]);

        DB::beginTransaction();
        try {
            // Update contract base salary
            $contract = Employeecontracts::find($this->contract_id);
            $contract->update(['base_salary' => $this->salary]);

            // Delete existing allowances and deductions for this contract
            ContractAllowance::where('contract_id', $this->contract_id)->delete();
            ContractDeduction::where('contract_id', $this->contract_id)->delete();

            // Save selected allowances
            foreach ($this->selectedAllowances as $allowanceId) {
                $allowance = allowances::find($allowanceId);
                if ($allowance) {
                    ContractAllowance::create([
                        'contract_id' => $this->contract_id,
                        'allowance_id' => $allowanceId,
                        'amount_override' => null, // Use default calculation
                        'is_active' => true,
                        'added_by' => Auth::id(),
                    ]);
                }
            }

            // Save selected deductions
            foreach ($this->selectedDeductions as $deductionId) {
                $deduction = Deduction::find($deductionId);
                if ($deduction) {
                    ContractDeduction::create([
                        'contract_id' => $this->contract_id,
                        'deduction_id' => $deductionId,
                        'amount_override' => null, // Use default calculation
                        'is_active' => true,
                        'added_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Contract salary, allowances, and deductions saved successfully!');
            $this->loadContractData();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.hr.staffs.salary');
    }
}
