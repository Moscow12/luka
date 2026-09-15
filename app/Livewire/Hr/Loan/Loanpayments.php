<?php

namespace App\Livewire\Hr\Loan;

use App\Models\Employee;
use App\Models\loan_payments;
use App\Models\loanrequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Loanpayments extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = 'active';

    // Current logged-in employee
    public $currentEmployee = null;

    public $hasEmployeeAccount = false;

    // Modal state
    public $showModal = false;

    public $showHistoryModal = false;

    // Selected loan for payment
    public $selectedLoan = null;

    public $loanRequestId;

    // Payment form fields
    public $payment_amount;

    public $payment_date;

    public $remarks;

    // Loan summary
    public $total_loan = 0;

    public $total_paid = 0;

    public $remaining_balance = 0;

    public $expected_installment = 0;

    // Payment history
    public $paymentHistory = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'payment_amount' => [
                'required',
                'numeric',
                'min:1',
                'max:'.($this->remaining_balance ?: 999999999),
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected $messages = [
        'payment_amount.required' => 'Please enter the payment amount.',
        'payment_amount.max' => 'Payment cannot exceed remaining balance.',
        'payment_date.required' => 'Please select the payment date.',
        'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
    ];

    public function mount()
    {
        $this->currentEmployee = Employee::where('user_id', Auth::id())->first();
        $this->hasEmployeeAccount = $this->currentEmployee !== null;
        $this->payment_date = now()->format('Y-m-d');
    }

    public function openPaymentModal($id)
    {
        $this->resetForm();
        $this->loanRequestId = $id;
        $this->selectedLoan = loanrequests::with(['employee', 'loan_item'])->findOrFail($id);
        $this->calculateLoanSummary();
        $this->payment_amount = $this->expected_installment;
        $this->showModal = true;
    }

    public function openHistoryModal($id)
    {
        $this->loanRequestId = $id;
        $this->selectedLoan = loanrequests::with(['employee', 'loan_item'])->findOrFail($id);
        $this->calculateLoanSummary();
        $this->loadPaymentHistory();
        $this->showHistoryModal = true;
    }

    public function calculateLoanSummary()
    {
        if (! $this->selectedLoan) {
            return;
        }

        // Calculate total loan amount (principal + interest)
        $principal = floatval($this->selectedLoan->approved_amount ?: $this->selectedLoan->amount);
        $months = intval($this->selectedLoan->repayment_period_months);
        $annualRate = floatval($this->selectedLoan->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        if ($this->selectedLoan->interest_type === 'flat') {
            $interestAmount = $principal * ($annualRate / 100) * ($months / 12);
            $this->total_loan = $principal + $interestAmount;
            $this->expected_installment = $this->total_loan / $months;
        } else {
            if ($monthlyRate > 0) {
                $this->expected_installment = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
                    / (pow(1 + $monthlyRate, $months) - 1);
            } else {
                $this->expected_installment = $principal / $months;
            }
            $this->total_loan = $this->expected_installment * $months;
        }

        // Get total paid
        $this->total_paid = loan_payments::where('loanrequest_id', $this->selectedLoan->id)
            ->where('status', 'paid')
            ->sum('amount');

        $this->remaining_balance = max(0, $this->total_loan - $this->total_paid);

        // Round values
        $this->total_loan = round($this->total_loan, 2);
        $this->total_paid = round($this->total_paid, 2);
        $this->remaining_balance = round($this->remaining_balance, 2);
        $this->expected_installment = round($this->expected_installment, 2);
    }

    public function loadPaymentHistory()
    {
        $this->paymentHistory = loan_payments::where('loanrequest_id', $this->loanRequestId)
            ->with('recorded_by')
            ->orderBy('payment_date', 'desc')
            ->get()
            ->toArray();
    }

    public function resetForm()
    {
        $this->reset([
            'payment_amount',
            'remarks',
            'loanRequestId',
            'selectedLoan',
        ]);
        $this->payment_date = now()->format('Y-m-d');
        $this->total_loan = 0;
        $this->total_paid = 0;
        $this->remaining_balance = 0;
        $this->expected_installment = 0;
        $this->paymentHistory = [];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function recordPayment()
    {
        if (! $this->hasEmployeeAccount) {
            session()->flash('error', 'Your account is not linked to any employee record.');

            return;
        }

        $this->validate();

        $loan = loanrequests::findOrFail($this->loanRequestId);

        if (! in_array($loan->status, ['approved', 'disbursed', 'active'])) {
            session()->flash('error', 'Payments can only be recorded for active loans.');

            return;
        }

        DB::transaction(function () use ($loan) {
            // Record payment
            loan_payments::create([
                'loanrequest_id' => $this->loanRequestId,
                'amount' => $this->payment_amount,
                'payment_date' => $this->payment_date,
                'status' => 'paid',
                'remarks' => $this->remarks,
                'recorded_by' => $this->currentEmployee->id,
            ]);

            // Update loan status if needed
            if ($loan->status === 'approved') {
                $loan->update(['status' => 'active']);
            }

            // Check if loan is fully paid
            $this->calculateLoanSummary();
            $newBalance = $this->remaining_balance - $this->payment_amount;

            if ($newBalance <= 0) {
                $loan->update(['status' => 'closed']);
            }
        });

        session()->flash('success', 'Payment recorded successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    public function disburse($id)
    {
        $loan = loanrequests::findOrFail($id);

        if ($loan->status !== 'approved') {
            session()->flash('error', 'Only approved loans can be disbursed.');

            return;
        }

        $loan->update(['status' => 'disbursed']);
        session()->flash('success', 'Loan marked as disbursed successfully!');
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

        return [
            'total' => round($totalLoan, 2),
            'paid' => round($totalPaid, 2),
            'balance' => round(max(0, $totalLoan - $totalPaid), 2),
            'progress' => round($progress, 1),
        ];
    }

    public function render()
    {
        $loans = loanrequests::query()
            ->with(['employee', 'loan_item'])
            ->when($this->statusFilter === 'active', function ($query) {
                $query->whereIn('status', ['approved', 'disbursed', 'active']);
            })
            ->when($this->statusFilter === 'closed', function ($query) {
                $query->where('status', 'closed');
            })
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('employee_no', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);

        // Add progress to each loan
        $loans->getCollection()->transform(function ($loan) {
            $loan->progress = $this->getLoanProgress($loan);

            return $loan;
        });

        // Get counts
        $activeCount = loanrequests::whereIn('status', ['approved', 'disbursed', 'active'])->count();
        $closedCount = loanrequests::where('status', 'closed')->count();

        return view('livewire.hr.loan.loanpayments', [
            'loans' => $loans,
            'activeCount' => $activeCount,
            'closedCount' => $closedCount,
        ]);
    }
}
