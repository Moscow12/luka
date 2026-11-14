<?php

namespace App\Livewire\Engineering\Jobs;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\Engineering\MaintenanceJob;
use App\Models\products;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\JobService;
use App\Services\Engineering\SparesService;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Maintenance & procurement: the one page where an engineer reads the job, keeps notes,
 * requests spares, records safety tests and signs the job out.
 */
class Workspace extends Component
{
    use InteractsWithEngineering;

    public string $jobId;

    public string $noteBody = '';

    // Spares requisition being written
    public bool $sparesOpen = false;

    /** @var array<int, array{product_id:?string, spare_name:string, uom:string, item_code:string, quantity:string, date_consumed:string}> */
    public array $spareLines = [];

    public string $spareSearch = '';

    /** @var array<string, string> comment per safety test, needed to mark one "not applicable" */
    public array $testComments = [];

    // Final status & closure
    public string $finalStatus = '';

    public string $explanation = '';

    public string $recommendation = '';

    public string $equipmentStatus = 'working';

    public ?int $qtyDelivered = null;

    public string $receivedBy = '';

    public function mount(MaintenanceJob $job, JobService $service): void
    {
        abort_unless(EngineeringAccess::canViewJob(Auth::user(), $job), 403);

        $service->open($job, Auth::user());

        $this->jobId = $job->id;
        $this->recommendation = (string) $job->recommendation;
        $this->qtyDelivered = $job->oxygenOrder?->qty_ordered;
    }

    protected function job(): MaintenanceJob
    {
        return MaintenanceJob::findOrFail($this->jobId);
    }

    /* ---------- Job notes ---------- */

    public function saveNote(JobService $service): void
    {
        $this->validate(['noteBody' => ['required', 'string', 'min:3', 'max:3000']], [], ['noteBody' => 'note']);

        if ($this->attempt(fn () => $service->addNote($this->job(), Auth::user(), $this->noteBody))) {
            $this->noteBody = '';
        }
    }

    /* ---------- Spares ---------- */

    public function openSpares(): void
    {
        $this->resetErrorBag();
        $this->sparesOpen = true;
        $this->spareLines = [];
        $this->spareSearch = '';
    }

    public function closeSpares(): void
    {
        $this->reset(['sparesOpen', 'spareLines', 'spareSearch']);
    }

    /** Adds a stock item from the store's item master. */
    public function addStockItem(string $productId): void
    {
        if ($product = products::find($productId)) {
            $this->spareLines[] = $this->blankLine($product->id, $product->name, (string) $product->unit, (string) $product->code);
            $this->spareSearch = '';
        }
    }

    /** Adds a free-text line for an item the store does not stock. */
    public function addNonStockItem(): void
    {
        $this->spareLines[] = $this->blankLine(null, trim($this->spareSearch));
        $this->spareSearch = '';
    }

    public function removeSpareLine(int $index): void
    {
        unset($this->spareLines[$index]);
        $this->spareLines = array_values($this->spareLines);
    }

    public function sendSpares(SparesService $service): void
    {
        $this->validate([
            'spareLines' => ['required', 'array', 'min:1'],
            'spareLines.*.spare_name' => ['required', 'string', 'max:200'],
            'spareLines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'spareLines.*.date_consumed' => ['nullable', 'date'],
        ], [
            'spareLines.required' => 'Add at least one spare.',
        ], ['spareLines.*.spare_name' => 'spare', 'spareLines.*.quantity' => 'quantity']);

        $sent = $this->attempt(function () use ($service) {
            $requisition = $service->send($this->job(), Auth::user(), $this->spareLines);
            session()->flash('success', "Spares requisition {$requisition->number} sent to the engineering store.");
        });

        if ($sent) {
            $this->closeSpares();
        }
    }

    public function toggleNoSpares(SparesService $service): void
    {
        $job = $this->job();

        $this->attempt(fn () => $service->markNoSparesConsumed($job, Auth::user(), ! $job->no_spares_consumed));
    }

    public function informClient(JobService $service): void
    {
        $this->attempt(fn () => $service->informClient($this->job(), Auth::user()), 'The requester has been informed that the spare is being sourced.');
    }

    /* ---------- Safety testing ---------- */

    public function toggleTest(string $type, JobService $service): void
    {
        $job = $this->job();
        $current = $job->safetyTests->firstWhere('test_type', $type);

        $this->attempt(fn () => $service->saveSafetyTest($job, Auth::user(), $type, ! ($current?->isSatisfied() ?? false), false, $this->testComments[$type] ?? null));
    }

    public function markTestNotApplicable(string $type, JobService $service): void
    {
        $this->attempt(fn () => $service->saveSafetyTest($this->job(), Auth::user(), $type, false, true, $this->testComments[$type] ?? null));
    }

    /* ---------- Closure ---------- */

    public function close(JobService $service): void
    {
        $this->validate([
            'finalStatus' => ['required', Rule::in(array_keys(Lookups::FINAL_STATUSES))],
            'explanation' => ['required_if:finalStatus,assistance_needed', 'nullable', 'string', 'max:2000'],
            'recommendation' => ['nullable', 'string', 'max:2000'],
            'equipmentStatus' => [Rule::in(array_keys(Lookups::EQUIPMENT_STATUSES))],
        ], [
            'finalStatus.required' => 'Select the final status of the job.',
            'explanation.required_if' => 'Explain what additional assistance is needed.',
        ]);

        $job = $this->job();

        $closed = $this->attempt(fn () => $service->close($job, Auth::user(), [
            'final_status' => $this->finalStatus,
            'explanation' => $this->explanation ?: null,
            'recommendation' => $this->recommendation ?: null,
            'equipment_status' => $this->equipmentStatus,
            'qty_delivered' => $this->qtyDelivered,
            'received_by' => $this->receivedBy ?: null,
        ]), $this->finalStatus === 'assistance_needed'
            ? 'Assistance requested. The head of section and the HOD have been notified; the job stays open.'
            : "{$job->number} signed out. The requester has been asked for feedback.");

        if ($closed) {
            $this->reset(['finalStatus', 'explanation']);
        }
    }

    protected function blankLine(?string $productId, string $name, string $uom = '', string $code = ''): array
    {
        return [
            'product_id' => $productId,
            'spare_name' => $name,
            'uom' => $uom,
            'item_code' => $code,
            'quantity' => '1',
            'date_consumed' => Eng::now()->toDateString(),
        ];
    }

    public function render(JobService $service)
    {
        $job = MaintenanceJob::with([
            'requisition.department', 'requisition.room', 'requisition.category', 'equipment.room', 'section',
            'engineer', 'assistants', 'assignedBy', 'notes.author', 'safetyTests.completedBy',
            'sparesRequisitions.lines', 'jobCard', 'rca', 'oxygenOrder', 'ppmInstance.entries', 'technicalReport',
            'currentFeedback', 'signedOutBy',
        ])->findOrFail($this->jobId);

        $search = trim($this->spareSearch);

        return view('livewire.engineering.jobs.workspace', [
            'job' => $job,
            'canWork' => EngineeringAccess::canWorkJob(Auth::user(), $job),
            'blockers' => $job->isOpen() ? $service->completionBlockers($job) : [],
            'needsSafetyTests' => ! $job->oxygenOrder && in_array($job->section->code, config('engineering.safety_test_sections'), true),
            'productMatches' => $this->sparesOpen && mb_strlen($search) >= 2
                ? products::where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))->orderBy('name')->limit(6)->get(['id', 'name', 'code', 'unit'])
                : collect(),
        ]);
    }
}
