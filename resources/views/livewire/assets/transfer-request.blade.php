<div>
    @if($notice)
        <div class="alert alert-success alert-dismissible fade show position-fixed bottom-0 end-0 m-3 shadow" style="z-index: 1080; max-width: 420px;" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>{{ $notice }}
            <button type="button" class="btn-close" wire:click="$set('notice', null)" aria-label="Close"></button>
        </div>
    @endif

    @if($registry)
        <x-engineering.modal :title="'Transfer '.$registry->label()" close="close">
            <form wire:submit="submit">
                <div class="modal-body">
                    <div class="border rounded p-2 bg-light small mb-3">
                        <div class="text-muted">Currently</div>
                        <strong>{{ $registry->department?->name ?? '—' }}</strong> · {{ $registry->facilityLocation?->label() ?? '—' }} · Qty {{ $registry->quantity }}
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiving department <span class="text-danger">*</span></label>
                        <select class="form-select @error('toDepartmentId') is-invalid @enderror" wire:model.live="toDepartmentId">
                            <option value="">Select</option>
                            @foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach
                        </select>
                        @error('toDepartmentId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Receiving room / location <span class="text-danger">*</span></label>
                        <select class="form-select @error('toLocationId') is-invalid @enderror" wire:model="toLocationId" @disabled(! $toDepartmentId)>
                            <option value="">{{ $toDepartmentId && $locations->isEmpty() ? 'No active room for this department' : 'Select' }}</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->label() }}{{ $location->department_id ? '' : ' (no department)' }}</option>
                            @endforeach
                        </select>
                        @error('toLocationId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if($registry->quantity > 1)
                        <div class="mb-3">
                            <label class="form-label">Quantity to move <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="{{ $registry->quantity }}" class="form-control @error('quantity') is-invalid @enderror" wire:model="quantity">
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Moving part of the lot splits it into a new asset record at the receiving location.</div>
                        </div>
                    @endif

                    <div>
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" rows="2" wire:model="reason"></textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="close">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>Request transfer</button>
                </div>
            </form>
        </x-engineering.modal>
    @endif
</div>
