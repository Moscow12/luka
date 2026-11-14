{{-- Section -> engineer -> assistants. Expects $allSections and $staff; used by assignment and re-assignment. --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Section required <span class="text-danger">*</span></label>
        <select class="form-select @error('formSectionId') is-invalid @enderror" wire:model.live="formSectionId">
            <option value="">Select section</option>
            @foreach($allSections as $section)
                <option value="{{ $section->id }}">{{ $section->name }}</option>
            @endforeach
        </select>
        @error('formSectionId') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Assigned engineer <span class="text-danger">*</span></label>
        <select class="form-select @error('engineerId') is-invalid @enderror" wire:model.live="engineerId" @disabled($staff->isEmpty())>
            <option value="">{{ $staff->isEmpty() ? 'No active staff in this section' : 'Select engineer' }}</option>
            @foreach($staff as $person)
                <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</option>
            @endforeach
        </select>
        @error('engineerId') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Only active staff of the selected section are listed.</div>
    </div>
    <div class="col-12">
        <label class="form-label">Assistant engineers</label>
        <div class="d-flex flex-wrap gap-2">
            @forelse($staff->where('id', '!=', $engineerId) as $person)
                <div wire:key="assistant-{{ $person->id }}">
                    <input type="checkbox" class="btn-check" id="assistant-{{ $person->id }}" value="{{ $person->id }}" wire:model="assistantIds">
                    <label class="btn btn-sm btn-outline-secondary" for="assistant-{{ $person->id }}">{{ $person->first_name }} {{ $person->last_name }}</label>
                </div>
            @empty
                <span class="text-muted small">No other staff in this section.</span>
            @endforelse
        </div>
    </div>
</div>
