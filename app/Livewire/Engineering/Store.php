<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\JobCard;
use App\Models\Engineering\SparesLine;
use App\Models\Engineering\SparesRequisition;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\SparesService;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Engineering store: incoming spares requisitions, and the register of job cards on their
 * way through certification, authorisation and purchasing.
 */
class Store extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $tab = 'spares';

    #[Url]
    public string $sparesFilter = 'pending';

    #[Url]
    public string $cardStatus = '';

    public string $search = '';

    /** @var array<string, string> quantity to issue, per line id */
    public array $issueQty = [];

    // Line being rejected
    public ?string $rejectLineId = null;

    public string $rejectReason = '';

    public function mount(): void
    {
        $user = Auth::user();

        abort_unless(
            EngineeringAccess::canRunStore($user) || EngineeringAccess::canProcessJobCards($user) || EngineeringAccess::canViewReports($user),
            403,
        );
    }

    public function updating(string $name): void
    {
        if (in_array($name, ['tab', 'sparesFilter', 'cardStatus', 'search'], true)) {
            $this->resetPage();
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'cards' ? 'cards' : 'spares';
        $this->search = '';
        $this->resetPage();
    }

    /** Issues the typed quantity, or everything outstanding when none was typed. */
    public function issue(string $lineId, SparesService $service): void
    {
        $line = SparesLine::findOrFail($lineId);
        $quantity = filled($this->issueQty[$lineId] ?? null) ? (float) $this->issueQty[$lineId] : $line->qty_requested - $line->qty_issued;

        if ($this->attempt(fn () => $service->issue($line, Auth::user(), $quantity), "Issued {$quantity} × {$line->spare_name}.")) {
            unset($this->issueQty[$lineId]);
        }
    }

    public function sendToProcurement(string $lineId, SparesService $service): void
    {
        $line = SparesLine::findOrFail($lineId);

        $this->attempt(fn () => $service->sendToProcurement($line, Auth::user()), "{$line->spare_name} marked for procurement. The engineer can now raise a job card.");
    }

    public function openReject(string $lineId): void
    {
        $this->rejectLineId = $lineId;
        $this->rejectReason = '';
    }

    public function closeReject(): void
    {
        $this->reset(['rejectLineId', 'rejectReason']);
    }

    public function reject(SparesService $service): void
    {
        $this->validate(['rejectReason' => ['required', 'string', 'min:3', 'max:300']], [], ['rejectReason' => 'reason']);

        if ($this->attempt(fn () => $service->reject(SparesLine::findOrFail($this->rejectLineId), Auth::user(), $this->rejectReason), 'Line rejected.')) {
            $this->closeReject();
        }
    }

    protected function sparesQuery(): Builder
    {
        return SparesRequisition::query()
            ->with(['lines', 'job.equipment:id,name,code', 'job.engineer:id,first_name,last_name', 'job.section:id,name', 'requestedBy:id,first_name,surname'])
            ->when($this->sparesFilter === 'pending', fn (Builder $q) => $q->whereIn('status', ['requested', 'partially_issued']))
            ->when($this->search, fn (Builder $q, $term) => $q->where(fn (Builder $w) => $w
                ->where('number', 'like', "%{$term}%")
                ->orWhereHas('job', fn (Builder $j) => $j->where('number', 'like', "%{$term}%"))
                ->orWhereHas('lines', fn (Builder $l) => $l->where('spare_name', 'like', "%{$term}%"))))
            ->oldest('requested_at');
    }

    protected function cardsQuery(): Builder
    {
        return JobCard::query()
            ->with(['job.equipment:id,name,code', 'job.section:id,name', 'requestingEngineer:id,first_name,last_name', 'approvals.actor:id,first_name,surname'])
            ->where('status', '!=', 'draft')
            ->when($this->cardStatus, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($this->search, fn (Builder $q, $term) => $q->where(fn (Builder $w) => $w
                ->where('number', 'like', "%{$term}%")
                ->orWhereHas('job', fn (Builder $j) => $j->where('number', 'like', "%{$term}%"))))
            ->latest('submitted_at');
    }

    public function export()
    {
        if ($this->tab === 'cards') {
            return $this->exportTable(
                'Job cards register',
                ['S/N', 'Job card No.', 'MRV No.', 'Equipment / area', 'Section', 'Requesting engineer', 'Submitted', 'Certified by / date', 'Authorised by / date', 'Estimated grand total', 'Order No.', 'Order date', 'Delivery date', 'Status', 'Days outstanding'],
                $this->cardsQuery()->get()->values()->map(fn (JobCard $card, int $i) => [
                    $i + 1,
                    $card->number,
                    $card->job->number,
                    $card->job->equipmentName(),
                    $card->job->section->name,
                    Eng::name($card->requestingEngineer),
                    Eng::dt($card->submitted_at),
                    $this->approvalLabel($card->certification()),
                    $this->approvalLabel($card->authorisation()),
                    $card->grand_total,
                    $card->order_number,
                    Eng::date($card->order_date),
                    Eng::date($card->delivery_actual_at),
                    Lookups::JOB_CARD_STATUSES[$card->status],
                    $card->delivery_actual_at ? '' : $card->daysSinceSubmitted(),
                ]),
                ['Status' => Lookups::label(Lookups::JOB_CARD_STATUSES, $this->cardStatus ?: null, ''), 'Search' => $this->search],
            );
        }

        $rows = $this->sparesQuery()->get()->flatMap(fn (SparesRequisition $sr) => $sr->lines->map(fn (SparesLine $line) => [
            $sr->number,
            $sr->job->number,
            $sr->job->equipmentName(),
            Eng::name($sr->job->engineer),
            Eng::dt($sr->requested_at),
            $line->spare_name,
            $line->uom,
            $line->item_code,
            $line->qty_requested,
            $line->qty_issued,
            Lookups::SPARES_LINE_STATUSES[$line->status],
            Eng::dt($line->issued_at),
            $line->reject_reason,
        ]));

        return $this->exportTable(
            'Spares requisitions',
            ['SR No.', 'MRV No.', 'Equipment', 'Engineer', 'Requested', 'Spare', 'UoM', 'Item code', 'Qty requested', 'Qty issued', 'Line status', 'Processed', 'Reject reason'],
            $rows,
            ['View' => ucfirst($this->sparesFilter), 'Search' => $this->search],
        );
    }

    protected function approvalLabel($approval): string
    {
        return $approval ? $approval->actor?->full_name.' · '.Eng::dt($approval->created_at) : '';
    }

    public function render(SparesService $spares)
    {
        $user = Auth::user();
        $requisitions = $this->tab === 'spares' ? $this->sparesQuery()->paginate(10) : null;

        return view('livewire.engineering.store', [
            'requisitions' => $requisitions,
            'stock' => $requisitions
                ? $requisitions->getCollection()->flatMap->lines->pluck('product_id')->filter()->unique()
                    ->mapWithKeys(fn (string $productId) => [$productId => $spares->stockOnHand($productId)])
                : collect(),
            'cards' => $this->tab === 'cards' ? $this->cardsQuery()->paginate(15) : null,
            'isStoreKeeper' => EngineeringAccess::canRunStore($user),
            'pendingSpares' => SparesRequisition::whereIn('status', ['requested', 'partially_issued'])->count(),
            'pendingCards' => JobCard::whereIn('status', ['pending_certification', 'pending_authorisation', 'approved', 'purchasing'])->count(),
        ]);
    }
}
