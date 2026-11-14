<?php

namespace App\Livewire\Engineering\Reports;

use App\Livewire\Engineering\Concerns\HasReportPeriod;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Engineering department report for management: one line per requisition with its job card,
 * purchasing state, technician, remarks and satisfaction.
 */
class ExecutiveSummary extends Component
{
    use HasReportPeriod, InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $filterSection = '';

    public function mount(): void
    {
        abort_unless(EngineeringAccess::canViewReports(Auth::user()), 403);
    }

    public function updating(string $name): void
    {
        $this->resetPage();
    }

    protected function query(): Builder
    {
        [$from, $to] = $this->periodRange();

        return EngineeringAccess::scopeVisibleJobs(MaintenanceJob::query(), Auth::user())
            ->whereHas('requisition', fn (Builder $q) => $q->whereBetween('requested_at', [$from, $to]))
            ->when($this->filterSection, fn (Builder $q, $id) => $q->where('section_id', $id))
            ->with(['requisition.department:id,name', 'section:id,name', 'engineer:id,first_name,last_name', 'jobCard', 'currentFeedback', 'equipment:id,name'])
            ->latest();
    }

    /** The latest thing management should know: assistance asked, else the recommendation. */
    public static function remarks(MaintenanceJob $job): string
    {
        return (string) ($job->assistance_explanation ?: $job->recommendation ?: $job->jobCard?->comments);
    }

    public function export()
    {
        return $this->exportTable(
            'Engineering department report',
            ['S/N', 'MRV No.', 'Reporting date', 'Approved date', 'Job card No.', 'Section', 'Department', 'Description', 'Sent to PSO', 'Status', 'Technician', 'Remarks', 'Satisfaction'],
            $this->query()->get()->values()->map(fn (MaintenanceJob $job, int $i) => [
                $i + 1,
                $job->number,
                Eng::dt($job->requisition->requested_at, 'd M Y'),
                Eng::dt($job->jobCard?->approved_at, 'd M Y'),
                $job->jobCard?->number,
                $job->section->name,
                $job->requisition->department?->name,
                $job->equipmentName().': '.$job->problem_description,
                $job->jobCard?->isApproved() ? Eng::dt($job->jobCard->approved_at, 'd M Y') : '',
                $job->status->label().($job->is_rework ? ' (RE-WORK)' : ''),
                Eng::name($job->engineer),
                self::remarks($job),
                $job->currentFeedback?->rating->label() ?? 'No score',
            ]),
            ['Period' => $this->periodLabel(), 'Section' => Section::find($this->filterSection)?->name],
        );
    }

    public function render()
    {
        return view('livewire.engineering.reports.executive-summary', [
            'jobs' => $this->query()->paginate(20),
            'sections' => EngineeringAccess::selectableSections(Auth::user()),
            'periodLabel' => $this->periodLabel(),
        ]);
    }
}
