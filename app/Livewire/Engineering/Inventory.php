<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\Room;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\InventoryService;
use App\Support\Engineering\Eng;
use App\Support\Engineering\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Equipment register: what the hospital has, where it is, and whether it works.
 */
class Inventory extends Component
{
    use InteractsWithEngineering, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public const VIEWS = [
        'register' => 'In service',
        'replacement' => 'Due for replacement',
        'flagged' => 'Flagged for removal',
        'removed' => 'Removed',
    ];

    protected const BLANK_FORM = [
        'name' => '', 'section_id' => '', 'code' => '', 'serial_number' => '', 'model' => '',
        'manufacturer' => '', 'manufacturer_email' => '', 'manufacturer_phone' => '', 'manufacturer_postal' => '',
        'supplier' => '', 'under_contract' => false, 'contract_expiry' => null,
        'department_id' => '', 'room_id' => '', 'quantity' => 1, 'installation_date' => null,
        'condition_received' => 'new', 'expected_life_years' => null, 'manual_location' => '',
        'purchase_cost' => null, 'current_value' => null, 'counted_at' => null,
    ];

    #[Url]
    public string $view = 'register';

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterSection = '';

    #[Url]
    public string $filterDepartment = '';

    public string $filterRoom = '';

    #[Url]
    public string $filterStatus = '';

    // Add / edit
    public bool $formOpen = false;

    public ?string $editingId = null;

    /** @var array<string, mixed> */
    public array $form = self::BLANK_FORM;

    public string $codePrefix = '';

    // Status change
    public ?string $statusEquipmentId = null;

    public string $newStatus = 'working';

    public string $statusNote = '';

    // Removal
    public ?string $removeEquipmentId = null;

    public string $removalReason = '';

    public string $removalApprovedBy = '';

    // Detail drawer
    public ?string $detailId = null;

    public function updating(string $name): void
    {
        if ($name === 'view' || $name === 'search' || str_starts_with($name, 'filter')) {
            $this->resetPage();
        }
    }

    public function updatedFilterDepartment(): void
    {
        $this->filterRoom = '';
    }

    public function updatedFormDepartmentId(): void
    {
        $this->form['room_id'] = '';
    }

    public function setView(string $view): void
    {
        $this->view = array_key_exists($view, self::VIEWS) ? $view : 'register';
        $this->resetPage();
    }

    public function showDetail(?string $equipmentId): void
    {
        $this->detailId = $equipmentId;
    }

    /* ---------- Add / edit ---------- */

