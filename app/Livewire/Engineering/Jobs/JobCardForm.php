<?php

namespace App\Livewire\Engineering\Jobs;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\JobCard;
use App\Models\Engineering\MaintenanceJob;
use App\Models\LocalPurchaseOrder;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\EngineeringActionException;
use App\Services\Engineering\JobCardService;
use App\Support\Engineering\Lookups;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Engineering job card: the engineer costs it, a certifier and an authoriser approve it,
 * and the purchasing office records the order and delivery.
 */
class JobCardForm extends Component
{
    use InteractsWithEngineering;

    protected const PURCHASING_FIELDS = [
        'local_purchase_order_id', 'order_number', 'order_date', 'supplier_name',
        'proforma_requested_at', 'proforma_received_at', 'spare_procured_at',
        'tender_opened_at', 'tender_approved_at', 'delivery_expected_at', 'delivery_actual_at',
        'delay_reason_order', 'delay_reason_delivery',
    ];

    public string $jobId;

    public ?string $cardId = null;

    // Why no job card can be shown or raised for this job
    public ?string $unavailable = null;

    /** @var array<string, mixed> narrative and costing fields, keyed by column */
    public array $form = [];

    /** @var array<int, array{description:string, uom:string, item_code:string, quantity:string, price:string}> */
    public array $items = [];

    public string $decisionComment = '';

    /** @var array<string, mixed> */
    public array $purchasing = [];

    public function mount(MaintenanceJob $job, JobCardService $service): void
    {
        $user = Auth::user();

        abort_unless(
            EngineeringAccess::canViewJob($user, $job) || EngineeringAccess::canProcessJobCards($user) || EngineeringAccess::canRunStore($user),
            403,
        );

        $this->jobId = $job->id;

        try {
            $card = $service->forJob($job, $user);
        } catch (EngineeringActionException $e) {
            $this->unavailable = $e->getMessage();

            return;
        }

        $this->cardId = $card->id;
        $this->loadCard($card);
    }

    protected function card(): JobCard
    {
        return JobCard::findOrFail($this->cardId);
    }

