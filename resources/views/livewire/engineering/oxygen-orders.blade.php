@php use App\Support\Engineering\Eng; @endphp

<x-engineering.page title="Order medical oxygen" subtitle="Order cylinders for your ward and follow the delivery">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-lungs me-2"></i>New order</h6></div>
                <form wire:submit="order" class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Cylinder size / type <span class="text-danger">*</span></label>
                        <select class="form-select @error('cylinderType') is-invalid @enderror" wire:model="cylinderType">
                            <option value="">Select</option>
                            @foreach($cylinderTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('cylinderType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity">
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ward / department <span class="text-danger">*</span></label>
                        <select class="form-select @error('departmentId') is-invalid @enderror" wire:model.live="departmentId">
                            <option value="">Select</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('departmentId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Room</label>
                        <select class="form-select" wire:model="roomId" @disabled($rooms->isEmpty())>
                            <option value="">{{ $rooms->isEmpty() ? 'No rooms registered' : 'Select room' }}</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Place / bed</label>
                        <input type="text" class="form-control @error('locationText') is-invalid @enderror" wire:model="locationText">
                        @error('locationText') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact phone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" wire:model="phone">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="oxygen-emergency" wire:model.live="emergency">
                        <label class="form-check-label" for="oxygen-emergency">Emergency: the cylinder in use is empty or about to run out</label>
                    </div>
                    <button type="submit" class="btn w-100 {{ $emergency ? 'btn-danger' : 'btn-primary' }}" wire:loading.attr="disabled" wire:target="order">
                        <i class="fa-solid fa-paper-plane me-1"></i>Place order
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">My orders</h6></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Order</th><th>Cylinders</th><th>Deliver to</th><th>Status</th><th>Delivery</th></tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $job)
                                <tr wire:key="order-{{ $job->id }}">
                                    <td class="text-nowrap">
                                        <a href="{{ route('engineering.jobs.summary', $job) }}" class="fw-semibold text-decoration-none">{{ $job->requisition->number }}</a>
                                        <div class="small text-muted">{{ Eng::dt($job->requisition->requested_at) }}</div>
                                    </td>
                                    <td>{{ $job->oxygenOrder->qty_ordered }} × {{ $job->oxygenOrder->cylinder_type }}</td>
                                    <td>{{ $job->requisition->locationLabel() }}<div class="small text-muted">{{ $job->requisition->department?->name }}</div></td>
                                    <td><x-engineering.job-badges :job="$job" /></td>
                                    <td>
                                        @if($job->oxygenOrder->isDelivered())
                                            {{ $job->oxygenOrder->qty_delivered }} delivered by {{ Eng::name($job->oxygenOrder->deliveredBy) }}
                                            <div class="small text-muted">{{ Eng::dt($job->oxygenOrder->delivered_at) }} · received by {{ $job->oxygenOrder->received_by }}</div>
                                        @elseif($job->engineer)
                                            <span class="text-muted">Assigned to {{ Eng::name($job->engineer) }}</span>
                                        @else
                                            <span class="text-muted">Waiting for assignment</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <x-engineering.empty colspan="5" icon="fa-lungs" text="You have not ordered any cylinders yet." />
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($orders->hasPages())
                    <div class="card-footer">{{ $orders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-engineering.page>