    public function create(): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $this->editingId = null;
        $this->form = array_merge(self::BLANK_FORM, ['counted_at' => Eng::now()->toDateString()]);
        $this->codePrefix = '';
        $this->formOpen = true;
    }

    public function edit(string $equipmentId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $equipment = Equipment::findOrFail($equipmentId);

        $this->editingId = $equipment->id;
        $this->form = array_merge(self::BLANK_FORM, $equipment->only(array_keys(self::BLANK_FORM)), [
            'contract_expiry' => $equipment->contract_expiry?->toDateString(),
            'installation_date' => $equipment->installation_date->toDateString(),
            'counted_at' => $equipment->counted_at->toDateString(),
        ]);
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
    }

    /** Fills the code with the next free sequence for a prefix such as BMC/LAB/CHM/CFM. */
    public function suggestCode(InventoryService $service): void
    {
        $this->validate(['codePrefix' => ['required', 'string', 'regex:/^[A-Za-z0-9]+(\/[A-Za-z0-9]+)*$/']], [
            'codePrefix.regex' => 'Use letters and digits separated by slashes, e.g. BMC/LAB/CHM/CFM.',
        ], ['codePrefix' => 'code prefix']);

        $this->form['code'] = $service->nextCode($this->codePrefix);
    }

    public function save(InventoryService $service): void
    {
        $this->authorizeManage();

        $this->validate([
            'form.name' => ['required', 'string', 'max:150'],
            'form.section_id' => ['required', Rule::exists('eng_sections', 'id')],
            'form.code' => ['required', 'string', 'max:60', Rule::unique('eng_equipment', 'code')->ignore($this->editingId)],
            'form.serial_number' => ['required', 'string', 'max:100'],
            'form.manufacturer' => ['required', 'string', 'max:150'],
            'form.manufacturer_email' => ['nullable', 'email', 'max:150'],
            'form.department_id' => ['required', Rule::exists('departments', 'id')],
            'form.room_id' => ['required', Rule::exists('eng_rooms', 'id')->where('department_id', $this->form['department_id'])],
            'form.quantity' => ['required', 'integer', 'min:1'],
            'form.installation_date' => ['required', 'date', 'before_or_equal:today'],
            'form.condition_received' => ['required', Rule::in(['new', 'old'])],
            'form.expected_life_years' => ['nullable', 'integer', 'min:1', 'max:100'],
            'form.manual_location' => ['required', 'string', 'max:200'],
            'form.contract_expiry' => ['nullable', 'date'],
            'form.purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'form.current_value' => ['nullable', 'numeric', 'min:0'],
            'form.counted_at' => ['required', 'date', 'before_or_equal:today'],
        ], [], collect(array_keys(self::BLANK_FORM))->mapWithKeys(fn (string $field) => ["form.{$field}" => str_replace(['_id', '_'], ['', ' '], $field)])->all());

        $data = array_map(fn ($value) => $value === '' ? null : $value, $this->form);
        $data['code'] = strtoupper($data['code']);

        if ($this->editingId) {
            Equipment::findOrFail($this->editingId)->update($data);
        } else {
            $equipment = Equipment::create($data + ['counted_by' => Auth::id()]);
            $service->changeStatus($equipment, 'working', null, Auth::user());
        }

        session()->flash('success', "{$data['name']} ({$data['code']}) saved.");
        $this->formOpen = false;
    }

    /* ---------- Status ---------- */

    public function openStatus(string $equipmentId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $equipment = Equipment::findOrFail($equipmentId);
        $this->statusEquipmentId = $equipment->id;
        $this->newStatus = $equipment->status;
        $this->statusNote = '';
    }

    public function closeStatus(): void
    {
        $this->reset(['statusEquipmentId', 'statusNote']);
    }

    public function saveStatus(InventoryService $service): void
    {
        $this->authorizeManage();
        $this->validate(['newStatus' => ['required', Rule::in(array_keys(Lookups::EQUIPMENT_STATUSES))]]);

        $equipment = Equipment::findOrFail($this->statusEquipmentId);

        if ($this->attempt(fn () => $service->changeStatus($equipment, $this->newStatus, $this->statusNote ?: null, Auth::user()), "{$equipment->name} is now ".strtolower(Lookups::EQUIPMENT_STATUSES[$this->newStatus]).'.')) {
            $this->closeStatus();
        }
    }

    /* ---------- Removal ---------- */

    public function openRemove(string $equipmentId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $this->removeEquipmentId = $equipmentId;
        $this->removalReason = '';
        $this->removalApprovedBy = '';
    }

    public function closeRemove(): void
    {
        $this->reset(['removeEquipmentId', 'removalReason', 'removalApprovedBy']);
    }

    public function remove(InventoryService $service): void
    {
        // De-commissioning needs the head of engineering, not just an inventory editor
        abort_unless(EngineeringAccess::isManager(Auth::user()), 403);

        $this->validate([
            'removalReason' => ['required', 'string', 'min:5', 'max:200'],
            'removalApprovedBy' => ['required', 'string', 'max:150'],
        ], [], ['removalReason' => 'reason', 'removalApprovedBy' => 'approving authority']);

        $equipment = Equipment::findOrFail($this->removeEquipmentId);

        if ($this->attempt(fn () => $service->remove($equipment, $this->removalReason, $this->removalApprovedBy, Auth::user()), "{$equipment->name} removed from the active inventory.")) {
            $this->closeRemove();
        }
    }

    protected function authorizeManage(): void
    {
        abort_unless(EngineeringAccess::canManageInventory(Auth::user()), 403);
    }

    protected function query(): Builder
    {
        $query = Equipment::query()
            ->with(['room:id,name,floor,block', 'department:id,name', 'section:id,name', 'ppmSchedules:id,equipment_id,next_due_at,status'])
            ->search($this->search)
            ->when($this->filterSection, fn (Builder $q, $id) => $q->where('section_id', $id))
            ->when($this->filterDepartment, fn (Builder $q, $id) => $q->where('department_id', $id))
            ->when($this->filterRoom, fn (Builder $q, $id) => $q->where('room_id', $id))
            ->when($this->filterStatus, fn (Builder $q, $status) => $q->where('status', $status));

        return match ($this->view) {
            'removed' => $query->whereNotNull('removed_at')->latest('removed_at'),
            'flagged' => $query->inService()->where('flagged_for_removal', true)->orderBy('name'),
            'replacement' => $query->inService()->whereNotNull('expected_life_years')->orderBy('installation_date'),
            default => $query->inService()->orderBy('name'),
        };
    }

    /** The replacement date is derived, so that view is narrowed after loading. */
    protected function results()
    {
        if ($this->view !== 'replacement') {
            return $this->query()->paginate(15);
        }

        return $this->query()->get()->filter->isDueForReplacement()->values();
    }

    public function export()
    {
        $items = $this->view === 'replacement' ? $this->results() : $this->query()->get();

        return $this->exportTable(
            'Equipment inventory - '.self::VIEWS[$this->view],
            ['S/N', 'Code', 'Equipment', 'Section', 'Serial number', 'Model', 'Manufacturer', 'Manufacturer contact', 'Department', 'Room', 'Quantity', 'Installed', 'Condition received', 'Predicted replacement', 'Status', 'Status note', 'Manual location', 'Counted by', 'Counted on', 'Removed on', 'Removal reason', 'Removal approved by'],
            $items->values()->map(fn (Equipment $e, int $i) => [
                $i + 1, $e->code, $e->name, $e->section->name, $e->serial_number, $e->model, $e->manufacturer, $e->manufacturerContact(),
                $e->department->name, $e->room->label(), $e->quantity, Eng::date($e->installation_date), strtoupper($e->condition_received),
                Eng::date($e->replacementDate()), Lookups::EQUIPMENT_STATUSES[$e->status], $e->status_note, $e->manual_location,
                $e->countedBy?->full_name, Eng::date($e->counted_at), Eng::dt($e->removed_at), $e->removal_reason, $e->removal_approved_by,
            ]),
            ['Search' => $this->search, 'Status' => Lookups::label(Lookups::EQUIPMENT_STATUSES, $this->filterStatus ?: null, '')],
        );
    }

    public function render()
    {
        $user = Auth::user();

        // Uptime of what the filters select: working units over all units
        $units = (clone $this->query())->reorder()->when($this->view !== 'removed', fn (Builder $q) => $q->whereNull('removed_at'))
            ->selectRaw('status, sum(quantity) as units')->groupBy('status')->pluck('units', 'status');

        return view('livewire.engineering.inventory', [
            'equipment' => $this->results(),
            'paginated' => $this->view !== 'replacement',
            'working' => (int) ($units['working'] ?? 0),
            'total' => (int) $units->sum(),
            'uptimeTarget' => config('engineering.kpi.targets.uptime'),
            'sections' => Section::active()->get(),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'filterRooms' => $this->filterDepartment ? Room::where('department_id', $this->filterDepartment)->orderBy('name')->get() : collect(),
            'formRooms' => $this->formOpen && $this->form['department_id'] ? Room::where('department_id', $this->form['department_id'])->where('status', 'active')->orderBy('name')->get() : collect(),
            'canManage' => EngineeringAccess::canManageInventory($user),
            'canRemove' => EngineeringAccess::isManager($user),
            'detail' => $this->detailId ? Equipment::with(['room', 'department', 'section', 'countedBy', 'statusLogs.changedBy', 'ppmSchedules.responsible'])->find($this->detailId) : null,
            'statusEquipment' => $this->statusEquipmentId ? Equipment::find($this->statusEquipmentId) : null,
            'removeEquipment' => $this->removeEquipmentId ? Equipment::find($this->removeEquipmentId) : null,
        ]);
    }
}
