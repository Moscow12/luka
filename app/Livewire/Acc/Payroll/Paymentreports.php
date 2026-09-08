<?php

namespace App\Livewire\Acc\Payroll;

use App\Exports\PaymentReportsExport;
use App\Models\allowances;
use App\Models\ContractAllowance;
use App\Models\ContractDeduction;
use App\Models\departments;
use App\Models\payroll_items;
use App\Models\payrolls;
use Barryvdh\DomPDF\Facade\Pdf;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'employee.workstation',
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
        'employee.workstation',
        'contract',
        'items.contractAllowance.allowance',
        'items.contractDeduction.deduction',
    ])->findOrFail($id);

    $pdf = Pdf::loadView('exports.salary-slip-pdf', [
        'payroll' => $payroll,
    ])->setPaper('a4', 'portrait');

    $identifier = $payroll->employee->employee_number ?? $payroll->employee->id;
    $filename = str_replace(['/', '\\'], '-', "salary-slip-{$identifier}.pdf");

    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
}

    public function regeneratePayroll($payrollId)
    {
        try {
            $payroll = payrolls::with('employee.activeContract')->find($payrollId);

            if (! $payroll) {
                ToastMagic::error('Payroll record not found');

                return;
            }

            $employee = $payroll->employee;
            $contract = $payroll->contract_id
                ? \App\Models\Employeecontracts::find($payroll->contract_id)
                : $employee?->activeContract;

            if (! $employee || ! $contract) {
                ToastMagic::error('Cannot regenerate: employee or contract no longer exists');

                return;
            }

            DB::transaction(function () use ($payroll, $employee, $contract) {
                $basicSalary = $contract->base_salary;

                $contractAllowances = ContractAllowance::where('contract_id', $contract->id)
                    ->where('is_active', true)
                    ->with('allowance')
                    ->get();

                $contractDeductions = ContractDeduction::where('contract_id', $contract->id)
                    ->where('is_active', true)
                    ->with('deduction')
                    ->get();

                $totalAllowances = $contractAllowances->sum(function ($item) use ($basicSalary) {
                    return $item->calculateAmount($basicSalary);
                });

                $mafaoDeductions = $contractDeductions->filter(function ($item) {
                    return $item->deduction && $item->deduction->deduction_type === 'mafao';
                });

                $totalMafaoDeduction = $mafaoDeductions->sum(function ($item) use ($basicSalary) {
                    return $item->calculateAmount($basicSalary, $basicSalary);
                });

                $taxableSalary = $basicSalary - $totalMafaoDeduction;

                $payeCalculation = calculate_paye($taxableSalary);
                $paye = $payeCalculation['tax_amount'];

                $grossSalary = $basicSalary + $totalAllowances;

                $nonMafaoDeductions = $contractDeductions->filter(function ($item) {
                    return ! $item->deduction || $item->deduction->deduction_type !== 'mafao';
                });

                $otherDeductions = $nonMafaoDeductions->sum(function ($item) use ($basicSalary, $grossSalary) {
                    return $item->calculateAmount($basicSalary, $grossSalary);
                });

                $totalDeductions = $paye + $totalMafaoDeduction + $otherDeductions;
                $netSalary = $grossSalary - $totalDeductions;

                // Remove old payroll items before recreating them
                payroll_items::where('payroll_id', $payroll->id)->delete();

                $payroll->update([
                    'contract_id' => $contract->id,
                    'basic_salary' => $basicSalary,
                    'gross_salary' => $grossSalary,
                    'taxable_salary' => $taxableSalary,
                    'mafao_deductions' => $totalMafaoDeduction,
                    'paye_tax' => $paye,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                ]);

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

                payroll_items::create([
                    'payroll_id' => $payroll->id,
                    'name' => 'PAYE Tax',
                    'type' => 'deduction',
                    'amount' => $paye,
                    'added_by' => Auth::user()->id,
                ]);

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
            });

            ToastMagic::success("Payroll for {$employee->first_name} {$employee->last_name} ({$payroll->period}) has been regenerated");

            // Refresh the salary slip view if it's currently open for this payroll
            if ($this->viewingPayrollId === $payrollId) {
                $this->viewSalarySlip($payrollId);
            }
        } catch (\Exception $e) {
            ToastMagic::error('Failed to regenerate payroll: '.$e->getMessage());
            Log::error("Payroll regeneration error for payroll {$payrollId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
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
                    $q->where(function ($q) {
                        $q->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%')
                            ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                    });
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
