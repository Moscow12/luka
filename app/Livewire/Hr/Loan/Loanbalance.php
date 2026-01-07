<?php

namespace App\Livewire\Hr\Loan;

use App\Models\Employee;
use App\Models\loan_payments;
use App\Models\loanrequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Loanbalance extends Component
{
    // Current logged-in employee
    public $currentEmployee = null;

    public $hasEmployeeAccount = false;

    // Selected loan for details
    public $selectedLoan = null;

    public $showDetailsModal = false;

    // Loan summary for selected loan
    public $total_loan = 0;

    public $total_paid = 0;

    public $remaining_balance = 0;

    public $expected_installment = 0;

    public $payments_made = 0;

    public $payments_remaining = 0;

    // Payment history
    public $paymentHistory = [];

    // Remaining payment schedule
    public $remainingSchedule = [];

    // Overall summary
    public $totalActiveLoans = 0;

    public $totalOutstandingBalance = 0;

    public $totalMonthlyInstallment = 0;

    public function mount()
    {
        $this->currentEmployee = Employee::where('user_id', Auth::id())->first();
        $this->hasEmployeeAccount = $this->currentEmployee !== null;

        if ($this->hasEmployeeAccount) {
            $this->calculateOverallSummary();
        }
    }

    public function calculateOverallSummary()
    {
        $activeLoans = loanrequests::where('employee_id', $this->currentEmployee->id)
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->get();

        $this->totalActiveLoans = $activeLoans->count();
        $this->totalOutstandingBalance = 0;
        $this->totalMonthlyInstallment = 0;

        foreach ($activeLoans as $loan) {
            $progress = $this->getLoanProgress($loan);
            $this->totalOutstandingBalance += $progress['balance'];
            $this->totalMonthlyInstallment += $loan->installment_amount;
        }

        $this->totalOutstandingBalance = round($this->totalOutstandingBalance, 2);
        $this->totalMonthlyInstallment = round($this->totalMonthlyInstallment, 2);
    }

    public function getLoanProgress($loan)
    {
        $principal = floatval($loan->approved_amount ?: $loan->amount);
        $months = intval($loan->repayment_period_months);
        $annualRate = floatval($loan->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        if ($loan->interest_type === 'flat') {
            $interestAmount = $principal * ($annualRate / 100) * ($months / 12);
            $totalLoan = $principal + $interestAmount;
            $installment = $totalLoan / $months;
        } else {
            if ($monthlyRate > 0) {
                $installment = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
                    / (pow(1 + $monthlyRate, $months) - 1);
            } else {
                $installment = $principal / $months;
            }
            $totalLoan = $installment * $months;
        }

        $totalPaid = loan_payments::where('loanrequest_id', $loan->id)
            ->where('status', 'paid')
            ->sum('amount');

        $progress = $totalLoan > 0 ? min(100, ($totalPaid / $totalLoan) * 100) : 0;
        $paymentsMade = loan_payments::where('loanrequest_id', $loan->id)
            ->where('status', 'paid')
            ->count();

        return [
            'total' => round($totalLoan, 2),
            'paid' => round($totalPaid, 2),
            'balance' => round(max(0, $totalLoan - $totalPaid), 2),
            'installment' => round($installment, 2),
            'progress' => round($progress, 1),
            'payments_made' => $paymentsMade,
            'payments_remaining' => max(0, $months - $paymentsMade),
        ];
    }

    public function openDetailsModal($id)
    {
        $this->selectedLoan = loanrequests::with(['loan_item'])->findOrFail($id);
        $this->calculateLoanDetails();
        $this->loadPaymentHistory();
        $this->generateRemainingSchedule();
        $this->showDetailsModal = true;
    }

    public function calculateLoanDetails()
    {
        if (! $this->selectedLoan) {
            return;
        }

        $progress = $this->getLoanProgress($this->selectedLoan);
        $this->total_loan = $progress['total'];
        $this->total_paid = $progress['paid'];
        $this->remaining_balance = $progress['balance'];
        $this->expected_installment = $progress['installment'];
        $this->payments_made = $progress['payments_made'];
        $this->payments_remaining = $progress['payments_remaining'];
    }

    public function loadPaymentHistory()
    {
        $this->paymentHistory = loan_payments::where('loanrequest_id', $this->selectedLoan->id)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->toArray();
    }

    public function generateRemainingSchedule()
    {
        $this->remainingSchedule = [];

        if (! $this->selectedLoan || $this->remaining_balance <= 0) {
            return;
        }

        $principal = floatval($this->selectedLoan->approved_amount ?: $this->selectedLoan->amount);
        $totalMonths = intval($this->selectedLoan->repayment_period_months);
        $annualRate = floatval($this->selectedLoan->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        // Get last payment date or approval date
        $lastPayment = loan_payments::where('loanrequest_id', $this->selectedLoan->id)
            ->orderBy('payment_date', 'desc')
            ->first();

        $startDate = $lastPayment
            ? \Carbon\Carbon::parse($lastPayment->payment_date)
            : \Carbon\Carbon::parse($this->selectedLoan->approval_date ?? $this->selectedLoan->request_date);

        if ($this->selectedLoan->interest_type === 'flat') {
            // Flat rate - equal payments
            $remainingMonths = $this->payments_remaining;
            $monthlyPayment = $remainingMonths > 0 ? $this->remaining_balance / $remainingMonths : 0;
            $balance = $this->remaining_balance;

            for ($i = 1; $i <= $remainingMonths && $i <= 12; $i++) {
                $balance -= $monthlyPayment;
                $this->remainingSchedule[] = [
                    'month' => $i,
                    'date' => $startDate->copy()->addMonths($i)->format('M Y'),
                    'payment' => round($monthlyPayment, 2),
                    'balance' => round(max(0, $balance), 2),
                ];
            }
        } else {
            // Reducing balance - recalculate from current balance
            $balance = $this->remaining_balance;
            $installment = $this->expected_installment;
            $monthsRemaining = min(12, $this->payments_remaining);

            for ($i = 1; $i <= $monthsRemaining && $balance > 0; $i++) {
                $interestPayment = $balance * $monthlyRate;
                $principalPayment = min($installment - $interestPayment, $balance);
                $payment = $principalPayment + $interestPayment;
                $balance -= $principalPayment;

                $this->remainingSchedule[] = [
                    'month' => $i,
                    'date' => $startDate->copy()->addMonths($i)->format('M Y'),
                    'principal' => round($principalPayment, 2),
                    'interest' => round($interestPayment, 2),
                    'payment' => round($payment, 2),
                    'balance' => round(max(0, $balance), 2),
                ];
            }
        }
    }

    public function render()
    {
        $loans = collect([]);

        if ($this->hasEmployeeAccount) {
            $loans = loanrequests::where('employee_id', $this->currentEmployee->id)
                ->with(['loan_item'])
                ->whereIn('status', ['approved', 'disbursed', 'active', 'closed'])
                ->latest()
                ->get()
                ->map(function ($loan) {
                    $loan->progress = $this->getLoanProgress($loan);

                    return $loan;
                });
        }

        return view('livewire.hr.loan.loanbalance', [
            'loans' => $loans,
        ]);
    }
}
