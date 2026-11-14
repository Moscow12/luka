@php use App\Livewire\Engineering\Reports\WeeklySummary; @endphp

<x-engineering.page title="MRV weekly summary" :subtitle="$weekLabel">
    <x-slot:actions>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" wire:click="shiftWeek(-1)" title="Previous week"><i class="fa-solid fa-chevron-left"></i></button>
            <input type="date" class="form-control rounded-0" wire:model.live="date" style="width: 170px;" title="Any date in the week">
            <button type="button" class="btn btn-outline-secondary" wire:click="shiftWeek(1)" title="Next week"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        @foreach($columns as $column)<th class="text-center">{{ $column['label'] }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach(WeeklySummary::ROWS as $key => [$label, $metric])
                        <tr>
                            <td>{{ $label }}</td>
                            @foreach($columns as $column)
                                @php $value = $column['current'][$key]; $band = $metric ? $kpi->band($metric, $value) : null; @endphp
                                <td class="text-center {{ $band ? 'eng-cell-'.$band.' fw-semibold' : '' }}">{{ $value ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    <tr class="table-light"><th colspan="{{ count($columns) + 1 }}">Improvements <span class="fw-normal text-muted">(current week − previous week)</span></th></tr>

                    @foreach(WeeklySummary::VARIANCE_ROWS as $key => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            @foreach($columns as $column)
                                @php [$delta, $band] = $kpi->variance($key, $column['current'][$key], $column['previous'][$key] ?? null); @endphp
                                <td class="text-center {{ $band ? 'eng-cell-'.$band.' fw-semibold' : '' }}">
                                    @if($delta === null) — @else {{ sprintf('%+.2f', $delta) }} {{ WeeklySummary::SYMBOLS[$band] }} @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer small">
            <div class="mb-1">
                <strong>Performance:</strong>
                <span class="eng-cell-green px-2 rounded">target achieved</span>
                <span class="eng-cell-yellow px-2 rounded">near target, HOS to help</span>
                <span class="eng-cell-red px-2 rounded">target missed, HOS and HOD to intervene</span>
            </div>
            <div>
                <strong>Improvement:</strong>
                <span class="eng-cell-green px-2 rounded">▲ improved</span>
                <span class="eng-cell-yellow px-2 rounded">▬ did not improve</span>
                <span class="eng-cell-red px-2 rounded">▼ dropped in performance</span>
                <span class="text-muted ms-2">“—” means there is nothing to compare or divide by.</span>
            </div>
        </div>
    </div>
</x-engineering.page>
