<?php

namespace App\Livewire\Engineering\Reports;

use App\Livewire\Engineering\Concerns\HasReportPeriod;
use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\JobCard;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Sourcing of spares and materials, step by step with time frames, from the job cards.
 */
class ProcurementTracker extends Component
{
    use HasReportPeriod, InteractsWithEngineering;

    #[Url]
    public string $filterSection = '';

    #[Url]
    public string $filterStatus = '';

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless(EngineeringAccess::isEngineeringStaff($user) || EngineeringAccess::canProcessJobCards($user) || EngineeringAccess::canRunStore($user), 403);
    }

    /** Job cards submitted in the period; an engineer sees only the cards of their own jobs. */
    protected function cards(): Collection
    {
        [$from, $to] = $this->periodRange();
        $user = Auth::user();
        $seesAll = EngineeringAccess::canProcessJobCards($user) || EngineeringAccess::canRunStore($user);

        return JobCard::query()
            ->whereBetween('submitted_at', [$from, $to])
            ->where('status', '!=', 'draft')
            ->when(! $seesAll, fn (Builder $q) => $q->whereHas('job', fn (Builder $j) => EngineeringAccess::scopeVisibleJobs($j, $user)))
            ->when($this->filterSection, fn (Builder $q, $id) => $q->whereHas('job', fn (Builder $j) => $j->where('section_id', $id)))
            ->with(['job.equipment:id,name,code', 'job.section:id,name', 'requestingEngineer:id,first_name,last_name'])
            ->oldest('submitted_at')
            ->get()
            ->when($this->filterStatus, fn (Collection $cards) => $cards->filter(fn (JobCard $card) => $card->procurementStatus() === $this->filterStatus))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(Collection $cards): array
    {
        $counts = $cards->countBy(fn (JobCard $card) => $card->procurementStatus());
        $average = fn (callable $days) => ($values = $cards->map($days)->filter(fn ($value) => $value !== null))->isEmpty() ? null : round($values->avg(), 1);

        return [
            'counts' => collect(Lookups::PROCUREMENT_STATUSES)->map(fn ($meta, $status) => $counts[$status] ?? 0)->all(),
            'total' => $cards->count(),
            'submission_to_approval' => $average(fn (JobCard $c) => $c->approved_at ? (int) $c->submitted_at->diffInDays($c->approved_at) : null),
            'approval_to_order' => $average(fn (JobCard $c) => $c->daysApprovalToOrder()),
            'order_to_delivery' => $average(fn (JobCard $c) => $c->daysOrderToDelivery()),
        ];
    }

    public function export()
    {
        $cards = $this->cards();
        $summary = $this->summary($cards);
        $today = Eng::now()->format('d M Y');
        $status = fn (?\Carbon\CarbonInterface $requested, ?\Carbon\CarbonInterface $done) => $done ? 'Done' : ($requested ? 'In progress' : 'Not started');

        $headings = [
            'S/N', 'Date now', 'Days since job card submitted', 'Job card No.', 'MRV No.', 'Responsible person', 'Description / problem', 'Equipment / area affected',
            'Job card submission date', 'Date of approval', 'Proforma - date requested', 'Proforma - status', 'Proforma - date received',
            'Spare procurement - date procured', 'Spare procurement - status', 'Tender / LPO - date opened', 'Tender / LPO - status', 'Tender / LPO - date of approval',
            'Supplier name', 'Delivery period (days)', 'Expected arrival', 'Actual arrival', 'Status',
        ];

        $rows = $cards->map(fn (JobCard $card, int $i) => [
            $i + 1, $today, $card->daysSinceSubmitted(), $card->number, $card->job->number, Eng::name($card->requestingEngineer),
            $card->description ?: $card->job->problem_description, $card->job->equipmentName(),
            Eng::dt($card->submitted_at, 'd M Y'), Eng::dt($card->approved_at, 'd M Y'),
            Eng::date($card->proforma_requested_at), $status($card->proforma_requested_at, $card->proforma_received_at), Eng::date($card->proforma_received_at),
            Eng::date($card->spare_procured_at), $status($card->order_date, $card->spare_procured_at),
            Eng::date($card->tender_opened_at), $status($card->tender_opened_at, $card->tender_approved_at), Eng::date($card->tender_approved_at),
            $card->supplier_name, $card->deliveryPeriod(), Eng::date($card->delivery_expected_at), Eng::date($card->delivery_actual_at),
            Lookups::PROCUREMENT_STATUSES[$card->procurementStatus()]['label'],
        ]);

        // Shade the status column like the on-screen report
        $statusColumn = count($headings) - 1;
        $fills = $cards->map(fn (JobCard $card) => [$statusColumn => Lookups::PROCUREMENT_STATUSES[$card->procurementStatus()]['colour']])->all();

        // Summary of status under the table
        $rows = $rows->push([])->push(['', 'SUMMARY OF STATUS']);
        foreach (Lookups::PROCUREMENT_STATUSES as $key => $meta) {
            $fills[$rows->count()] = [1 => $meta['colour']];
            $rows->push(['', $meta['label'], $summary['counts'][$key]]);
        }
        $rows->push(['', 'Total', $summary['total']]);

        return $this->exportTable('Procurement tracker', $headings, $rows, ['Period' => $this->periodLabel(), 'Section' => Section::find($this->filterSection)?->name], $fills);
    }

    public function render()
    {
        $cards = $this->cards();

        return view('livewire.engineering.reports.procurement-tracker', [
            'cards' => $cards,
            'summary' => $this->summary($cards),
            'sections' => Section::active()->get(),
            'periodLabel' => $this->periodLabel(),
            'today' => Eng::now()->format('d M Y'),
        ]);
    }
}
