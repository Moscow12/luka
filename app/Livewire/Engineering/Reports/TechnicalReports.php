<?php

namespace App\Livewire\Engineering\Reports;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\TechnicalReport;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Engineering technical report: a per-equipment narrative for management, written from a
 * job when a device needs external help, major spend or replacement.
 */
class TechnicalReports extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected const FIELDS = [
        'date_reported', 'problem_background', 'work_done', 'equipment_requirement', 'engineer_needs',
        'current_value', 'new_purchase_cost', 'external_expert_cost', 'comment_to_management',
    ];

    // Job whose report is open
    #[Url]
    public ?string $job = null;

    public string $search = '';

    /** @var array<string, mixed> */
    public array $form = [];

    public function mount(): void
    {
        abort_unless(EngineeringAccess::isEngineeringStaff(Auth::user()), 403);

        if ($this->job) {
            $this->open($this->job);
        }
    }

    /** Opens the job's report, pre-filled from the job when it has not been written yet. */
    public function open(string $jobId): void
    {
        $job = $this->visibleJobs()->with(['technicalReport', 'notes', 'equipment'])->findOrFail($jobId);
        $report = $job->technicalReport;

        $this->job = $job->id;
        $this->resetErrorBag();

        $this->form = $report
            ? array_merge($report->only(self::FIELDS), ['date_reported' => $report->date_reported->toDateString()])
            : [
                'date_reported' => Eng::now()->toDateString(),
                'problem_background' => $job->problem_description,
                'work_done' => $job->notes->where('kind', 'engineer')->pluck('body')->implode("\n"),
                'equipment_requirement' => '',
                'engineer_needs' => '',
                'current_value' => $job->equipment?->current_value,
                'new_purchase_cost' => null,
                'external_expert_cost' => null,
                'comment_to_management' => '',
            ];
    }

    public function close(): void
    {
        $this->reset(['job', 'form']);
    }

    public function save(): void
    {
        $job = $this->visibleJobs()->findOrFail($this->job);
        abort_unless(EngineeringAccess::isOnJob(Auth::user(), $job), 403);

        $this->validate([
            'form.date_reported' => ['required', 'date'],
            'form.problem_background' => ['required', 'string'],
            'form.work_done' => ['required', 'string'],
            'form.equipment_requirement' => ['required', 'string'],
            'form.comment_to_management' => ['required', 'string'],
            'form.current_value' => ['nullable', 'numeric', 'min:0'],
            'form.new_purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'form.external_expert_cost' => ['nullable', 'numeric', 'min:0'],
        ], [], collect(self::FIELDS)->mapWithKeys(fn (string $field) => ["form.{$field}" => str_replace('_', ' ', $field)])->all());

        $data = array_map(fn ($value) => $value === '' ? null : $value, $this->form);

        TechnicalReport::updateOrCreate(['job_id' => $job->id], $data + [
            'reported_by' => Auth::id(),
            'reporter_role' => EngineeringAccess::employee(Auth::user())?->position?->name,
        ]);

        session()->flash('success', "Technical report for {$job->number} saved.");
    }

    /** The report as a document that opens in MS Word. */
    public function downloadWord()
    {
        $job = $this->visibleJobs()->with(['technicalReport.reportedBy', 'equipment.room', 'requisition.department', 'requisition.room'])->findOrFail($this->job);
        abort_unless($job->technicalReport, 404);

        $html = view('livewire.engineering.reports.partials.technical-report-document', ['job' => $job, 'report' => $job->technicalReport])->render();

        return response()->streamDownload(
            fn () => print ('<html><head><meta charset="utf-8"><style>body{font-family:Calibri,Arial,sans-serif} td,th{padding:4px 8px;vertical-align:top} h2{margin-bottom:0}</style></head><body>'.$html.'</body></html>'),
            'technical-report-'.Str::slug($job->number).'.doc',
            ['Content-Type' => 'application/msword'],
        );
    }

    protected function visibleJobs(): Builder
    {
        return EngineeringAccess::scopeVisibleJobs(MaintenanceJob::query(), Auth::user());
    }

    public function render()
    {
        $user = Auth::user();
        $openJob = $this->job
            ? $this->visibleJobs()->with(['technicalReport.reportedBy', 'equipment.room', 'requisition.department', 'requisition.room', 'section'])->find($this->job)
            : null;

        return view('livewire.engineering.reports.technical-reports', [
            'reports' => $openJob ? null : TechnicalReport::query()
                ->whereHas('job', fn (Builder $q) => EngineeringAccess::scopeVisibleJobs($q, $user)->search($this->search))
                ->with(['job.equipment:id,name,code', 'job.requisition.department:id,name', 'reportedBy:id,first_name,surname'])
                ->latest('date_reported')->paginate(15),
            // Jobs of mine that could carry a report
            'candidates' => $openJob ? collect() : EngineeringAccess::scopeOwnJobs(MaintenanceJob::query(), $user)
                ->whereDoesntHave('technicalReport')->whereNotNull('equipment_id')->with('equipment:id,name,code')->latest()->limit(20)->get(),
            'openJob' => $openJob,
            'canEdit' => $openJob ? EngineeringAccess::isOnJob($user, $openJob) : false,
        ]);
    }
}
