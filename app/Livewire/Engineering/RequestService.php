<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Engineering\Equipment;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\ProblemCategory;
use App\Models\Engineering\Requisition;
use App\Models\Engineering\Room;
use App\Models\Engineering\Section;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\RequisitionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Raise an MRV: section -> nature of work -> category -> up to five items -> submit.
 */
class RequestService extends Component
{
    use InteractsWithEngineering;

    public ?string $sectionId = null;

    // null until the user picks routine or emergency
    public ?bool $emergency = null;

    public ?string $categoryId = null;

    public string $justification = '';

    /** @var array<int, array{equipment_id:?string, equipment_label:string, problem_description:string, warning:?string}> */
    public array $items = [];

    // The item being written
    public string $equipmentSearch = '';

    public ?string $draftEquipmentId = null;

    public string $draftDescription = '';

    // Requester details, pre-filled from the profile
    public string $phone = '';

    public ?string $departmentId = null;

    public ?string $roomId = null;

    public string $locationText = '';

    public ?string $submittedId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $employee = EngineeringAccess::employee($user);

        $this->phone = (string) ($employee?->phone ?? $user->phone_number);
        $this->departmentId = $employee?->department_id;
    }

    public function selectSection(string $sectionId): void
    {
        if ($this->sectionId !== $sectionId) {
            $this->sectionId = $sectionId;
            $this->reset(['categoryId', 'emergency', 'justification']);
        }
    }

    /** A category on the emergency list cannot be downgraded by the requester. */
    public function setEmergency(bool $emergency): void
    {
        $this->emergency = $this->autoEmergency ? true : $emergency;
    }

    public function updatedCategoryId(): void
    {
        unset($this->autoEmergency);

        if ($this->autoEmergency) {
            $this->emergency = true;
        }
    }

    public function updatedDepartmentId(): void
    {
        $this->roomId = null;
    }

    public function pickEquipment(string $equipmentId): void
    {
        $equipment = Equipment::inService()->find($equipmentId);

        if ($equipment) {
            $this->draftEquipmentId = $equipment->id;
            $this->equipmentSearch = "{$equipment->name} ({$equipment->code})";
        }
    }

    public function clearEquipment(): void
    {
        $this->reset(['draftEquipmentId', 'equipmentSearch']);
    }

    public function saveItem(): void
    {
        $max = config('engineering.max_items_per_requisition');

        if (count($this->items) >= $max) {
            $this->addError('draftDescription', "A requisition can carry at most {$max} items. Submit this one and raise another.");

            return;
        }

        $this->validate([
            'equipmentSearch' => ['required', 'string', 'max:200'],
            'draftDescription' => ['required', 'string', 'min:10', 'max:2000'],
        ], [], ['equipmentSearch' => 'equipment or area', 'draftDescription' => 'problem description']);

        $this->items[] = [
            'equipment_id' => $this->draftEquipmentId,
            'equipment_label' => $this->equipmentSearch,
            'problem_description' => trim($this->draftDescription),
            'warning' => $this->openRequisitionWarning($this->draftEquipmentId),
        ];

        $this->reset(['draftEquipmentId', 'equipmentSearch', 'draftDescription']);
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function submit(RequisitionService $service): void
    {
        $this->validate([
            'sectionId' => ['required'],
            'emergency' => ['required', 'boolean'],
            'categoryId' => ['required'],
            'items' => ['required', 'array', 'min:1'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ]{9,15}$/'],
            'locationText' => ['required_without:roomId', 'nullable', 'string', 'max:200'],
        ], [
            'sectionId.required' => 'Select the responsible section.',
            'emergency.required' => 'Choose routine or emergency work.',
            'categoryId.required' => 'Select the problem category.',
            'items.required' => 'Save at least one item before submitting.',
            'locationText.required_without' => 'Pick a room or describe the place.',
        ]);

        $this->attempt(function () use ($service) {
            $requisition = $service->submit(Auth::user(), [
                'section_id' => $this->sectionId,
                'category_id' => $this->categoryId,
                'emergency' => (bool) $this->emergency,
                'justification' => $this->justification,
                'phone' => $this->phone,
                'department_id' => $this->departmentId,
                'room_id' => $this->roomId,
                'location_text' => $this->locationText,
            ], array_map(fn (array $item) => [
                'equipment_id' => $item['equipment_id'],
                'equipment_text' => $item['equipment_label'],
                'problem_description' => $item['problem_description'],
            ], $this->items));

            $this->submittedId = $requisition->id;
        });
    }

    public function startAnother(): void
    {
        $this->reset(['sectionId', 'emergency', 'categoryId', 'justification', 'items', 'submittedId', 'roomId', 'locationText']);
    }

    #[Computed]
    public function autoEmergency(): bool
    {
        return $this->categoryId !== null && (ProblemCategory::find($this->categoryId)?->isEmergency() ?? false);
    }

    /** A user escalating a routine category must say why. */
    #[Computed]
    public function needsJustification(): bool
    {
        return $this->emergency === true && $this->categoryId !== null && ! $this->autoEmergency;
    }

    protected function openRequisitionWarning(?string $equipmentId): ?string
    {
        $open = $equipmentId ? MaintenanceJob::open()->where('equipment_id', $equipmentId)->first() : null;

        return $open ? "This equipment already has an open requisition {$open->number}." : null;
    }

    public function render()
    {
        $search = trim($this->equipmentSearch);

        return view('livewire.engineering.request-service', [
            'sections' => Section::active()->get(),
            'categories' => $this->sectionId
                ? ProblemCategory::where('section_id', $this->sectionId)->where('status', 'active')->orderBy('name')->get()
                : collect(),
            'matches' => $this->draftEquipmentId === null && mb_strlen($search) >= 2
                ? Equipment::inService()->search($search)->with('room:id,name')->orderBy('name')->limit(6)->get()
                : collect(),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'rooms' => $this->departmentId
                ? Room::where('department_id', $this->departmentId)->where('status', 'active')->orderBy('name')->get()
                : collect(),
            'submitted' => $this->submittedId ? Requisition::with('jobs')->find($this->submittedId) : null,
            'maxItems' => config('engineering.max_items_per_requisition'),
        ]);
    }
}
