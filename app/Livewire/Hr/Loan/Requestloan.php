<?php

namespace App\Livewire\Hr\Loan;

use App\Models\Employee;
use App\Models\loan_items;
use App\Models\loanrequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Requestloan extends Component
{
    use WithPagination;

    public $search = '';

    // Form fields
    public $loan_item_id;

    public $amount;

    public $repayment_period_months;

    public $interest_type = 'reducing_balance';

    public $reason;

    public $employee_id;

    // Calculated fields
    public $interest_rate = 0;

    public $interest_amount = 0;

    public $total_repayment = 0;

    public $installment_amount = 0;

    public $payment_schedule = [];

    // Loan item constraints
    public $min_amount = 0;

    public $max_amount = 0;

    public $max_period = 0;

    // UI state
    public $showModal = false;

    public $modalMode = 'create';

    public $showSchedule = false;

    // Data
    public $loanItems = [];

    public $selectedLoanItem = null;

    // Current logged-in employee
    public $currentEmployee = null;

    public $hasEmployeeAccount = false;

    // Check for existing loans
    public $hasExistingLoan = false;

    public $existingLoanStatus = null;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'loan_item_id' => ['required', 'exists:loan_items,id'],
            'amount' => ['required', 'numeric', 'min:'.$this->min_amount, 'max:'.$this->max_amount],
            'repayment_period_months' => ['required', 'integer', 'min:1', 'max:'.$this->max_period],
            'interest_type' => ['required', 'in:flat,reducing_balance'],
            'reason' => ['required', 'string', 'min:10'],
        ];
    }

    protected $messages = [
        'amount.min' => 'Amount must be at least :min.',
        'amount.max' => 'Amount cannot exceed :max.',
        'repayment_period_months.max' => 'Repayment period cannot exceed :max months.',
        'reason.min' => 'Please provide a detailed reason (at least 10 characters).',
    ];

    public function mount()
    {
        $this->loanItems = loan_items::orderBy('name')->get();

        // Get the logged-in user's employee record
        $this->currentEmployee = Employee::where('user_id', Auth::id())->first();

        if ($this->currentEmployee) {
            $this->hasEmployeeAccount = true;
            $this->employee_id = $this->currentEmployee->id;
            $this->checkExistingLoan();
        } else {
            $this->hasEmployeeAccount = false;
        }
    }

    public function updatedLoanItemId($value)
    {
        if ($value) {
            $this->selectedLoanItem = loan_items::find($value);
            if ($this->selectedLoanItem) {
                $this->interest_rate = $this->selectedLoanItem->interest_rate;
                $this->min_amount = $this->selectedLoanItem->min_amount;
                $this->max_amount = $this->selectedLoanItem->max_amount;
                $this->max_period = $this->selectedLoanItem->repayment_period_months;
                $this->repayment_period_months = $this->max_period;

                // Reset and recalculate
                $this->amount = $this->min_amount;
                $this->calculateLoan();
            }
        } else {
            $this->resetLoanCalculation();
        }
    }

    public function updatedAmount()
    {
        $this->calculateLoan();
    }

    public function updatedRepaymentPeriodMonths()
    {
        $this->calculateLoan();
    }

    public function updatedInterestType()
    {
        $this->calculateLoan();
    }

    public function checkExistingLoan()
    {
        if ($this->employee_id) {
            $existingLoan = loanrequests::where('employee_id', $this->employee_id)
                ->whereIn('status', ['pending', 'under_review', 'approved', 'disbursed', 'active'])
                ->first();

            $this->hasExistingLoan = $existingLoan !== null;
            $this->existingLoanStatus = $existingLoan?->status;
        } else {
            $this->hasExistingLoan = false;
            $this->existingLoanStatus = null;
        }
    }

    public function calculateLoan()
    {
        if (! $this->amount || ! $this->repayment_period_months || ! $this->interest_rate) {
            $this->resetLoanCalculation();

            return;
        }

        $principal = floatval($this->amount);
        $months = intval($this->repayment_period_months);
        $annualRate = floatval($this->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        if ($this->interest_type === 'flat') {
            // Flat interest: Interest = Principal × Rate × Time
            $this->interest_amount = $principal * ($annualRate / 100) * ($months / 12);
            $this->total_repayment = $principal + $this->interest_amount;
            $this->installment_amount = $this->total_repayment / $months;

            // Generate flat payment schedule
            $this->generateFlatSchedule($principal, $months);
        } else {
            // Reducing balance (EMI formula)
            if ($monthlyRate > 0) {
                $this->installment_amount = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
                    / (pow(1 + $monthlyRate, $months) - 1);
            } else {
                $this->installment_amount = $principal / $months;
            }

            $this->total_repayment = $this->installment_amount * $months;
            $this->interest_amount = $this->total_repayment - $principal;

            // Generate reducing balance payment schedule
            $this->generateReducingSchedule($principal, $months, $monthlyRate);
        }

        // Round values
        $this->interest_amount = round($this->interest_amount, 2);
        $this->total_repayment = round($this->total_repayment, 2);
        $this->installment_amount = round($this->installment_amount, 2);
    }

    private function generateFlatSchedule($principal, $months)
    {
        $this->payment_schedule = [];
        $monthlyPrincipal = $principal / $months;
        $monthlyInterest = $this->interest_amount / $months;
        $balance = $principal + $this->interest_amount;

        for ($i = 1; $i <= $months; $i++) {
            $payment = $monthlyPrincipal + $monthlyInterest;
            $balance -= $payment;

            $this->payment_schedule[] = [
                'month' => $i,
                'date' => now()->addMonths($i)->format('M Y'),
                'principal' => round($monthlyPrincipal, 2),
                'interest' => round($monthlyInterest, 2),
                'payment' => round($payment, 2),
                'balance' => round(max(0, $balance), 2),
            ];
        }
    }

    private function generateReducingSchedule($principal, $months, $monthlyRate)
    {
        $this->payment_schedule = [];
        $balance = $principal;

        for ($i = 1; $i <= $months; $i++) {
            $interestPayment = $balance * $monthlyRate;
            $principalPayment = $this->installment_amount - $interestPayment;
            $balance -= $principalPayment;

            $this->payment_schedule[] = [
                'month' => $i,
                'date' => now()->addMonths($i)->format('M Y'),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestPayment, 2),
                'payment' => round($this->installment_amount, 2),
                'balance' => round(max(0, $balance), 2),
            ];
        }
    }

    private function resetLoanCalculation()
    {
        $this->interest_amount = 0;
        $this->total_repayment = 0;
        $this->installment_amount = 0;
        $this->payment_schedule = [];
    }

    public function openModal($mode = 'create')
    {
        // Refresh existing loan check when opening modal
        $this->checkExistingLoan();

        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'loan_item_id',
            'amount',
            'repayment_period_months',
            'reason',
        ]);
        $this->interest_type = 'reducing_balance';
        $this->resetLoanCalculation();
        $this->min_amount = 0;
        $this->max_amount = 0;
        $this->max_period = 0;
        $this->selectedLoanItem = null;

        // Keep the employee_id set to current employee
        if ($this->currentEmployee) {
            $this->employee_id = $this->currentEmployee->id;
        }
    }

    public function toggleSchedule()
    {
        $this->showSchedule = ! $this->showSchedule;
    }

    public function save()
    {
        // Verify employee account exists
        if (! $this->hasEmployeeAccount) {
            session()->flash('error', 'Your account is not linked to any employee record. Please contact HR.');

            return;
        }

        // Check for existing loan before saving
        $this->checkExistingLoan();

        if ($this->hasExistingLoan) {
            session()->flash('error', 'You already have a '.$this->existingLoanStatus.' loan. Please wait until it is completed.');

            return;
        }

        $this->validate();

        loanrequests::create([
            'employee_id' => $this->employee_id,
            'loan_item_id' => $this->loan_item_id,
            'amount' => $this->amount,
            'request_date' => now(),
            'interest_rate' => $this->interest_rate,
            'interest_type' => $this->interest_type,
            'repayment_period_months' => $this->repayment_period_months,
            'reason' => $this->reason,
            'status' => 'pending',
            'installment_amount' => $this->installment_amount,
            'added_by' => Auth::id(),
            'applicant_type' => 'staff',
        ]);

        session()->flash('success', 'Loan request submitted successfully!');
        $this->showModal = false;
        $this->resetForm();
        $this->checkExistingLoan();
    }

    public function cancelRequest($id)
    {
        $loan = loanrequests::findOrFail($id);

        // Ensure user can only cancel their own loans
        if ($loan->employee_id !== $this->employee_id) {
            session()->flash('error', 'You can only cancel your own loan requests.');

            return;
        }

        if ($loan->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be cancelled.');

            return;
        }

        $loan->update(['status' => 'cancelled']);
        session()->flash('success', 'Loan request cancelled successfully!');
        $this->checkExistingLoan();
    }

    public function render()
    {
        // Only show current employee's loan requests
        $loanRequests = loanrequests::query()
            ->with(['loan_item', 'employee', 'addedBy'])
            ->where('employee_id', $this->employee_id)
            ->latest()
            ->paginate(10);

        return view('livewire.hr.loan.requestloan', [
            'loanRequests' => $loanRequests,
        ]);
    }
}
