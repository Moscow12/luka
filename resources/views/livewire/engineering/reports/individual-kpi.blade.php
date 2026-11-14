@php
    use App\Livewire\Engineering\Reports\IndividualKpi;
    use App\Support\Engineering\Eng;

    $dash = fn ($value, $decimals = 1) => $value === null ? '—' : number_format($value, $decimals);
@endphp

<x-engineering.page title="Individual KPI for engineers" :subtitle="'Reporting week · '.$weekLabel">
    <x-slot:actions>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary" wire:click="shiftWeek(-1)" title="Previous week"><i class="fa-solid fa-chevron-left"></i></button>
            <input type="date" class="form-control rounded-0" wire:model.live="date" style="width: 170px;" title="Any date in the week">
            <button type="button" class="btn btn-outline-secondary" wire:click="shiftWeek(1)" title="Next week"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <ul class="nav nav-pills flex-nowrap overflow-auto">
            @foreach(IndividualKpi::TABS as $key => $label)
                @if($seesOthers || in_array($key, ['scores', 'oxygen']))
                    <li class="nav-item"><button type="button" class="nav-link text-nowrap {{ $tab === $key ? 'active' : '' }}" wire:click="setTab('{{ $key }}')">{{ $label }}</button></li>
                @endif
            @endforeach
        </ul>
        @if($seesOthers && $tab !== 'oxygen' && $tab !== 'uptime')
            <select class="form-select ms-md-auto" wire:model.live="filterSection" style="width: auto;">
                <option value="">All sections</option>
                @foreach($sections as $section)<option value="{{ $section->id }}">{{ $section->name }}</option>@endforeach
            </select>
        @endif
    </div>

    @if($tab === 'scores')
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="text-start">S/N</th>
                            <th rowspan="2" class="text-start">Staff</th>
                            <th colspan="3">Jobs</th>
                            <th colspan="3">PPM</th>
                            <th rowspan="2">Avg TAT<br><span class="fw-normal small">days</span></th>
                            @foreach(IndividualKpi::KPIS as $key => $label)
                                <th rowspan="2" title="Target {{ $targets[$key] }}{{ $key === 'tat' ? ' days' : '%' }} · weight {{ $weights[$key] + 0 }}%">{{ $label }}<br><span class="fw-normal small">weighted</span></th>
                            @endforeach
                            <th rowspan="2">Overall<br>week score</th>
                        </tr>
                        <tr><th>Assigned</th><th>Done</th><th>%</th><th>Assigned</th><th>Done</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr wire:key="kpi-{{ $row['employee_id'] }}" class="{{ $monthlyEmployeeId === $row['employee_id'] ? 'table-active' : '' }}">
                                <td class="text-start">{{ $loop->iteration }}</td>
                                <td class="text-start">
                                    <a href="#" class="text-decoration-none fw-semibold" wire:click.prevent="showMonth('{{ $row['employee_id'] }}')" title="Show the month">{{ $row['name'] }}</a>
                                    <div class="small text-muted">{{ $row['section'] }}</div>
                                </td>
                                <td>{{ $row['metrics']['assigned'] }}</td>
                                <td>{{ $row['metrics']['finished'] }}</td>
                                <td>{{ $dash($row['metrics']['jobs_pct']) }}</td>
                                <td>{{ $row['metrics']['ppm_assigned'] }}</td>
                                <td>{{ $row['metrics']['ppm_finished'] }}</td>
                                <td>{{ $dash($row['metrics']['ppm_pct']) }}</td>
                                <td>{{ $dash($row['metrics']['tat_days'], 2) }}</td>
                                @foreach(array_keys(IndividualKpi::KPIS) as $key)
                                    <td title="{{ $row['scores'][$key] === null ? 'Not applicable this week' : 'Score '.number_format($row['scores'][$key], 1).'%' }}">{{ $dash($row['weighted'][$key], 3) }}</td>
                                @endforeach
                                <td class="eng-cell-{{ $row['band'] }} fw-bold">{{ number_format($row['overall'], 3) }}</td>
                            </tr>
                        @empty
                            <x-engineering.empty colspan="18" icon="fa-user-gear" text="No engineering staff to report on. Add staff to sections under Engineering setup." />
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small">
                <span class="eng-cell-red px-2 rounded">≤ 0.600</span>
                <span class="eng-cell-yellow px-2 rounded">0.601 – 0.900</span>
                <span class="eng-cell-green px-2 rounded">≥ 0.901</span>
                <span class="text-muted ms-2">Maintenance KPI target {{ $targets['overall'] }}%. “—” = not applicable to this person this week; the remaining weights are re-normalised to 100%. Hover a column for its target and weight.</span>
            </div>
        </div>

        @if($monthly)
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Monthly score · {{ $rows->firstWhere('employee_id', $monthlyEmployeeId)['name'] }} · {{ \Illuminate\Support\Carbon::parse($date)->format('F Y') }}</h6>
                    <button type="button" class="btn-close" wire:click="showMonth(null)"></button>
                </div>
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    @forelse($monthly['weeks'] as $week)
                        <div class="border rounded px-3 py-2 text-center eng-cell-{{ $week['band'] }}">
                            <div class="small">Week of {{ $week['week_start']->format('d M') }}</div>
                            <div class="fw-bold">{{ number_format($week['overall'], 3) }}</div>
                        </div>
                    @empty
                        <span class="text-muted">No reporting weeks in this month yet.</span>
                    @endforelse
                    @if($monthly['average'] !== null)
                        <div class="ms-md-auto text-end">
                            <div class="small text-muted">Monthly score (average of the weeks)</div>
                            <div class="h4 mb-0">{{ number_format($monthly['average'], 3) }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @elseif($tab === 'uptime')
        @forelse($uptime as $department)
            <div class="card mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0">{{ $department['department'] }}</h6>
                    <span class="small text-muted">Engineer assigned: <strong>{{ $department['engineer'] ?? 'nobody' }}</strong></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 text-center">
                        <thead class="table-light"><tr><th class="text-start">Device type</th><th>Working</th><th>Partly defective</th><th>Out of service</th><th>Total</th><th>% uptime</th></tr></thead>
                        <tbody>
                            @foreach($department['types'] as $type)
                                <tr>
                                    <td class="text-start">{{ $type['type'] }}</td>
                                    <td>{{ $type['working'] }}</td><td>{{ $type['partly_defective'] }}</td><td>{{ $type['out_of_service'] }}</td><td>{{ $type['total'] }}</td>
                                    <td>{{ $dash($type['uptime']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td class="text-start">All devices in the department</td>
                                <td>{{ $department['totals']['working'] }}</td><td>{{ $department['totals']['partly_defective'] }}</td><td>{{ $department['totals']['out_of_service'] }}</td><td>{{ $department['totals']['total'] }}</td>
                                <td class="{{ ($department['totals']['uptime'] ?? 0) >= $targets['uptime'] ? 'eng-cell-green' : 'eng-cell-yellow' }}">{{ $dash($department['totals']['uptime']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-center py-5 text-muted">No equipment in the inventory yet.</div></div>
        @endforelse
        <p class="small text-muted">Uptime = working units ÷ total units, from the current status in the inventory. Target {{ $targets['uptime'] }}%.</p>
    @elseif($tab === 'five_s')
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>S/N</th><th>Staff</th><th>Section</th><th class="text-center">5S score this week</th><th style="width: 260px;">Record score (0–100%)</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr wire:key="five-{{ $row['employee_id'] }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['section'] }}</td>
                                <td class="text-center">{{ $row['scores']['five_s'] === null ? 'Not recorded' : number_format($row['scores']['five_s'], 1).'%' }}</td>
                                <td>
                                    @if($canRecordFiveS)
                                        <div class="input-group input-group-sm">
                                            <input type="number" min="0" max="100" step="any" class="form-control @error('fiveS.'.$row['employee_id']) is-invalid @enderror" wire:model="fiveS.{{ $row['employee_id'] }}" wire:keydown.enter="saveFiveS('{{ $row['employee_id'] }}')">
                                            <button type="button" class="btn btn-outline-primary" wire:click="saveFiveS('{{ $row['employee_id'] }}')">Save</button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-engineering.empty colspan="5" text="No engineering staff to score." />
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="row g-3">
            @if($recordablePlants->isNotEmpty())
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Record a reading</h6></div>
                        <form wire:submit="saveReading" class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Plant <span class="text-danger">*</span></label>
                                <select class="form-select @error('readingPlantId') is-invalid @enderror" wire:model="readingPlantId">
                                    <option value="">Select</option>
                                    @foreach($recordablePlants as $plant)<option value="{{ $plant->id }}">{{ $plant->name }}</option>@endforeach
                                </select>
                                @error('readingPlantId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reading taken at <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('readingAt') is-invalid @enderror" wire:model="readingAt">
                                @error('readingAt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purity % <span class="text-danger">*</span></label>
                                <input type="number" min="0" max="100" step="any" class="form-control @error('readingPurity') is-invalid @enderror" wire:model="readingPurity">
                                @error('readingPurity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Readings are due every 12 hours. Below {{ $minPurity }}% alerts the responsible staff.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save reading</button>
                        </form>
                    </div>
                </div>
            @endif
            <div class="{{ $recordablePlants->isNotEmpty() ? 'col-lg-8' : 'col-12' }}">
                @forelse($plants as $plant)
                    @php $average = $plant->readings->avg('purity_pct'); $missing = max(0, $expectedReadings - $plant->readings->count()); @endphp
                    <div class="card mb-3">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h6 class="mb-0">{{ $plant->name }} <span class="text-muted fw-normal small">· {{ Eng::name($plant->responsible) }}</span></h6>
                            <span class="small">
                                Weekly average <strong>{{ $average === null ? '—' : number_format($average, 1).'%' }}</strong> ·
                                <span class="{{ $missing ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $plant->readings->count() }} of {{ $expectedReadings }} readings</span>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            @forelse($plant->readings as $reading)
                                <div class="border rounded px-2 py-1 text-center {{ $reading->isBelowMinimum() ? 'eng-cell-red' : '' }}" title="Recorded by {{ $reading->recordedBy?->full_name }}">
                                    <div class="small text-muted">{{ Eng::dt($reading->reading_at, 'D H:i') }}</div>
                                    <div class="fw-semibold">{{ number_format($reading->purity_pct, 1) }}%</div>
                                </div>
                            @empty
                                <span class="text-muted">No readings this week.</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="card"><div class="card-body text-center py-5 text-muted">No oxygen plants are set up. Add them under Engineering setup.</div></div>
                @endforelse
            </div>
        </div>
    @endif
</x-engineering.page>
