<?php

namespace App\Livewire\Engineering;

use App\Livewire\Engineering\Concerns\InteractsWithEngineering;
use App\Models\departments;
use App\Models\Employee;
use App\Models\Engineering\MaintenanceJob;
use App\Models\Engineering\Room;
use App\Models\Engineering\RoomStaff;
use App\Services\Engineering\EngineeringAccess;
use App\Services\Engineering\InventoryService;
use App\Support\Engineering\Eng;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Room register per department, and which engineer is responsible for each room or department.
 */
class InventoryRooms extends Component
{
    use InteractsWithEngineering;

    #[Url]
    public string $departmentId = '';

    // Room add / edit
    public bool $roomFormOpen = false;

    public ?string $editingRoomId = null;

    /** @var array{name:string, code:string, floor:string, block:string} */
    public array $room = ['name' => '', 'code' => '', 'floor' => '', 'block' => ''];

    // Responsibility: room id, or "department" for the whole department
    public ?string $assignTarget = null;

    public string $assignEmployeeId = '';

    public function mount(): void
    {
        $this->departmentId = $this->departmentId ?: (string) departments::orderBy('name')->value('id');
    }

    protected function authorizeManage(): void
    {
        abort_unless(EngineeringAccess::canManageInventory(Auth::user()), 403);
    }

    /* ---------- Rooms ---------- */

    public function createRoom(): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $this->editingRoomId = null;
        $this->room = ['name' => '', 'code' => '', 'floor' => '', 'block' => ''];
        $this->roomFormOpen = true;
    }

    public function editRoom(string $roomId): void
    {
        $this->authorizeManage();
        $this->resetErrorBag();

        $room = Room::findOrFail($roomId);
        $this->editingRoomId = $room->id;
        $this->room = ['name' => $room->name, 'code' => $room->code, 'floor' => (string) $room->floor, 'block' => (string) $room->block];
        $this->roomFormOpen = true;
    }

    public function closeRoomForm(): void
    {
        $this->roomFormOpen = false;
    }

    public function saveRoom(): void
    {
        $this->authorizeManage();

        $this->validate([
            'departmentId' => ['required', Rule::exists('departments', 'id')],
            'room.name' => ['required', 'string', 'max:120'],
            'room.code' => ['required', 'string', 'max:30', Rule::unique('eng_rooms', 'code')->where('department_id', $this->departmentId)->ignore($this->editingRoomId)],
            'room.floor' => ['nullable', 'string', 'max:60'],
            'room.block' => ['nullable', 'string', 'max:80'],
        ], [], ['room.name' => 'room name', 'room.code' => 'room code']);

        $data = array_merge($this->room, ['code' => strtoupper($this->room['code']), 'department_id' => $this->departmentId]);

        $this->editingRoomId ? Room::findOrFail($this->editingRoomId)->update($data) : Room::create($data);

        session()->flash('success', "Room {$data['name']} saved.");
        $this->roomFormOpen = false;
    }

    /** A room in use is deactivated, never deleted. */
    public function toggleRoom(string $roomId): void
    {
        $this->authorizeManage();

        $room = Room::findOrFail($roomId);

        if ($room->status === 'active') {
            $inUse = $room->equipment()->inService()->exists()
                || MaintenanceJob::open()->whereHas('requisition', fn (Builder $q) => $q->where('room_id', $room->id))->exists();

            if ($inUse) {
                session()->flash('error', "{$room->name} still has equipment or open requisitions and cannot be deactivated.");

                return;
            }
        }

        $room->update(['status' => $room->status === 'active' ? 'inactive' : 'active']);
    }

    /* ---------- Responsible staff ---------- */

    public function openAssign(string $target): void
    {
        // Assigning staff to rooms is for the HOD or a head of section
        abort_unless(EngineeringAccess::isManager(Auth::user()) || EngineeringAccess::isHeadOfSection(Auth::user()), 403);

        $this->resetErrorBag();
        $this->assignTarget = $target;
        $this->assignEmployeeId = '';
    }

    public function closeAssign(): void
    {
        $this->reset(['assignTarget', 'assignEmployeeId']);
    }

    public function assign(InventoryService $service): void
    {
        abort_unless(EngineeringAccess::isManager(Auth::user()) || EngineeringAccess::isHeadOfSection(Auth::user()), 403);

        $this->validate(['assignEmployeeId' => ['required', Rule::exists('employees', 'id')]], ['assignEmployeeId.required' => 'Select the staff member.']);

        $service->assignResponsibility($this->assignEmployeeId, $this->departmentId, $this->assignTarget === 'department' ? null : $this->assignTarget, Auth::user());

        session()->flash('success', 'Responsibility assigned. The previous assignment is kept in the history.');
        $this->closeAssign();
    }

    public function export()
    {
        return $this->exportTable(
            'Room responsibility history',
            ['Department', 'Room', 'Staff', 'From', 'To', 'Assigned by'],
            RoomStaff::with(['department:id,name', 'room:id,name', 'employee:id,first_name,last_name', 'assignedBy:id,first_name,surname'])
                ->where('department_id', $this->departmentId)->latest('effective_from')->get()
                ->map(fn (RoomStaff $row) => [
                    $row->department->name, $row->room?->name ?? 'Whole department', Eng::name($row->employee),
                    Eng::dt($row->effective_from), $row->effective_to ? Eng::dt($row->effective_to) : 'Current', $row->assignedBy?->full_name,
                ]),
            ['Department' => departments::find($this->departmentId)?->name],
        );
    }

    public function render()
    {
        $user = Auth::user();
        $assignments = RoomStaff::with(['employee:id,first_name,last_name', 'assignedBy:id,first_name,surname', 'room:id,name'])
            ->where('department_id', $this->departmentId)->latest('effective_from')->get();

        return view('livewire.engineering.inventory-rooms', [
            'departments' => departments::orderBy('name')->get(['id', 'name']),
            'rooms' => Room::where('department_id', $this->departmentId)->withCount(['equipment' => fn (Builder $q) => $q->whereNull('removed_at')])->orderBy('name')->get(),
            'current' => $assignments->whereNull('effective_to')->keyBy(fn (RoomStaff $row) => $row->room_id ?? 'department'),
            'history' => $assignments->whereNotNull('effective_to'),
            'canManage' => EngineeringAccess::canManageInventory($user),
            'canAssign' => EngineeringAccess::isManager($user) || EngineeringAccess::isHeadOfSection($user),
            // Engineering staff = anyone who belongs to a section
            'engineers' => $this->assignTarget
                ? Employee::whereIn('id', DB::table('eng_section_staff')->select('employee_id'))->where('status', 'Active')->orderBy('first_name')->get(['id', 'first_name', 'last_name'])
                : collect(),
        ]);
    }
}
