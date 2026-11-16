<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\asset;
use App\Models\assetclass;
use App\Models\assetregistry;
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
use Illuminate\Support\Facades\DB;
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

    /** Engineering profile fields (stored on eng_equipment). */
    protected const PROFILE_FIELDS = [
        'name', 'section_id', 'code', 'manufacturer', 'manufacturer_email', 'manufacturer_phone', 'manufacturer_postal',
        'under_contract', 'contract_expiry', 'installation_date', 'condition_received', 'expected_life_years',
        'manual_location', 'counted_at',
    ];

    /** Identity fields stored on the asset register row; editable here, location is not. */
    protected const REGISTRY_FIELDS = ['serial_number', 'model', 'vendor', 'purchase_cost'];

    protected const BLANK_FORM = [
        'name' => '', 'section_id' => '', 'code' => '', 'serial_number' => '', 'model' => '',
        'manufacturer' => '', 'manufacturer_email' => '', 'manufacturer_phone' => '', 'manufacturer_postal' => '',
        'vendor' => '', 'under_contract' => false, 'contract_expiry' => null,
        'department_id' => '', 'room_id' => '', 'quantity' => 1, 'installation_date' => null,
        'condition_received' => 'new', 'expected_life_years' => null, 'manual_location' => '',
        'purchase_cost' => null, 'counted_at' => null,
        // New item: asset class and catalogue item (or a new catalogue name)
        'asset_class_id' => '', 'asset_id' => '', 'new_asset_name' => '',
        // From the register: the existing row
        'asset_registry_id' => '',
    ];

    /** 'new' = create the register row too, 'register' = pick an existing register row. */
    public string $source = 'new';

    public string $registrySearch = '';

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

    public function updatedFormAssetClassId(): void
    {
        $this->form['asset_id'] = '';
        $this->form['section_id'] = (string) (assetclass::find($this->form['asset_class_id'])?->eng_section_id ?? $this->form['section_id']);
    }

    public function updatedFormAssetId(): void
    {
        if ($this->form['asset_id'] && blank($this->form['name'])) {
            $this->form['name'] = (string) asset::find($this->form['asset_id'])?->name;
        }
    }

    public function setSource(string $source): void
    {
        $this->source = $source === 'register' ? 'register' : 'new';
        $this->form = array_merge(self::BLANK_FORM, ['counted_at' => $this->form['counted_at'] ?? Eng::now()->toDateString()]);
        $this->registrySearch = '';
        $this->resetErrorBag();
    }

    /** Picks an existing register row and pre-fills what the register already knows. */
    public function pickRegistry(string $registryId): void
    {
        $this->authorizeManage();

        $row = $this->registryCandidates()->firstWhere('id', $registryId) ?? abort(404);

        $this->form = array_merge($this->form, [
            'asset_registry_id' => $row->id,
            'name' => $this->form['name'] ?: (string) $row->asset?->name,
            'section_id' => $this->form['section_id'] ?: (string) $row->assetClass?->eng_section_id,
            'code' => $this->form['code'] ?: (string) $row->codeno,
            'installation_date' => $this->form['installation_date'] ?: $row->purchase_date?->toDateString(),
        ]);
        $this->registrySearch = '';
    }

    public function clearRegistry(): void
    {
        $this->form['asset_registry_id'] = '';
    }

    /** Active register rows of engineering-maintained classes that have no engineering profile yet. */
    protected function registryCandidates()
    {
        return assetregistry::query()
            ->with(['asset:id,name', 'assetClass:id,name,eng_section_id', 'department:id,name', 'facilityLocation.building:id,name'])
            ->whereHas('assetClass', fn (Builder $q) => $q->whereNotNull('eng_section_id'))
            ->where('status', '!=', 'disposed')
            ->whereDoesntHave('equipment')
            ->when(trim($this->registrySearch), fn (Builder $q, $term) => $q->where(fn (Builder $w) => $w
                ->where('codeno', 'like', "%{$term}%")
                ->orWhere('serial_number', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhereHas('asset', fn (Builder $a) => $a->where('name', 'like', "%{$term}%"))))
            ->latest()
            ->limit(15)
            ->get();
    }

    /** Opens the shared asset transfer form; location changes only go through an approved transfer. */
    public function requestTransfer(string $equipmentId): void
    {
        $this->authorizeManage();

        $equipment = Equipment::findOrFail($equipmentId);
        $this->formOpen = false;
        $this->dispatch('open-asset-transfer', registryId: $equipment->asset_registry_id);
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
        $this->source = 'new';
        $this->registrySearch = '';
        $this->form = array_merge(self::BLANK_FORM, ['counted_at' => Eng::now()->toDateString()]);
        $this->codePrefix = '';
        $this->formOpen = true;
    }

    public function edit(string $equipmentId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $equipment = Equipment::with('assetRegistry')->findOrFail($equipmentId);
        $registry = $equipment->assetRegistry;

        $this->editingId = $equipment->id;
        $this->form = array_merge(self::BLANK_FORM, $equipment->only([...self::PROFILE_FIELDS, 'department_id', 'room_id', 'quantity', 'asset_registry_id']), [
            'serial_number' => (string) $registry?->serial_number,
            'model' => (string) $registry?->model,
            'vendor' => (string) $registry?->vendor,
            'purchase_cost' => $registry?->purchase_cost,
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

        $creatingNew = ! $this->editingId && $this->source === 'new';
        $registryId = $this->editingId ? Equipment::findOrFail($this->editingId)->asset_registry_id : ($this->form['asset_registry_id'] ?: null);

        $rules = [
            'form.name' => ['required', 'string', 'max:150'],
            'form.section_id' => ['required', Rule::exists('eng_sections', 'id')],
            'form.code' => ['required', 'string', 'max:60', Rule::unique('eng_equipment', 'code')->ignore($this->editingId)],
            'form.serial_number' => ['required', 'string', 'max:100', Rule::unique('assetregistries', 'serial_number')->ignore($registryId)],
            'form.model' => ['nullable', 'string', 'max:100'],
            'form.vendor' => ['nullable', 'string', 'max:150'],
            'form.manufacturer' => ['required', 'string', 'max:150'],
            'form.manufacturer_email' => ['nullable', 'email', 'max:150'],
            'form.installation_date' => ['required', 'date', 'before_or_equal:today'],
            'form.condition_received' => ['required', Rule::in(['new', 'old'])],
            'form.expected_life_years' => ['nullable', 'integer', 'min:1', 'max:100'],
            'form.manual_location' => ['required', 'string', 'max:200'],
            'form.contract_expiry' => ['nullable', 'date'],
            'form.purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'form.counted_at' => ['required', 'date', 'before_or_equal:today'],
        ];

        if ($creatingNew) {
            $rules += [
                'form.asset_class_id' => ['required', Rule::exists('assetclasses', 'id')->whereNotNull('eng_section_id')->whereNull('deleted_at')],
                'form.asset_id' => ['required_without:form.new_asset_name', 'nullable', Rule::exists('assets', 'id')->where('asset_class_id', $this->form['asset_class_id'])],
                'form.new_asset_name' => ['required_without:form.asset_id', 'nullable', 'string', 'max:255'],
                'form.department_id' => ['required', Rule::exists('departments', 'id')],
                'form.room_id' => ['required', Rule::exists('facilitylocations', 'id')->where('department_id', $this->form['department_id'])->whereNull('deleted_at')],
                'form.quantity' => ['required', 'integer', 'min:1'],
            ];
        } elseif (! $this->editingId) {
            $rules['form.asset_registry_id'] = ['required', Rule::exists('assetregistries', 'id')->whereNull('deleted_at')];
        }

        $this->validate($rules, [
            'form.asset_registry_id.required' => 'Pick the item from the asset register.',
            'form.asset_id.required_without' => 'Pick the item from the catalogue or type a new item name.',
            'form.new_asset_name.required_without' => 'Pick the item from the catalogue or type a new item name.',
        ], collect(array_keys(self::BLANK_FORM))->mapWithKeys(fn (string $field) => ["form.{$field}" => str_replace(['_id', '_'], ['', ' '], $field)])->all());

        $data = array_map(fn ($value) => $value === '' ? null : $value, $this->form);
        $data['code'] = strtoupper($data['code']);

        $profile = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
        $profile['under_contract'] = (bool) $profile['under_contract'];
        $registryFields = array_intersect_key($data, array_flip(self::REGISTRY_FIELDS));

        $saved = $this->attempt(function () use ($service, $creatingNew, $data, $profile, $registryFields, $registryId) {
            DB::transaction(function () use ($service, $creatingNew, $data, $profile, $registryFields, $registryId) {
                if ($this->editingId) {
                    $equipment = Equipment::findOrFail($this->editingId);
                    $equipment->update($profile);
                    $equipment->assetRegistry()->first()?->update($registryFields);

                    return;
                }

                if ($creatingNew) {
                    $service->createWithRegistry($data['room_id'], $registryFields + [
                        'asset_class_id' => $data['asset_class_id'],
                        'asset_id' => $data['asset_id'] ?: $this->catalogueItem($data['asset_class_id'], $data['new_asset_name'])->id,
                        'quantity' => (int) $data['quantity'],
                        'purchase_date' => $data['installation_date'],
                    ], $profile, Auth::user());

                    return;
                }

                $registry = assetregistry::findOrFail($registryId);
                $registry->update(array_filter($registryFields, fn ($value) => $value !== null));
                $service->linkRegistry($registry, $profile, Auth::user());
            });
        }, "{$data['name']} ({$data['code']}) saved.");

        if ($saved) {
            $this->formOpen = false;
        }
    }

    /** Finds or creates the catalogue item (assets table) for a newly typed name. */
    protected function catalogueItem(string $classId, string $name): asset
    {
        $name = trim($name);

        return asset::where('asset_class_id', $classId)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
            ?? asset::create(['name' => $name, 'type' => 'physical', 'asset_class_id' => $classId, 'added_by' => Auth::id()]);
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
            ->with(['room:id,name,floor,building_id', 'assetRegistry:id,serial_number,model,vendor,vendor_id,purchase_cost,depreciation', 'department:id,name', 'section:id,name', 'ppmSchedules:id,equipment_id,next_due_at,status'])
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
            'formDepartments' => $this->formOpen ? departments::active()->orderBy('name')->get(['id', 'name']) : collect(),
            'filterRooms' => $this->filterDepartment ? Room::where('department_id', $this->filterDepartment)->orderBy('name')->get() : collect(),
            'formRooms' => $this->formOpen && $this->form['department_id'] ? Room::active()->forDepartment($this->form['department_id'], false)->orderBy('name')->get() : collect(),
            'engineeringClasses' => $this->formOpen && $this->source === 'new' && ! $this->editingId ? assetclass::active()->maintainedByEngineering()->with('engSection:id,name')->orderBy('name')->get() : collect(),
            'catalogue' => $this->formOpen && $this->form['asset_class_id'] ? asset::active()->where('asset_class_id', $this->form['asset_class_id'])->orderBy('name')->get(['id', 'name']) : collect(),
            'registryCandidates' => $this->formOpen && $this->source === 'register' && ! $this->editingId && ! $this->form['asset_registry_id'] ? $this->registryCandidates() : collect(),
            'editingEquipment' => $this->formOpen && $this->editingId ? Equipment::with(['room', 'department', 'assetRegistry.pendingTransfer'])->find($this->editingId) : null,
            'pickedRegistry' => $this->formOpen && $this->form['asset_registry_id'] ? assetregistry::with(['asset:id,name', 'department:id,name', 'facilityLocation.building:id,name', 'pendingTransfer'])->find($this->form['asset_registry_id']) : null,
            'canManage' => EngineeringAccess::canManageInventory($user),
            'canRemove' => EngineeringAccess::isManager($user),
            'detail' => $this->detailId ? Equipment::with(['room', 'department', 'section', 'countedBy', 'statusLogs.changedBy', 'ppmSchedules.responsible', 'assetRegistry.vendorRelation', 'assetRegistry.pendingTransfer.toDepartment', 'assetRegistry.pendingTransfer.toLocation'])->find($this->detailId) : null,
            'statusEquipment' => $this->statusEquipmentId ? Equipment::find($this->statusEquipmentId) : null,
            'removeEquipment' => $this->removeEquipmentId ? Equipment::find($this->removeEquipmentId) : null,
        ]);
    }
}
