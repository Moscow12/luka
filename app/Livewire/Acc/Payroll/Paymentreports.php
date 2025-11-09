<?php

namespace App\Livewire\Acc\Payroll;

use App\Models\allowances;
use App\Models\departments;
use App\Models\payrolls;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Paymentreports extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public $search = '';

    #[Url]
    public $period = '';

    #[Url]
    public $department = '';

    #[Url]
    public $status = '';

    #[Url]
    public $salaryMin = '';

    #[Url]
    public $salaryMax = '';

    #[Url]
    public $allowance = '';

    public $perPage = 20;

    public $showFilters = false;

    // For viewing salary slip
    public $viewingPayrollId = null;

    public $payrollDetails = null;

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

    public function updatingPeriod()
    {
        $this->resetPage();
    }

    public function updatingDepartment()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingSalaryMin()
    {
        $this->resetPage();
    }

    public function updatingSalaryMax()
    {
        $this->resetPage();
    }

    public function updatingAllowance()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->period = now()->format('Y-m');
        $this->department = '';
        $this->status = '';
        $this->salaryMin = '';
        $this->salaryMax = '';
        $this->allowance = '';
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function viewSalarySlip($payrollId)
    {
        $this->viewingPayrollId = $payrollId;
        $this->payrollDetails = payrolls::with([
            'employee.department',
            'employee.activeContract',
            'contract',
            'items.contractAllowance.allowance',
            'items.contractDeduction.deduction',
        ])->find($payrollId);
    }

    public function closeSalarySlip()
    {
        $this->viewingPayrollId = null;
        $this->payrollDetails = null;
    }

    public function printSalarySlip()
    {
        $this->dispatch('print-salary-slip');
    }

    private function getPayrollsQuery()
    {
        return payrolls::query()
            ->with(['employee.department', 'contract', 'items'])
            ->when($this->period, fn ($query) => $query->where('period', $this->period))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->department, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('department_id', $this->department);
                });
            })
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->allowance, function ($query) {
                $query->whereHas('items', function ($q) {
                    $q->where('type', 'allowance')
                        ->whereHas('contractAllowance', function ($cq) {
                            $cq->where('allowance_id', $this->allowance);
                        });
                });
            })
            ->when($this->salaryMin, fn ($query) => $query->where('net_salary', '>=', $this->salaryMin))
            ->when($this->salaryMax, fn ($query) => $query->where('net_salary', '<=', $this->salaryMax))
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $payrolls = $this->getPayrollsQuery()->paginate($this->perPage);

        $departments = departments::orderBy('name')->get();
        $allowances = allowances::where('is_active', true)->orderBy('name')->get();

        // Calculate summary statistics
        $summaryQuery = $this->getPayrollsQuery();
        $summary = [
            'total_payrolls' => $summaryQuery->count(),
            'total_gross' => $summaryQuery->sum('gross_salary'),
            'total_deductions' => $summaryQuery->sum('total_deductions'),
            'total_net' => $summaryQuery->sum('net_salary'),
        ];

        return view('livewire.acc.payroll.paymentreports', [
            'payrolls' => $payrolls,
            'departments' => $departments,
            'allowances' => $allowances,
            'summary' => $summary,
        ]);
    }
}
