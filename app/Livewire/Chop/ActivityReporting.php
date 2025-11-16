<?php

namespace App\Livewire\Chop;

use App\Models\ActivityReport;
use App\Models\chopactivities;
use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ActivityReporting extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterFinancialYear = '';
    public $filterMonth = '';
    public $filterStatus = '';

    // Modal properties
    public $showReportModal = false;
    public $modalMode = 'create';
    public $reportId;

    // Report fields
    public $activity_id;
    public $report_month;
    public $status = 'completed';
    public $completion_notes;
    public $challenges_faced;
    public $amount_spent = 0;
    public $payment_proof_document;
    public $activity_proof_document;
    public $actual_completion_date;
    public $beneficiaries_reached;
    public $outcomes_achieved;

    // Temporary file uploads
    public $tempPaymentProof;
    public $tempActivityProof;

    public function mount()
    {
        $currentFY = FinancialYear::current();
        if ($currentFY) {
            $this->filterFinancialYear = $currentFY->id;
        }

        $this->filterMonth = now()->format('Y-m');
    }

    public function openReportModal($activityId, $month = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = 'create';
        $this->showReportModal = true;

        $this->activity_id = $activityId;
        $this->report_month = $month ?? now()->format('Y-m');
        $this->actual_completion_date = now()->format('Y-m-d');

        // Check if report already exists
        $existingReport = ActivityReport::where('activity_id', $activityId)
            ->where('report_month', $this->report_month)
            ->first();

        if ($existingReport) {
            $this->loadReport($existingReport);
        } else {
            $this->resetReportForm();
        }
    }

    public function editReport($reportId)
    {
        $report = ActivityReport::findOrFail($reportId);

        // Check if user has permission (must be reporter or assigned personnel)
        if ($report->reported_by !== Auth::id()) {
            $activity = chopactivities::with('personels')->find($report->activity_id);
            $userTitles = Auth::user()->employee->jobtitle_id ?? null;
            $assignedTitles = $activity->personels->pluck('title_id')->toArray();

            if (!in_array($userTitles, $assignedTitles)) {
                session()->flash('error', 'You do not have permission to edit this report.');
                return;
            }
        }

        $this->loadReport($report);
        $this->modalMode = 'edit';
        $this->showReportModal = true;
    }

    protected function loadReport($report)
    {
        $this->reportId = $report->id;
        $this->activity_id = $report->activity_id;
        $this->report_month = $report->report_month;
        $this->status = $report->status;
        $this->completion_notes = $report->completion_notes;
        $this->challenges_faced = $report->challenges_faced;
        $this->amount_spent = $report->amount_spent;
        $this->payment_proof_document = $report->payment_proof_document;
        $this->activity_proof_document = $report->activity_proof_document;
        $this->actual_completion_date = $report->actual_completion_date?->format('Y-m-d');
        $this->beneficiaries_reached = $report->beneficiaries_reached;
        $this->outcomes_achieved = $report->outcomes_achieved;
    }

    public function resetReportForm()
    {
        $this->reset([
            'reportId', 'status', 'completion_notes', 'challenges_faced',
            'amount_spent', 'payment_proof_document', 'activity_proof_document',
            'beneficiaries_reached', 'outcomes_achieved', 'tempPaymentProof', 'tempActivityProof'
        ]);
        $this->status = 'completed';
        $this->amount_spent = 0;
        $this->actual_completion_date = now()->format('Y-m-d');
    }

    public function saveReport()
    {
        $this->validate([
            'activity_id' => ['required', 'exists:chopactivities,id'],
            'report_month' => ['required', 'date_format:Y-m'],
            'status' => ['required', 'in:completed,partially_completed,not_completed,cancelled'],
            'completion_notes' => ['required', 'string'],
            'amount_spent' => ['required', 'numeric', 'min:0'],
            'actual_completion_date' => ['nullable', 'date'],
            'beneficiaries_reached' => ['nullable', 'integer', 'min:0'],
            'tempPaymentProof' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'tempActivityProof' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        DB::transaction(function () {
            $reportData = [
                'activity_id' => $this->activity_id,
                'report_month' => $this->report_month,
                'status' => $this->status,
                'completion_notes' => $this->completion_notes,
                'challenges_faced' => $this->challenges_faced,
                'amount_spent' => $this->amount_spent,
                'actual_completion_date' => $this->actual_completion_date,
                'beneficiaries_reached' => $this->beneficiaries_reached,
                'outcomes_achieved' => $this->outcomes_achieved,
            ];

            // Handle file uploads
            if ($this->tempPaymentProof) {
                $path = $this->tempPaymentProof->store('activity-reports/payment-proofs', 'public');
                $reportData['payment_proof_document'] = $path;

                // Delete old file if editing
                if ($this->modalMode === 'edit' && $this->payment_proof_document) {
                    Storage::disk('public')->delete($this->payment_proof_document);
                }
            }

            if ($this->tempActivityProof) {
                $path = $this->tempActivityProof->store('activity-reports/activity-proofs', 'public');
                $reportData['activity_proof_document'] = $path;

                // Delete old file if editing
                if ($this->modalMode === 'edit' && $this->activity_proof_document) {
                    Storage::disk('public')->delete($this->activity_proof_document);
                }
            }

            if ($this->modalMode === 'edit' && $this->reportId) {
                $report = ActivityReport::findOrFail($this->reportId);
                $report->update($reportData);
            } else {
                $reportData['reported_by'] = Auth::id();
                ActivityReport::create($reportData);
            }
        });

        session()->flash('success', $this->modalMode === 'edit' ? 'Report updated successfully!' : 'Report submitted successfully!');
        $this->showReportModal = false;
        $this->resetReportForm();
    }

    public function deleteReport($reportId)
    {
        $report = ActivityReport::findOrFail($reportId);

        // Check permission
        if ($report->reported_by !== Auth::id() && !Auth::user()->hasRole('Director')) {
            session()->flash('error', 'You do not have permission to delete this report.');
            return;
        }

        // Delete associated files
        if ($report->payment_proof_document) {
            Storage::disk('public')->delete($report->payment_proof_document);
        }
        if ($report->activity_proof_document) {
            Storage::disk('public')->delete($report->activity_proof_document);
        }

        $report->delete();
        session()->flash('success', 'Report deleted successfully!');
    }

    public function getMyActivities()
    {
        $userEmployee = Auth::user()->employee;
        if (!$userEmployee) {
            return collect();
        }

        $userTitleId = $userEmployee->jobtitle_id;

        return chopactivities::query()
            ->with(['category', 'source', 'financialYear', 'personels.title'])
            ->whereHas('personels', function ($query) use ($userTitleId) {
                $query->where('title_id', $userTitleId);
            })
            ->where('is_active', true)
            ->when($this->filterFinancialYear, fn ($q) => $q->where('financial_year_id', $this->filterFinancialYear))
            ->get();
    }

    public function render()
    {
        $myActivities = $this->getMyActivities();

        // Get reports for the activities
        $reports = ActivityReport::query()
            ->with(['activity.category', 'activity.source', 'reporter'])
            ->whereIn('activity_id', $myActivities->pluck('id'))
            ->when($this->search, function ($query) {
                $query->whereHas('activity', function ($q) {
                    $q->where('planned_activity', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterMonth, fn ($q) => $q->where('report_month', $this->filterMonth))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('report_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();

        return view('livewire.chop.activity-reporting', [
            'myActivities' => $myActivities,
            'reports' => $reports,
            'financialYears' => $financialYears,
            'statusOptions' => ActivityReport::getStatusOptions(),
        ]);
    }
}
