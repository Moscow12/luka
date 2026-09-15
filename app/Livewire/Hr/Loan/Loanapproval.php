<?php

namespace App\Livewire\Hr\Loan;

use App\Models\Employee;
use App\Models\loan_approval;
use App\Models\loanrequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Loanapproval extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = 'pending';

    // Current logged-in employee (approver)
    public $currentEmployee = null;

    public $hasEmployeeAccount = false;

    // Modal state
    public $showModal = false;

    public $modalMode = 'approve'; // approve or reject

    // Selected loan request
    public $selectedLoan = null;

    public $loanRequestId;

    // Approval form fields
    public $approved_amount;

    public $remarks;

    // Calculated fields for display
    public $interest_amount = 0;

    public $total_repayment = 0;

    public $installment_amount = 0;

    public $payment_schedule = [];

    public $showSchedule = false;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        $rules = [
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->modalMode === 'approve') {
            $rules['approved_amount'] = [
                'required',
                'numeric',
                'min:1',
                'max:'.($this->selectedLoan->amount ?? 0),
            ];
        }

        return $rules;
    }

    protected $messages = [
        'approved_amount.required' => 'Please enter the approved amount.',
        'approved_amount.max' => 'Approved amount cannot exceed requested amount.',
    ];

    public function mount()
    {
        // Get the logged-in user's employee record
        $this->currentEmployee = Employee::where('user_id', Auth::id())->first();
        $this->hasEmployeeAccount = $this->currentEmployee !== null;
    }

    public function updatedApprovedAmount()
    {
        $this->calculateLoan();
    }

    public function calculateLoan()
    {
        if (! $this->selectedLoan || ! $this->approved_amount) {
            return;
        }

        $principal = floatval($this->approved_amount);
        $months = intval($this->selectedLoan->repayment_period_months);
        $annualRate = floatval($this->selectedLoan->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        if ($this->selectedLoan->interest_type === 'flat') {
            $this->interest_amount = $principal * ($annualRate / 100) * ($months / 12);
            $this->total_repayment = $principal + $this->interest_amount;
            $this->installment_amount = $this->total_repayment / $months;
            $this->generateFlatSchedule($principal, $months);
        } else {
            if ($monthlyRate > 0) {
                $this->installment_amount = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
                    / (pow(1 + $monthlyRate, $months) - 1);
            } else {
                $this->installment_amount = $principal / $months;
            }
            $this->total_repayment = $this->installment_amount * $months;
            $this->interest_amount = $this->total_repayment - $principal;
            $this->generateReducingSchedule($principal, $months, $monthlyRate);
        }

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

    public function openApproveModal($id)
    {
        $this->resetForm();
        $this->loanRequestId = $id;
        $this->selectedLoan = loanrequests::with(['employee', 'loan_item'])->findOrFail($id);
        $this->approved_amount = $this->selectedLoan->amount;
        $this->modalMode = 'approve';
        $this->showModal = true;
        $this->calculateLoan();
    }

    public function openRejectModal($id)
    {
        $this->resetForm();
        $this->loanRequestId = $id;
        $this->selectedLoan = loanrequests::with(['employee', 'loan_item'])->findOrFail($id);
        $this->modalMode = 'reject';
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'approved_amount',
            'remarks',
            'loanRequestId',
            'selectedLoan',
        ]);
        $this->interest_amount = 0;
        $this->total_repayment = 0;
        $this->installment_amount = 0;
        $this->payment_schedule = [];
        $this->showSchedule = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function toggleSchedule()
    {
        $this->showSchedule = ! $this->showSchedule;
    }

    public function approve()
    {
        if (! $this->hasEmployeeAccount) {
            session()->flash('error', 'Your account is not linked to any employee record.');

            return;
        }

        $this->validate();

        $loan = loanrequests::findOrFail($this->loanRequestId);

        if ($loan->status !== 'pending' && $loan->status !== 'under_review') {
            session()->flash('error', 'This loan request cannot be approved.');

            return;
        }

        // Recalculate installment based on approved amount
        $principal = floatval($this->approved_amount);
        $months = intval($loan->repayment_period_months);
        $annualRate = floatval($loan->interest_rate);
        $monthlyRate = $annualRate / 12 / 100;

        if ($loan->interest_type === 'flat') {
            $interestAmount = $principal * ($annualRate / 100) * ($months / 12);
            $totalRepayment = $principal + $interestAmount;
            $installmentAmount = $totalRepayment / $months;
        } else {
            if ($monthlyRate > 0) {
                $installmentAmount = $principal * $monthlyRate * pow(1 + $monthlyRate, $months)
                    / (pow(1 + $monthlyRate, $months) - 1);
            } else {
                $installmentAmount = $principal / $months;
            }
        }

        // Create approval record
        loan_approval::create([
            'loanrequest_id' => $this->loanRequestId,
            'approved_by' => $this->currentEmployee->id,
            'status' => 'approved',
            'approval_date' => now(),
            'approved_amount' => $this->approved_amount,
            'remarks' => $this->remarks,
        ]);

        // Update loan request
        $loan->update([
            'status' => 'approved',
            'approved_amount' => $this->approved_amount,
            'approval_date' => now(),
            'installment_amount' => round($installmentAmount, 2),
            'remarks' => $this->remarks,
        ]);

        session()->flash('success', 'Loan request approved successfully!');
        $this->showModal = false;
        $this->resetForm();
    }

    public function reject()
    {
        if (! $this->hasEmployeeAccount) {
            session()->flash('error', 'Your account is not linked to any employee record.');

            return;
        }

        $this->validate([
            'remarks' => ['required', 'string', 'min:10'],
        ], [
            'remarks.required' => 'Please provide a reason for rejection.',
            'remarks.min' => 'Please provide a detailed reason (at least 10 characters).',
        ]);

        $loan = loanrequests::findOrFail($this->loanRequestId);

        if ($loan->status !== 'pending' && $loan->status !== 'under_review') {
            session()->flash('error', 'This loan request cannot be rejected.');

            return;
        }

        // Create rejection record
        loan_approval::create([
            'loanrequest_id' => $this->loanRequestId,
            'approved_by' => $this->currentEmployee->id,
            'status' => 'rejected',
            'approval_date' => now(),
            'approved_amount' => 0,
            'remarks' => $this->remarks,
        ]);

        // Update loan request
        $loan->update([
            'status' => 'rejected',
            'remarks' => $this->remarks,
        ]);

        session()->flash('success', 'Loan request rejected.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function markUnderReview($id)
    {
        $loan = loanrequests::findOrFail($id);

        if ($loan->status !== 'pending') {
            session()->flash('error', 'Only pending requests can be marked for review.');

            return;
        }

        $loan->update(['status' => 'under_review']);
        session()->flash('success', 'Loan request marked as under review.');
    }

    public function render()
    {
        $loanRequests = loanrequests::query()
            ->with(['employee', 'loan_item', 'addedBy'])
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'pending') {
                    $query->whereIn('status', ['pending', 'under_review']);
                } else {
                    $query->where('status', $this->statusFilter);
                }
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

        // Get counts for status tabs
        $pendingCount = loanrequests::whereIn('status', ['pending', 'under_review'])->count();
        $approvedCount = loanrequests::where('status', 'approved')->count();
        $rejectedCount = loanrequests::where('status', 'rejected')->count();

        return view('livewire.hr.loan.loanapproval', [
            'loanRequests' => $loanRequests,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }
}
