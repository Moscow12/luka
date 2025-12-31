<?php

namespace App\Livewire\Acc\Payroll;

use App\Exports\PaymentReportsExport;
use App\Models\allowances;
use App\Models\departments;
use App\Models\payrolls;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

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
        $this->reset([
            'search',
            'department',
            'status',
            'salaryMin',
            'salaryMax',
            'allowance',
        ]);
        $this->period = now()->format('Y-m');
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
            'employee.designation',
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

    public function downloadSalarySlipPdf($payrollId = null)
    {
        $id = $payrollId ?? $this->viewingPayrollId;

        if (!$id) {
            return;
        }

        $payroll = payrolls::with([
            'employee.department',
            'employee.designation',
            'employee.activeContract',
            'contract',
            'items.contractAllowance.allowance',
            'items.contractDeduction.deduction',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('exports.salary-slip-pdf', [
            'payroll' => $payroll,
        ])->setPaper('a4', 'portrait');

        $filename = 'salary-slip-' . ($payroll->employee->employee_number ?? $payroll->employee->id) . '-' . $payroll->period . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function exportExcel()
    {
        $query = $this->getPayrollsQuery();
        $summary = $this->getSummary();

        $filename = 'payment-reports-'.$this->period.'-'.now()->format('YmdHis').'.xlsx';

        return Excel::download(
            new PaymentReportsExport($query, $this->period, $summary),
            $filename
        );
    }

    public function exportCSV()
    {
        $query = $this->getPayrollsQuery();
        $summary = $this->getSummary();

        $filename = 'payment-reports-'.$this->period.'-'.now()->format('YmdHis').'.csv';

        return Excel::download(
            new PaymentReportsExport($query, $this->period, $summary),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPDF()
    {
        $query = $this->getPayrollsQuery();
        $summary = $this->getSummary();

        $filename = 'payment-reports-'.$this->period.'-'.now()->format('YmdHis').'.pdf';

        return Excel::download(
            new PaymentReportsExport($query, $this->period, $summary),
            $filename,
            \Maatwebsite\Excel\Excel::DOMPDF
        );
    }

    private function getSummary()
    {
        $summaryQuery = $this->getPayrollsQuery();

        return [
            'total_payrolls' => $summaryQuery->count(),
            'total_gross' => $summaryQuery->sum('gross_salary'),
            'total_deductions' => $summaryQuery->sum('total_deductions'),
            'total_net' => $summaryQuery->sum('net_salary'),
        ];
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

        $summary = $this->getSummary();

        return view('livewire.acc.payroll.paymentreports', [
            'payrolls' => $payrolls,
            'departments' => $departments,
            'allowances' => $allowances,
            'summary' => $summary,
        ]);
    }
}
