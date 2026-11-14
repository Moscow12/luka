{{-- Period selector for reports using the HasReportPeriod concern --}}
<div class="col-6 col-md-2">
    <label class="form-label small">Period</label>
    <select class="form-select" wire:model.live="period">
        @foreach($this::PERIODS as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-6 col-md-2">
    <label class="form-label small">From</label>
    <input type="date" class="form-control" wire:model.live="dateFrom" @disabled($period !== 'custom')>
</div>
<div class="col-6 col-md-2">
    <label class="form-label small">To</label>
    <input type="date" class="form-control" wire:model.live="dateTo" @disabled($period !== 'custom')>
</div>
