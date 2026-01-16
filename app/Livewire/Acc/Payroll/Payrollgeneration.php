<?php

namespace App\Livewire\Acc\Payroll;

use App\Models\ContractAllowance;
use App\Models\ContractDeduction;
use App\Models\departments;
use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\payroll_items;
use App\Models\payrolls;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Payrollgeneration extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $department = '';

    #[Url]
    public $contractType = '';

    #[Url]
    public $contractStatus = 'active'; // Filter for contract status

    #[Url]
    public $employeeStatus = 'Active'; // Filter for employee status (capitalized)

    #[Url]
    public $period = ''; // Y-m format (e.g., "2025-11")

    public $perPage = 20;

    public $selectedEmployees = [];

    public $selectAll = false;

    public $showFilters = false;

    // For viewing contract details
    public $viewingContractId = null;

    public $viewingContract = null;

    public $contractAllowances = [];

    public $contractDeductions = [];

    public function mount()
    {
        // Set default period to current month
        if (empty($this->period)) {
            $this->period = now()->format('Y-m');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
    }

    public function updatingContractType()
    {
        $this->resetPage();
    }

    public function updatingContractStatus()
    {
        $this->resetPage();
    }

    public function updatingEmployeeStatus()
    {
        $this->resetPage();
    }

    public function updatingPeriod()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedEmployees = $this->getEmployeesQuery()->pluck('id')->toArray();
        } else {
            $this->selectedEmployees = [];
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->department = '';
        $this->contractType = '';
        $this->contractStatus = 'active';
        $this->employeeStatus = 'Active';
        $this->period = now()->format('Y-m');
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function viewContractDetails($contractId)
    {
        $this->viewingContractId = $contractId;

        // Load the contract with base salary
        $this->viewingContract = Employeecontracts::find($contractId);

        // Load contract allowances
        $this->contractAllowances = ContractAllowance::where('contract_id', $contractId)
            ->where('is_active', true)
            ->with('allowance')
            ->get();

        // Load contract deductions
        $this->contractDeductions = ContractDeduction::where('contract_id', $contractId)
            ->where('is_active', true)
            ->with('deduction')
            ->get();
    }

    public function closeContractDetails()
    {
        $this->viewingContractId = null;
        $this->viewingContract = null;
        $this->contractAllowances = [];
        $this->contractDeductions = [];
    }

    public function generatePayrollForSelected()
    {
        try {
            if (empty($this->selectedEmployees)) {
                ToastMagic::error('Please select at least one employee');

                return;
            }

            $generated = 0;
            $errors = [];

            foreach ($this->selectedEmployees as $employeeId) {
                try {
                    $employee = Employee::with('activeContract')->find($employeeId);

                    if (! $employee) {
                        $errors[] = "Employee ID {$employeeId} not found";

                        continue;
                    }

                    if (! $employee->activeContract) {
                        $errors[] = "{$employee->first_name} {$employee->last_name} has no active contract";

                        continue;
                    }

                    // Check if payroll already exists for this period
                    $existingPayroll = payrolls::where('employee_id', $employeeId)
                        ->where('period', $this->period)
                        ->first();

                    if ($existingPayroll) {
                        $errors[] = "{$employee->first_name} {$employee->last_name} already has payroll for {$this->period}";

                        continue;
                    }

                    $this->generatePayroll($employee, $employee->activeContract);
                    $generated++;
                } catch (\Exception $e) {

                    $employeeName = isset($employee) ? "{$employee->first_name} {$employee->last_name}" : "Employee ID {$employeeId}";
                    $errors[] = "Error generating payroll for {$employeeName}: {$e->getMessage()}";
                    Log::error("Payroll generation error for employee {$employeeId}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Display results
            if ($generated > 0) {
                ToastMagic::success("Successfully generated {$generated} payroll record(s) for period {$this->period}");
                $this->selectedEmployees = [];
                $this->selectAll = false;
            } elseif (empty($errors)) {
                ToastMagic::warning('No payroll records were generated');
            }

            if (! empty($errors)) {
                foreach ($errors as $error) {
                    ToastMagic::warning($error);
                }
            }
        } catch (\Exception $e) {
            ToastMagic::error('Failed to generate payroll: '.$e->getMessage());
            Log::error('Payroll generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function generatePayroll(Employee $employee, Employeecontracts $contract)
    {
        $basicSalary = $contract->base_salary;

        // Get contract allowances
        $contractAllowances = ContractAllowance::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->with('allowance')
            ->get();

        // Get contract deductions
        $contractDeductions = ContractDeduction::where('contract_id', $contract->id)
            ->where('is_active', true)
            ->with('deduction')
            ->get();

        // Calculate total allowances using the new method that handles percentage vs fixed
        $totalAllowances = $contractAllowances->sum(function ($item) use ($basicSalary) {
            return $item->calculateAmount($basicSalary);
        });

        // Calculate Mafao deductions (deduction_type = 'mafao') on basic salary
        // Formula: TaxableAmount = BasicSalary - (MafaoDeductionValue% * BasicSalary / 100)
        $mafaoDeductions = $contractDeductions->filter(function ($item) {
            return $item->deduction && $item->deduction->deduction_type === 'mafao';
        });

        $totalMafaoDeduction = $mafaoDeductions->sum(function ($item) use ($basicSalary) {
            return $item->calculateAmount($basicSalary, $basicSalary);
        });

        // Calculate taxable salary (Basic Salary minus Mafao deductions) - PAYE is on salary only, not allowances
        $taxableSalary = $basicSalary - $totalMafaoDeduction;

        // Calculate PAYE based on taxable salary (basic salary after Mafao deductions)
        $payeCalculation = calculate_paye($taxableSalary);
        $paye = $payeCalculation['tax_amount'];

        // Calculate gross salary (basic + allowances) - for net salary calculation
        $grossSalary = $basicSalary + $totalAllowances;

        // Calculate other deductions (non-Mafao) using the new method that handles percentage vs fixed
        $nonMafaoDeductions = $contractDeductions->filter(function ($item) {
            return !$item->deduction || $item->deduction->deduction_type !== 'mafao';
        });

        $otherDeductions = $nonMafaoDeductions->sum(function ($item) use ($basicSalary, $grossSalary) {
            return $item->calculateAmount($basicSalary, $grossSalary);
        });

        // Total deductions = PAYE + Mafao deductions + Other deductions
        $totalDeductions = $paye + $totalMafaoDeduction + $otherDeductions;

        // Calculate net salary
        $netSalary = $grossSalary - $totalDeductions;

        // Create payroll record
        $payroll = payrolls::create([
            'employee_id' => $employee->id,
            'contract_id' => $contract->id,
            'period' => $this->period,
            'basic_salary' => $basicSalary,
            'gross_salary' => $grossSalary,
            'taxable_salary' => $taxableSalary,
            'mafao_deductions' => $totalMafaoDeduction,
            'paye_tax' => $paye,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'status' => 'pending',
        ]);

        // Create payroll items for allowances with calculated amounts
        foreach ($contractAllowances as $allowance) {
            $calculatedAmount = $allowance->calculateAmount($basicSalary);
            payroll_items::create([
                'payroll_id' => $payroll->id,
                'contract_allowance_id' => $allowance->id,
                'name' => $allowance->allowance->name ?? 'Allowance',
                'type' => 'allowance',
                'amount' => $calculatedAmount,
                'added_by' => Auth::user()->id,
            ]);
        }

        // Create payroll item for PAYE
        payroll_items::create([
            'payroll_id' => $payroll->id,
            'name' => 'PAYE Tax',
            'type' => 'deduction',
            'amount' => $paye,
            'added_by' => Auth::user()->id,
        ]);

        // Create payroll items for Mafao deductions (pension/social security contributions) - based on basic salary
        foreach ($mafaoDeductions as $deduction) {
            $calculatedAmount = $deduction->calculateAmount($basicSalary, $basicSalary);
            payroll_items::create([
                'payroll_id' => $payroll->id,
                'contract_deduction_id' => $deduction->id,
                'name' => $deduction->deduction->name ?? 'Mafao Deduction',
                'type' => 'deduction',
                'amount' => $calculatedAmount,
                'added_by' => Auth::user()->id,
            ]);
        }

        // Create payroll items for other deductions (non-Mafao)
        foreach ($nonMafaoDeductions as $deduction) {
            $calculatedAmount = $deduction->calculateAmount($basicSalary, $grossSalary);
            payroll_items::create([
                'payroll_id' => $payroll->id,
                'contract_deduction_id' => $deduction->id,
                'name' => $deduction->deduction->name ?? 'Deduction',
                'type' => 'deduction',
                'amount' => $calculatedAmount,
                'added_by' => Auth::user()->id,
            ]);
        }

        return $payroll;
    }

    private function getEmployeesQuery()
    {
        return Employee::query()
            ->with(['department', 'activeContract'])
            ->when($this->employeeStatus, function ($query) {
                if ($this->employeeStatus === 'Active') {
                    $query->where('status', 'Active');
                } else {
                    // For other statuses (Suspended, Terminated, Retired)
                    $query->where('status', '!=', 'Active');
                }
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->department, fn ($query) => $query->where('department_id', $this->department))
            ->when($this->contractStatus === 'active', function ($query) {
                $query->whereHas('activeContract');
            })
            ->when($this->contractStatus === 'no_active', function ($query) {
                $query->whereDoesntHave('activeContract');
            })
            ->when($this->contractType, function ($query) {
                $query->whereHas('activeContract', function ($q) {
                    $q->where('contract_type', $this->contractType);
                });
            })
            ->orderBy('first_name');
    }

    public function render()
    {
        try {
            $employees = $this->getEmployeesQuery()->paginate($this->perPage);
            $departments = departments::orderBy('name')->get();
            $contractTypes = ['permanent', 'temporary', 'part_time'];

            return view('livewire.acc.payroll.payrollgeneration', [
                'employees' => $employees,
                'departments' => $departments,
                'contractTypes' => $contractTypes,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Payroll query error: ' . $e->getMessage());
            ToastMagic::error('Unable to load employee data. Please try again or contact support.');

            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            return view('livewire.acc.payroll.payrollgeneration', [
                'employees' => $emptyPaginator,
                'departments' => collect(),
                'contractTypes' => ['permanent', 'temporary', 'part_time'],
            ]);
        } catch (\Exception $e) {
            Log::error('Payroll error: ' . $e->getMessage());
            ToastMagic::error('An unexpected error occurred. Please try again.');

            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            return view('livewire.acc.payroll.payrollgeneration', [
                'employees' => $emptyPaginator,
                'departments' => collect(),
                'contractTypes' => ['permanent', 'temporary', 'part_time'],
            ]);
        }
    }
}
