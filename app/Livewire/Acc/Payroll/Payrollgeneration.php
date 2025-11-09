<?php

namespace App\Livewire\Acc\Payroll;

use App\Models\ContractAllowance;
use App\Models\ContractDeduction;
use App\Models\departments;
use App\Models\Employee;
use App\Models\Employeecontracts;
use App\Models\payroll_items;
use App\Models\payrolls;
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
        $this->contractAllowances = [];
        $this->contractDeductions = [];
    }

    public function generatePayrollForSelected()
    {
        if (empty($this->selectedEmployees)) {
            toaster()->error('Please select at least one employee');

            return;
        }

        $generated = 0;
        $errors = [];

        foreach ($this->selectedEmployees as $employeeId) {
            try {
                $employee = Employee::with('activeContract')->find($employeeId);

                if (! $employee || ! $employee->activeContract) {
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
                $errors[] = "Error generating payroll for employee ID {$employeeId}: {$e->getMessage()}";
            }
        }

        if ($generated > 0) {
            toaster()->success("{$generated} payroll(s) generated successfully");
            $this->selectedEmployees = [];
            $this->selectAll = false;
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                toaster()->warning($error);
            }
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

        // Calculate total allowances
        $totalAllowances = $contractAllowances->sum(function ($item) {
            return $item->amount_override ?? $item->allowance->amount ?? 0;
        });

        // Calculate gross salary (basic + allowances)
        $grossSalary = $basicSalary + $totalAllowances;

        // Calculate PAYE
        $payeCalculation = calculate_paye($grossSalary);
        $paye = $payeCalculation['tax_amount'];

        // Calculate total deductions (including PAYE)
        $otherDeductions = $contractDeductions->sum(function ($item) {
            return $item->amount_override ?? $item->deduction->amount ?? 0;
        });
        $totalDeductions = $paye + $otherDeductions;

        // Calculate net salary
        $netSalary = $grossSalary - $totalDeductions;

        // Create payroll record
        $payroll = payrolls::create([
            'employee_id' => $employee->id,
            'contract_id' => $contract->id,
            'period' => $this->period,
            'basic_salary' => $basicSalary,
            'gross_salary' => $grossSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,
            'status' => 'pending',
        ]);

        // Create payroll items for allowances
        foreach ($contractAllowances as $allowance) {
            payroll_items::create([
                'payroll_id' => $payroll->id,
                'name' => $allowance->allowance->name ?? 'Allowance',
                'type' => 'allowance',
                'amount' => $allowance->amount_override ?? $allowance->allowance->amount ?? 0,
            ]);
        }

        // Create payroll item for PAYE
        payroll_items::create([
            'payroll_id' => $payroll->id,
            'name' => 'PAYE Tax',
            'type' => 'deduction',
            'amount' => $paye,
        ]);

        // Create payroll items for deductions
        foreach ($contractDeductions as $deduction) {
            payroll_items::create([
                'payroll_id' => $payroll->id,
                'name' => $deduction->deduction->name ?? 'Deduction',
                'type' => 'deduction',
                'amount' => $deduction->amount_override ?? $deduction->deduction->amount ?? 0,
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
                        ->orWhere('employee_number', 'like', '%'.$this->search.'%')
                        ->orWhere('email_address', 'like', '%'.$this->search.'%');
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
        $employees = $this->getEmployeesQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();

        $contractTypes = ['permanent', 'temporary', 'part_time'];

        return view('livewire.acc.payroll.payrollgeneration', [
            'employees' => $employees,
            'departments' => $departments,
            'contractTypes' => $contractTypes,
        ]);
    }
}
