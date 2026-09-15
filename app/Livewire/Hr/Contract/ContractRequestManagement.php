<?php

namespace App\Livewire\Hr\Contract;

use App\Models\ContractRequest;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ContractRequestManagement extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $filterStatus = 'pending';

    public $filterType = '';

    public $search = '';

    // For reviewing a request
    public $reviewingRequest = null;

    public $showReviewModal = false;

    public $review_comments = '';

    public $action = '';

    public $hr_confirmation_letter;

    // For creating new contract on approval
    public $newContractData = [];

    // Statistics
    public $pendingCount = 0;

    public $approvedCount = 0;

    public $rejectedCount = 0;

    public function mount()
    {
        $this->loadStatistics();
    }

    protected function loadStatistics()
    {
        $this->pendingCount = ContractRequest::where('status', 'pending')->count();
        $this->approvedCount = ContractRequest::where('status', 'approved')
            ->whereMonth('updated_at', now()->month)
            ->count();
        $this->rejectedCount = ContractRequest::where('status', 'rejected')
            ->whereMonth('updated_at', now()->month)
            ->count();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function openReviewModal($id)
    {
        $this->reviewingRequest = ContractRequest::with(['employee', 'contract', 'reviewer', 'terminationReason'])->find($id);
        $this->review_comments = '';
        $this->action = '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->reviewingRequest = null;
        $this->review_comments = '';
        $this->action = '';
        $this->hr_confirmation_letter = null;
    }

    public function approveRequest()
    {
        $rules = [
            'review_comments' => 'nullable|string|max:1000',
        ];

        // Require confirmation letter for early termination
        if ($this->reviewingRequest && $this->reviewingRequest->requiresConfirmationLetter()) {
            $rules['hr_confirmation_letter'] = 'required|file|mimes:pdf,doc,docx|max:5120';
        } else {
            $rules['hr_confirmation_letter'] = 'nullable|file|mimes:pdf,doc,docx|max:5120';
        }

        $this->validate($rules, [
            'hr_confirmation_letter.required' => 'A confirmation letter is required for early termination requests.',
        ]);

        if (! $this->reviewingRequest) {
            session()->flash('error', 'Request not found.');

            return;
        }

        DB::beginTransaction();
        try {
            $request = $this->reviewingRequest;

            $updateData = [
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_comments' => $this->review_comments,
            ];

            // Handle HR confirmation letter upload
            if ($this->hr_confirmation_letter) {
                $updateData['hr_confirmation_letter'] = $this->hr_confirmation_letter->store('contract-requests/hr', 'public');
            }

            // Update request status
            $request->update($updateData);

            // Handle based on request type
            if ($request->request_type === 'termination') {
                // Update employee status to Terminated
                $employee = $request->employee;
                if ($employee) {
                    $employee->update(['status' => 'Terminated']);
                }

                // Update contract status to terminated
                $contract = $request->contract;
                if ($contract) {
                    $contract->update(['status' => 'terminated']);
                }

                session()->flash('success', 'Termination request approved. Employee status updated to terminated.');
            } elseif ($request->request_type === 'renewal' || $request->request_type === 'extension') {
                // For renewal/extension, HR needs to create a new contract manually
                // or we can auto-create based on the proposed dates
                session()->flash('success', 'Request approved. Please create a new contract for this employee with the proposed dates.');
            }

            DB::commit();
            $this->loadStatistics();
            $this->closeReviewModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function rejectRequest()
    {
        $this->validate([
            'review_comments' => 'required|string|min:10|max:1000',
        ], [
            'review_comments.required' => 'Please provide a reason for rejection.',
            'review_comments.min' => 'Rejection reason must be at least 10 characters.',
        ]);

        if (! $this->reviewingRequest) {
            session()->flash('error', 'Request not found.');

            return;
        }

        $this->reviewingRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_comments' => $this->review_comments,
        ]);

        session()->flash('success', 'Request has been rejected.');
        $this->loadStatistics();
        $this->closeReviewModal();
    }

    public function render()
    {
        $requests = ContractRequest::with(['employee', 'contract', 'reviewer'])
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn ($q) => $q->where('request_type', $this->filterType))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', function ($query) {
                    $query->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('middle_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.hr.contract.contract-request-management', [
            'requests' => $requests,
        ]);
    }
}