    /** Loads the stored card into the form state. */
    protected function loadCard(JobCard $card): void
    {
        $this->form = [
            'complaints_faults' => (string) $card->complaints_faults,
            'defects' => (string) $card->defects,
            'diagnosis_action' => (string) $card->diagnosis_action,
            'failure_reason' => (string) $card->failure_reason,
            'failure_reason_text' => (string) $card->failure_reason_text,
            'date_part_requested' => $card->date_part_requested?->toDateString(),
            'description' => (string) $card->description,
            'comments' => (string) $card->comments,
            'labour_charge' => $card->labour_charge + 0,
            'labour_qty' => $card->labour_qty + 0,
            'fabrication_charge' => $card->fabrication_charge + 0,
            'fabrication_qty' => $card->fabrication_qty + 0,
        ];

        $this->items = $card->items->map(fn ($item) => [
            'description' => $item->description,
            'uom' => (string) $item->uom,
            'item_code' => (string) $item->item_code,
            'quantity' => (string) ($item->quantity + 0),
            'price' => (string) ($item->price + 0),
        ])->all();

        $this->purchasing = collect(self::PURCHASING_FIELDS)
            ->mapWithKeys(fn (string $field) => [$field => $card->{$field} instanceof DateTimeInterface ? $card->{$field}->format('Y-m-d') : $card->{$field}])
            ->all();
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'uom' => '', 'item_code' => '', 'quantity' => '1', 'price' => '0'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /** Pulls order number, date and supplier from an LPO that already exists in purchasing. */
    public function updatedPurchasingLocalPurchaseOrderId($lpoId): void
    {
        $lpo = $lpoId ? LocalPurchaseOrder::find($lpoId) : null;

        if ($lpo) {
            $this->purchasing['order_number'] = $lpo->lpo_number;
            $this->purchasing['order_date'] = $lpo->generated_at?->toDateString();
        }
    }

    public function save(JobCardService $service): bool
    {
        $this->validate([
            'form.failure_reason' => ['nullable', Rule::in(array_keys(Lookups::FAILURE_REASONS))],
            'form.failure_reason_text' => ['required_if:form.failure_reason,other', 'nullable', 'string', 'max:300'],
            'form.date_part_requested' => ['nullable', 'date'],
            'form.labour_charge' => ['required', 'numeric', 'min:0'],
            'form.labour_qty' => ['required', 'numeric', 'min:0'],
            'form.fabrication_charge' => ['required', 'numeric', 'min:0'],
            'form.fabrication_qty' => ['required', 'numeric', 'min:0'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ], [
            'form.failure_reason_text.required_if' => 'Describe the reason of failure when "Others" is selected.',
        ], [
            'items.*.description' => 'item', 'items.*.quantity' => 'quantity', 'items.*.price' => 'price',
            'form.labour_charge' => 'labour charge', 'form.labour_qty' => 'labour quantity',
            'form.fabrication_charge' => 'fabrication charge', 'form.fabrication_qty' => 'fabrication quantity',
        ]);

        $data = array_merge($this->form, [
            'failure_reason' => $this->form['failure_reason'] ?: null,
            'date_part_requested' => $this->form['date_part_requested'] ?: null,
        ]);

        return $this->attempt(fn () => $service->save($this->card(), Auth::user(), $data, $this->items), 'Job card saved.');
    }

    public function submit(JobCardService $service): void
    {
        if ($this->save($service)) {
            $this->attempt(fn () => $service->submit($this->card(), Auth::user()), 'Job card submitted for certification.');
        }
    }

    public function decide(string $decision, JobCardService $service): void
    {
        abort_unless(in_array($decision, ['approved', 'returned', 'rejected'], true), 404);

        $done = $this->attempt(
            fn () => $service->decide($this->card(), Auth::user(), $decision, $this->decisionComment ?: null),
            'Decision recorded: '.$decision.'.',
        );

        if ($done) {
            $this->decisionComment = '';
        }
    }

    public function savePurchasing(JobCardService $service): void
    {
        $dateFields = array_filter(self::PURCHASING_FIELDS, fn (string $field) => str_ends_with($field, '_at') || $field === 'order_date');

        $this->validate(
            collect($dateFields)->mapWithKeys(fn (string $field) => ["purchasing.{$field}" => ['nullable', 'date']])->all(),
            [],
            collect($dateFields)->mapWithKeys(fn (string $field) => ["purchasing.{$field}" => str_replace('_', ' ', $field)])->all(),
        );

        // Empty inputs are stored as null, not as empty strings
        $data = array_map(fn ($value) => $value === '' ? null : $value, $this->purchasing);

        if ($this->attempt(fn () => $service->updatePurchasing($this->card(), Auth::user(), $data), 'Purchasing information saved.')) {
            $this->loadCard($this->card());
        }
    }

    /**
     * Live totals from what is on screen, so the engineer sees them before saving.
     *
     * @return array{sub:float, labour:float, fabrication:float, grand:float}
     */
    protected function totals(): array
    {
        $sub = collect($this->items)->sum(fn (array $item) => (float) $item['quantity'] * (float) $item['price']);
        $labour = (float) $this->form['labour_charge'] * (float) $this->form['labour_qty'];
        $fabrication = (float) $this->form['fabrication_charge'] * (float) $this->form['fabrication_qty'];

        return ['sub' => $sub, 'labour' => $labour, 'fabrication' => $fabrication, 'grand' => $sub + $labour + $fabrication];
    }

    public function render()
    {
        if (! $this->cardId) {
            return view('livewire.engineering.jobs.job-card-unavailable', ['job' => MaintenanceJob::findOrFail($this->jobId)]);
        }

        $user = Auth::user();
        $card = JobCard::with([
            'job.requisition.department', 'job.requisition.room', 'job.equipment', 'job.section', 'job.engineer', 'job.assistants',
            'requestingEngineer', 'approvals.actor', 'items',
        ])->findOrFail($this->cardId);

        return view('livewire.engineering.jobs.job-card', [
            'card' => $card,
            'job' => $card->job,
            'totals' => $this->totals(),
            'canEdit' => $card->isEditable() && EngineeringAccess::canWorkJob($user, $card->job),
            'canDecide' => ($card->status === 'pending_certification' && EngineeringAccess::canCertify($user))
                || ($card->status === 'pending_authorisation' && EngineeringAccess::canAuthorise($user)),
            'canPurchase' => $card->isApproved() && EngineeringAccess::canPurchase($user),
            'lpos' => $card->isApproved() && EngineeringAccess::canPurchase($user)
                ? LocalPurchaseOrder::latest()->limit(50)->get(['id', 'lpo_number', 'generated_at'])
                : collect(),
            'limits' => config('engineering.procurement'),
        ]);
    }
}
