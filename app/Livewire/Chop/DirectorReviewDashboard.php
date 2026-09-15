<?php

namespace App\Livewire\Chop;

use App\Models\BudgetRequest;
use App\Models\departments;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DirectorReviewDashboard extends Component
{
    use WithPagination;

    public $search = '';

    public $filterStatus = '';

    public $filterDepartment = '';

    public $filterFinancialYear = '';

    // Review modal
    public $showReviewModal = false;

    public $reviewingRequest = null;

    public $director_notes = '';

    public $itemModifications = [];

    // View modal
    public $showViewModal = false;

    public $viewingRequest = null;

    public function openReviewModal($requestId)
    {
        $this->reviewingRequest = BudgetRequest::with(['items.item', 'items.category', 'department', 'financialYear', 'requestedBy'])
            ->findOrFail($requestId);

        // Load items for modification
        $this->itemModifications = $this->reviewingRequest->items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_name' => $item->item->name,
                'category_name' => $item->category->name,
                'requested_quantity' => $item->requested_quantity,
                'requested_price' => $item->requested_price ?? 0,
                'approved_quantity' => $item->approved_quantity ?? $item->requested_quantity,
                'approved_price' => $item->approved_price ?? $item->requested_price,
                'director_comment' => $item->director_comment ?? '',
            ];
        })->toArray();

        $this->director_notes = $this->reviewingRequest->director_notes ?? '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->reviewingRequest = null;
        $this->itemModifications = [];
        $this->director_notes = '';
    }

    public function approve()
    {
        if (! $this->reviewingRequest) {
            return;
        }

        DB::transaction(function () {
            // Update request
            $this->reviewingRequest->update([
                'status' => BudgetRequest::STATUS_APPROVED,
                'director_notes' => $this->director_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Calculate approved amount
            $approvedAmount = 0;

            // Update items with director modifications
            foreach ($this->itemModifications as $mod) {
                $item = $this->reviewingRequest->items()->find($mod['id']);
                if ($item) {
                    $item->update([
                        'approved_quantity' => $mod['approved_quantity'],
                        'approved_price' => $mod['approved_price'],
                        'director_comment' => $mod['director_comment'],
                    ]);

                    $approvedAmount += $mod['approved_quantity'] * $mod['approved_price'];
                }
            }

            $this->reviewingRequest->update([
                'approved_amount' => $approvedAmount,
            ]);
        });

        session()->flash('success', 'Budget request approved successfully!');
        $this->closeReviewModal();
    }

    public function reject()
    {
        if (! $this->reviewingRequest) {
            return;
        }

        $this->reviewingRequest->update([
            'status' => BudgetRequest::STATUS_REJECTED,
            'director_notes' => $this->director_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('success', 'Budget request rejected.');
        $this->closeReviewModal();
    }

    public function requestRevision()
    {
        if (! $this->reviewingRequest) {
            return;
        }

        $this->reviewingRequest->update([
            'status' => BudgetRequest::STATUS_REVISION_REQUIRED,
            'director_notes' => $this->director_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('success', 'Revision requested. HoD has been notified.');
        $this->closeReviewModal();
    }

    public function setUnderReview($requestId)
    {
        $request = BudgetRequest::findOrFail($requestId);

        if ($request->status === BudgetRequest::STATUS_SUBMITTED) {
            $request->update([
                'status' => BudgetRequest::STATUS_UNDER_REVIEW,
                'reviewed_by' => Auth::id(),
            ]);

            session()->flash('success', 'Request marked as under review.');
        }
    }

    public function openViewModal($requestId)
    {
        $this->viewingRequest = BudgetRequest::with([
            'items.item',
            'items.category',
            'department',
            'financialYear',
            'requestedBy',
            'reviewedBy',
        ])->findOrFail($requestId);

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingRequest = null;
    }

    public function render()
    {
        $requests = BudgetRequest::query()
            ->with(['department', 'financialYear', 'requestedBy', 'items'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('request_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('department', function ($dq) {
                            $dq->where('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('requestedBy', function ($uq) {
                            $uq->where('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDepartment, fn ($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $departments = departments::orderBy('name')->get();
        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();

        // Statistics
        $stats = [
            'pending' => BudgetRequest::pending()->count(),
            'approved' => BudgetRequest::where('status', BudgetRequest::STATUS_APPROVED)->count(),
            'rejected' => BudgetRequest::where('status', BudgetRequest::STATUS_REJECTED)->count(),
            'total_requested' => BudgetRequest::pending()->sum('total_estimated_amount'),
        ];

        return view('livewire.chop.director-review-dashboard', [
            'requests' => $requests,
            'departments' => $departments,
            'financialYears' => $financialYears,
            'stats' => $stats,
            'statuses' => BudgetRequest::getStatuses(),
        ]);
    }
}
