<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Employee;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\PpmConsumable;
use App\Models\Engineering\PpmEntry;
use App\Models\Engineering\PpmInstance;
use App\Models\Engineering\PpmSchedule;
use App\Models\Engineering\PpmTask;
use App\Models\Engineering\Section;
use App\Models\products;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\PpmService;
use App\Services\Engineering\SparesService;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * PPM schedule and checklists: what is due, who is responsible, and the checklist of each occurrence.
 */
class Ppm extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $state = 'attention';

    #[Url]
    public string $filterSection = '';

    public string $filterDepartment = '';

    public string $filterPerformer = '';

    public string $search = '';

    // Checklist being filled; can be carried in from a job
    #[Url]
    public ?string $checklist = null;

    /** @var array<string, array{done:bool, not_applicable:bool, value:string, comment:string}> keyed by entry id */
    public array $entries = [];

    // Schedule add / edit
    public bool $formOpen = false;

    public ?string $editingId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<int, array{description:string, expected_value:string, mandatory:bool}> */
    public array $tasks = [];

    /** @var array<int, array{item_name:string, item_code:string, uom:string, qty_required:string}> */
    public array $consumables = [];

    public string $equipmentSearch = '';

    public function mount(): void
    {
        if ($this->checklist) {
            $this->loadEntries();
        }
    }

    public function updating(string $name): void
    {
        if ($name === 'state' || $name === 'search' || str_starts_with($name, 'filter')) {
            $this->resetPage();
        }
    }

    /* ---------- Schedule list actions ---------- */

    public function generate(string $scheduleId, PpmService $service): void
    {
        $schedule = PpmSchedule::with(['equipment', 'tasks', 'responsible', 'openInstance.job'])->findOrFail($scheduleId);

        $this->attempt(function () use ($service, $schedule) {
            $job = $service->generateRequisition($schedule, Auth::user());

            session()->flash('success', $job->engineer
                ? "PPM requisition {$job->number} raised and assigned to ".Eng::name($job->engineer).'.'
                : "PPM requisition {$job->number} raised. It is waiting in the helpdesk queue for assignment.");
        });
    }

    public function openChecklist(string $scheduleId, PpmService $service): void
    {
        $schedule = PpmSchedule::with(['tasks', 'openInstance'])->findOrFail($scheduleId);

        $this->attempt(function () use ($service, $schedule) {
            $this->checklist = $service->openChecklist($schedule)->id;
            $this->loadEntries();
        });
    }

    public function viewChecklist(string $instanceId): void
    {
        $this->checklist = $instanceId;
        $this->loadEntries();
    }

    public function closeChecklist(): void
    {
        $this->reset(['checklist', 'entries']);
    }

    /* ---------- Checklist ---------- */

    protected function loadEntries(): void
    {
        $this->entries = PpmEntry::where('instance_id', $this->checklist)->get()
            ->mapWithKeys(fn (PpmEntry $entry) => [$entry->id => [
                'done' => $entry->done,
                'not_applicable' => $entry->not_applicable,
                'value' => (string) $entry->value,
                'comment' => (string) $entry->comment,
            ]])->all();
    }

    /** Any change to a task line is saved straight away, stamped with who ticked it and when. */
    public function updatedEntries($value, string $key): void
    {
        $entryId = explode('.', $key)[0];
        $entry = PpmEntry::where('instance_id', $this->checklist)->findOrFail($entryId);

        if (! $this->attempt(fn () => app(PpmService::class)->saveEntry($entry, Auth::user(), $this->entries[$entryId]))) {
            $this->loadEntries();
        }
    }

    public function completeChecklist(PpmService $service): void
    {
        $instance = PpmInstance::with(['entries', 'schedule.equipment'])->findOrFail($this->checklist);

        $this->attempt(fn () => $service->complete($instance, Auth::user()), 'PPM completed. The next due date has been set.');
    }

    /** Sends the consumables the store is short of as a spares requisition against the PPM job. */
    public function requestConsumables(PpmService $ppm, SparesService $spares): void
    {
        $instance = PpmInstance::with(['schedule.consumables', 'job'])->findOrFail($this->checklist);

        if (! $instance->job) {
            session()->flash('error', 'Generate the PPM requisition first: spares are requested against its job.');

            return;
        }

        $lines = collect($ppm->consumablesStatus($instance->schedule, $spares))
            ->map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'spare_name' => $item['name'],
                'uom' => $item['uom'],
                'item_code' => $item['code'],
                'quantity' => $item['required'],
            ])->all();

        $this->attempt(function () use ($spares, $instance, $lines) {
            $requisition = $spares->send($instance->job, Auth::user(), $lines);
            session()->flash('success', "Consumables requested from the store on {$requisition->number}.");
        });
    }

    /* ---------- Schedule form ---------- */

    public function create(): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $this->editingId = null;
        $this->form = ['equipment_id' => '', 'frequency' => '6_months', 'interval_hours' => null, 'performer' => 'engineering', 'responsible_employee_id' => '', 'next_due_at' => null];
        $this->tasks = [['description' => '', 'expected_value' => '', 'mandatory' => true]];
        $this->consumables = [];
        $this->equipmentSearch = '';
        $this->formOpen = true;
    }

    public function edit(string $scheduleId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $schedule = PpmSchedule::with(['equipment', 'tasks', 'consumables'])->findOrFail($scheduleId);

        $this->editingId = $schedule->id;
        $this->form = [
            'equipment_id' => $schedule->equipment_id,
            'frequency' => $schedule->frequency,
            'interval_hours' => $schedule->interval_hours,
            'performer' => $schedule->performer,
            'responsible_employee_id' => (string) $schedule->responsible_employee_id,
            'next_due_at' => $schedule->next_due_at?->toDateString(),
        ];
        $this->tasks = $schedule->tasks->map(fn (PpmTask $task) => ['description' => $task->description, 'expected_value' => (string) $task->expected_value, 'mandatory' => $task->mandatory])->all();
        $this->consumables = $schedule->consumables->map(fn (PpmConsumable $item) => ['item_name' => $item->item_name, 'item_code' => (string) $item->item_code, 'uom' => (string) $item->uom, 'qty_required' => (string) ($item->qty_required + 0)])->all();
        $this->equipmentSearch = "{$schedule->equipment->name} ({$schedule->equipment->code})";
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
    }

    public function pickEquipment(string $equipmentId): void
    {
        if ($equipment = Equipment::inService()->find($equipmentId)) {
            $this->form['equipment_id'] = $equipment->id;
            $this->equipmentSearch = "{$equipment->name} ({$equipment->code})";
        }
    }

    public function clearEquipment(): void
    {
        $this->form['equipment_id'] = '';
        $this->equipmentSearch = '';
    }

    public function addTask(): void
    {
        $this->tasks[] = ['description' => '', 'expected_value' => '', 'mandatory' => true];
    }

    public function removeTask(int $index): void
    {
        unset($this->tasks[$index]);
        $this->tasks = array_values($this->tasks);
    }

    public function addConsumable(): void
    {
        $this->consumables[] = ['item_name' => '', 'item_code' => '', 'uom' => '', 'qty_required' => '1'];
    }

    public function removeConsumable(int $index): void
    {
        unset($this->consumables[$index]);
        $this->consumables = array_values($this->consumables);
    }

    public function save(PpmService $service): void
    {
        $this->authorizeManage();

        $this->validate([
            'form.equipment_id' => ['required', Rule::exists('eng_equipment', 'id')],
            'form.frequency' => ['required', Rule::in(array_keys(Lookups::PPM_FREQUENCIES))],
            'form.interval_hours' => ['required_if:form.frequency,running_hours', 'nullable', 'integer', 'min:1'],
            'form.next_due_at' => ['required_if:form.frequency,running_hours', 'nullable', 'date'],
            'form.performer' => ['required', Rule::in(array_keys(Lookups::PPM_PERFORMERS)),
                Rule::unique('eng_ppm_schedules', 'performer')->where('equipment_id', $this->form['equipment_id'])->ignore($this->editingId)],
            'form.responsible_employee_id' => ['required', Rule::exists('employees', 'id')],
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*.description' => ['required', 'string', 'max:300'],
            'consumables.*.item_name' => ['required', 'string', 'max:200'],
            'consumables.*.qty_required' => ['required', 'numeric', 'gt:0'],
        ], [
            'form.equipment_id.required' => 'Pick the equipment from the inventory.',
            'form.performer.unique' => 'This equipment already has a PPM schedule for that performer.',
            'form.next_due_at.required_if' => 'Enter the date the running-hours PPM is expected to be due.',
            'tasks.required' => 'Add at least one checklist task.',
        ], [
            'form.responsible_employee_id' => 'responsible staff', 'form.interval_hours' => 'interval hours',
            'tasks.*.description' => 'task', 'consumables.*.item_name' => 'item', 'consumables.*.qty_required' => 'quantity',
        ]);

        DB::transaction(function () use ($service) {
            $attributes = [
                'equipment_id' => $this->form['equipment_id'],
                'frequency' => $this->form['frequency'],
                'interval_hours' => $this->form['frequency'] === 'running_hours' ? $this->form['interval_hours'] : null,
                'performer' => $this->form['performer'],
                'responsible_employee_id' => $this->form['responsible_employee_id'],
                'next_due_at' => $this->form['next_due_at'] ?: null,
            ];

            $schedule = $this->editingId ? tap(PpmSchedule::findOrFail($this->editingId))->update($attributes) : PpmSchedule::create($attributes);

            // The template only: checklists already generated keep their own copy of the tasks
            $schedule->tasks()->delete();
            foreach (array_values($this->tasks) as $index => $task) {
                PpmTask::create(['schedule_id' => $schedule->id, 'seq' => $index + 1, 'description' => $task['description'], 'expected_value' => $task['expected_value'] ?: null, 'mandatory' => (bool) $task['mandatory']]);
            }

            $schedule->consumables()->delete();
            foreach ($this->consumables as $item) {
                PpmConsumable::create([
                    'schedule_id' => $schedule->id,
                    // A matching item code ties the consumable to the store, so stock on hand can be shown
                    'product_id' => $item['item_code'] ? products::where('code', $item['item_code'])->value('id') : null,
                    'item_name' => $item['item_name'],
                    'item_code' => $item['item_code'] ?: null,
                    'uom' => $item['uom'] ?: null,
                    'qty_required' => $item['qty_required'],
                ]);
            }

            // Calendar frequencies are always computed; a typed date only applies to running hours
            if ($schedule->frequency !== 'running_hours') {
                $schedule->update(['next_due_at' => $service->nextDue($schedule->refresh())]);
            }
        });

        session()->flash('success', 'PPM schedule saved.');
        $this->formOpen = false;
    }

    protected function authorizeManage(): void
    {
        abort_unless(EngineeringAccess::canManageInventory(Auth::user()), 403);
    }

    protected function query(): Builder
    {
        return PpmSchedule::query()
            ->active()
            ->with(['equipment.room:id,name', 'equipment.department:id,name', 'equipment.section:id,name', 'responsible:id,first_name,last_name', 'lastDoneBy:id,first_name,surname', 'openInstance.job:id,number', 'openInstance.entries', 'tasks:id,schedule_id', 'consumables'])
            ->when($this->state === 'attention', fn (Builder $q) => $q->needingAttention())
            ->when($this->filterPerformer, fn (Builder $q, $performer) => $q->where('performer', $performer))
            ->whereHas('equipment', fn (Builder $q) => $q
                ->when($this->filterSection, fn (Builder $e, $id) => $e->where('section_id', $id))
                ->when($this->filterDepartment, fn (Builder $e, $id) => $e->where('department_id', $id))
                ->search($this->search))
            ->orderByRaw('next_due_at is null')
            ->orderBy('next_due_at');
    }

    public function export(SparesService $spares, PpmService $ppm)
    {
        return $this->exportTable(
            'PPM report',
            ['S/N', 'PPM MRV No.', 'Responsible staff', 'Section', 'Equipment', 'Code', 'Department / room', 'Frequency', 'Performer', 'Last PPM done', 'Due date', 'Days to due', 'Overdue', 'Tasks due', 'Tasks completed', 'Consumables required', 'Consumables available in store'],
            $this->query()->get()->values()->map(function (PpmSchedule $schedule, int $i) use ($spares, $ppm) {
                $stock = collect($ppm->consumablesStatus($schedule, $spares));
                $instance = $schedule->openInstance;

                return [
                    $i + 1,
                    $instance?->job?->number,
                    Eng::name($schedule->responsible),
                    $schedule->equipment->section->name,
                    $schedule->equipment->name,
                    $schedule->equipment->code,
                    $schedule->equipment->department->name.' / '.$schedule->equipment->room->name,
                    Lookups::PPM_FREQUENCIES[$schedule->frequency],
                    Lookups::PPM_PERFORMERS[$schedule->performer],
                    $schedule->last_done_at ? Eng::dt($schedule->last_done_at).' by '.$schedule->lastDoneBy?->full_name : 'Never',
                    Eng::date($schedule->next_due_at),
                    $schedule->daysToDue(),
                    $schedule->dueState() === PpmSchedule::STATE_OVERDUE ? 'Yes' : 'No',
                    $schedule->tasks->count(),
                    $instance ? $instance->entries->filter->isSatisfied()->count() : 0,
                    $stock->map(fn ($c) => "{$c['name']} x{$c['required']}")->implode('; '),
                    $stock->map(fn ($c) => "{$c['name']}: ".($c['in_store'] ?? 'not stocked'))->implode('; '),
                ];
            }),
            ['View' => $this->state === 'attention' ? 'Due and overdue' : 'All schedules', 'Section' => Section::find($this->filterSection)?->name, 'Search' => $this->search],
        );
    }

    public function render(PpmService $ppm, SparesService $spares)
    {
        $user = Auth::user();
        $attention = PpmSchedule::active()->needingAttention()->get(['id', 'next_due_at']);

        $instance = $this->checklist
            ? PpmInstance::with(['entries.tickedBy', 'schedule.equipment.room', 'schedule.consumables', 'schedule.responsible', 'job', 'completedBy'])->find($this->checklist)
            : null;

        $search = trim($this->equipmentSearch);

        return view('livewire.engineering.ppm', [
            'schedules' => $this->query()->paginate(15),
            'dueSoon' => $attention->filter(fn (PpmSchedule $s) => $s->dueState() === PpmSchedule::STATE_DUE_SOON)->count(),
            'overdue' => $attention->filter(fn (PpmSchedule $s) => $s->dueState() === PpmSchedule::STATE_OVERDUE)->count(),
            'sections' => Section::active()->get(),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'canManage' => EngineeringAccess::canManageInventory($user),
            'instance' => $instance,
            'canFill' => $instance ? $ppm->canFill($instance, $user) : false,
            'consumablesStatus' => $instance ? $ppm->consumablesStatus($instance->schedule, $spares) : [],
            'equipmentMatches' => $this->formOpen && ! $this->form['equipment_id'] && mb_strlen($search) >= 2
                ? Equipment::inService()->search($search)->orderBy('name')->limit(6)->get(['id', 'name', 'code'])
                : collect(),
            'engineers' => $this->formOpen
                ? Employee::whereIn('id', DB::table('eng_section_staff')->select('employee_id'))->where('status', 'Active')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }
}
