@if($type === 'close')
    <label class="form-label">Quality of the work <span class="text-danger">*</span></label>
    <div class="d-flex gap-1 mb-1 fs-4" role="radiogroup">
        @for($i = 1; $i <= 5; $i++)
            <button type="button" class="btn btn-link p-0 text-warning" wire:click="$set('rating', {{ $i }})" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}">
                <i class="fa-{{ $i <= $rating ? 'solid' : 'regular' }} fa-star"></i>
            </button>
        @endfor
    </div>
    <div class="form-text mb-3">This rating feeds the assignee's ticket performance score.</div>
    @error('rating') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
    <label class="form-label">Closing remarks</label>
    <textarea class="form-control" rows="3" wire:model="remarks" placeholder="Optional"></textarea>
@else
    <label class="form-label">Why does this need more work? <span class="text-danger">*</span></label>
    <textarea class="form-control @error('remarks') is-invalid @enderror" rows="3" wire:model="remarks"></textarea>
    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
@endif
