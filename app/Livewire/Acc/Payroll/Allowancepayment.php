<?php

namespace App\Livewire\Acc\Payroll;

use App\Models\ContractAllowance;
use App\Models\departments;
use App\Models\Employee;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Allowancepayment extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $department = '';

    #[Url]
    public $contractType = '';

    #[Url]
    public $allowanceType = '';

    #[Url]
    public $employeeStatus = 'Active';

    #[Url]
    public $period = '';

    public $perPage = 20;

    public $selectedEmployees = [];

    public $selectAll = false;

    public $showFilters = false;

    // For viewing allowance details
    public $viewingEmployeeId = null;

    public $employeeAllowances = [];

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

    public function updatingAllowanceType()
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
        $this->allowanceType = '';
        $this->employeeStatus = 'Active';
        $this->period = now()->format('Y-m');
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function viewAllowanceDetails($employeeId)
    {
        $this->viewingEmployeeId = $employeeId;

        $employee = Employee::with('activeContract')->find($employeeId);

        if ($employee && $employee->activeContract) {
            $this->employeeAllowances = ContractAllowance::where('contract_id', $employee->activeContract->id)
                ->where('is_active', true)
                ->with('allowance')
                ->get();
        }
    }

    public function closeAllowanceDetails()
    {
        $this->viewingEmployeeId = null;
        $this->employeeAllowances = [];
    }

    public function processAllowancePayments()
    {
        if (empty($this->selectedEmployees)) {
            ToastMagic::error('Please select at least one employee');

            return;
        }

        $processed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($this->selectedEmployees as $employeeId) {
                $employee = Employee::with('activeContract')->find($employeeId);

                if (! $employee || ! $employee->activeContract) {
                    $errors[] = "{$employee->first_name} {$employee->last_name} has no active contract";

                    continue;
                }

                // Get active allowances for the contract
                $allowances = ContractAllowance::where('contract_id', $employee->activeContract->id)
                    ->where('is_active', true)
                    ->with('allowance')
                    ->get();

                if ($allowances->isEmpty()) {
                    $errors[] = "{$employee->first_name} {$employee->last_name} has no active allowances";

                    continue;
                }

                // Here you would create allowance payment records
                // For now, we'll just count as processed
                $processed++;
            }

            DB::commit();

            if ($processed > 0) {
                ToastMagic::success("Processed allowance payments for {$processed} employee(s)");
                $this->selectedEmployees = [];
                $this->selectAll = false;
            }

            if (! empty($errors)) {
                foreach ($errors as $error) {
                    ToastMagic::warning($error);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            ToastMagic::error('Error processing allowance payments: '.$e->getMessage());
        }
    }

    private function getEmployeesQuery()
    {
        return Employee::query()
            ->with(['department', 'activeContract.contractAllowances.allowance'])
            ->when($this->employeeStatus, function ($query) {
                if ($this->employeeStatus === 'Active') {
                    $query->where('status', 'Active');
                } else {
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
            ->whereHas('activeContract', function ($query) {
                $query->where('status', 'active')
                    ->when($this->contractType, function ($q) {
                        $q->where('contract_type', $this->contractType);
                    })
                    ->whereHas('contractAllowances', function ($q) {
                        $q->where('is_active', true)
                            ->when($this->allowanceType, function ($subQ) {
                                $subQ->where('allowance_id', $this->allowanceType);
                            });
                    });
            })
            ->orderBy('first_name');
    }

    public function render()
    {
        $employees = $this->getEmployeesQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();

        $contractTypes = ['permanent', 'temporary', 'part_time'];

        $allowances = \App\Models\allowances::orderBy('name')->get();

        return view('livewire.acc.payroll.allowancepayment', [
            'employees' => $employees,
            'departments' => $departments,
            'contractTypes' => $contractTypes,
            'allowances' => $allowances,
        ]);
    }
}
