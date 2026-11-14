@php use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="Rooms & responsible staff" subtitle="The room register, and who looks after the equipment in each room">
    <x-slot:actions>
        <a href="{{ route('engineering.inventory') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Inventory</a>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export history</button>
        @if($canManage)
            <button type="button" class="btn btn-primary" wire:click="createRoom"><i class="fa-solid fa-plus me-1"></i>Add room</button>
        @endif
    </x-slot:actions>

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <div style="min-width: 280px;">
                <label class="form-label small">Department</label>
                <select class="form-select" wire:model.live="departmentId">
                    @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                </select>
            </div>
            <div class="ms-md-auto">
                <div class="text-muted small">Responsible for the whole department</div>
                <span class="fw-semibold">{{ isset($current['department']) ? Eng::name($current['department']->employee) : 'Nobody assigned' }}</span>
                @if($canAssign)<button type="button" class="btn btn-sm btn-outline-primary ms-2" wire:click="openAssign('department')">Assign</button>@endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Room</th><th>Code</th><th>Floor</th><th>Block / building</th><th class="text-center">Equipment</th><th>Responsible staff</th><th>Status</th><th class="text-end">Action</th></tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                        @php $assignment = $current[$room->id] ?? null; @endphp
                        <tr wire:key="room-{{ $room->id }}">
                            <td class="fw-semibold">{{ $room->name }}</td>
                            <td>{{ $room->code }}</td>
                            <td>{{ $room->floor ?? '—' }}</td>
                            <td>{{ $room->block ?? '—' }}</td>
                            <td class="text-center">{{ $room->equipment_count }}</td>
                            <td>
                                @if($assignment)
                                    {{ Eng::name($assignment->employee) }}
                                    <div class="small text-muted">since {{ Eng::dt($assignment->effective_from, 'd M Y') }} · by {{ $assignment->assignedBy?->full_name }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $room->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($room->status) }}</span></td>
                            <td class="text-end text-nowrap">
                                @if($canAssign)<button type="button" class="btn btn-sm btn-outline-primary" wire:click="openAssign('{{ $room->id }}')">Assign staff</button>@endif
                                @if($canManage)
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="editRoom('{{ $room->id }}')" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggleRoom('{{ $room->id }}')" title="{{ $room->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="fa-solid {{ $room->status === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="8" icon="fa-door-open" text="No rooms registered for this department yet." />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($history->isNotEmpty())
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Previous assignments</h6></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Room</th><th>Staff</th><th>From</th><th>To</th><th>Assigned by</th></tr></thead>
                    <tbody>
                        @foreach($history as $row)
                            <tr>
                                <td>{{ $row->room?->name ?? 'Whole department' }}</td>
                                <td>{{ Eng::name($row->employee) }}</td>
                                <td>{{ Eng::dt($row->effective_from, 'd M Y') }}</td>
                                <td>{{ Eng::dt($row->effective_to, 'd M Y') }}</td>
                                <td>{{ $row->assignedBy?->full_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($roomFormOpen)
        <x-engineering.modal :title="$editingRoomId ? 'Edit room' : 'Add room'" close="closeRoomForm">
            <form wire:submit="saveRoom">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Room name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('room.name') is-invalid @enderror" wire:model="room.name">
                            @error('room.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase @error('room.code') is-invalid @enderror" wire:model="room.code" placeholder="LAB-12">
                            @error('room.code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Floor</label>
                            <input type="text" class="form-control" wire:model="room.floor" placeholder="1st floor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Block / building</label>
                            <input type="text" class="form-control" wire:model="room.block" placeholder="Block A">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeRoomForm">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save room</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif

    @if($assignTarget)
        <x-engineering.modal title="Assign responsible staff" close="closeAssign">
            <form wire:submit="assign">
                <div class="modal-body">
                    <p class="text-muted">The new assignment replaces the current one. Previous assignments stay in the history.</p>
                    <select class="form-select @error('assignEmployeeId') is-invalid @enderror" wire:model="assignEmployeeId">
                        <option value="">Select engineering staff</option>
                        @foreach($engineers as $engineer)<option value="{{ $engineer->id }}">{{ $engineer->first_name }} {{ $engineer->last_name }}</option>@endforeach
                    </select>
                    @error('assignEmployeeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeAssign">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</x-engineering.page>
