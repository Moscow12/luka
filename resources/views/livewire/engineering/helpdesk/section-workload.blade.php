<x-engineering.page title="Section workload" subtitle="Who is carrying what, and what has been waiting too long">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-success" wire:click="export"><i class="fa-solid fa-file-excel me-1"></i>Export to Excel</button>
    </x-slot:actions>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Section</label>
                    <select class="form-select" wire:model.live="sectionId">
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small">Closed jobs in</label>
                    <select class="form-select" wire:model.live="period">
                        <option value="week">Week</option>
                        <option value="month">Month</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small">Containing date</label>
                    <input type="date" class="form-control" wire:model.live="date">
                </div>
                <div class="col-md-2 text-md-end small text-muted pb-2">{{ $from->format('d M') }} – {{ $to->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>S/N</th>
                        <th>Name of staff</th>
                        <th class="text-center">Open jobs</th>
                        <th class="text-center">Closed jobs</th>
                        <th class="text-center">Avg TAT (days)</th>
                        <th class="text-center">Oldest open (days)</th>
                        <th class="text-center">Awaiting spares</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr wire:key="staff-{{ $row['id'] }}" class="{{ $drillEmployeeId === $row['id'] ? 'table-active' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-center">
                                @if($row['open'] > 0)
                                    <button type="button" class="btn btn-sm {{ $drillEmployeeId === $row['id'] ? 'btn-primary' : 'btn-outline-primary' }} px-3" wire:click="drill('{{ $row['id'] }}')" title="Show open jobs">
                                        {{ $row['open'] }}
                                    </button>
                                @else
                                    0
                                @endif
                            </td>
                            <td class="text-center">{{ $row['closed'] }}</td>
                            <td class="text-center">{{ $row['avg_tat'] !== null ? number_format($row['avg_tat'], 2) : '—' }}</td>
                            <td class="text-center {{ ($row['oldest'] ?? 0) >= $staleDays ? 'text-danger fw-semibold' : '' }}">{{ $row['oldest'] ?? '—' }}</td>
                            <td class="text-center">{{ $row['awaiting_spares'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <x-engineering.empty colspan="7" icon="fa-people-group" text="No staff in this section yet. Add them under Engineering setup." />
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="2">Section total</td>
                            <td class="text-center">{{ $rows->sum('open') }}</td>
                            <td class="text-center">{{ $rows->sum('closed') }}</td>
                            <td colspan="2"></td>
                            <td class="text-center">{{ $rows->sum('awaiting_spares') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if($drillEmployeeId)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Open jobs · {{ $drillName }}</h6>
                <button type="button" class="btn-close" wire:click="drill(null)" aria-label="Close"></button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>S/N</th><th>MRV No.</th><th>Equipment</th><th>Department</th><th>Room</th><th>Status</th><th class="text-center">Days unattended</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        @foreach($drillJobs as $job)
                            <tr wire:key="drill-{{ $job->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold text-nowrap">{{ $job->number }}</td>
                                <td>{{ $job->equipmentName() }}</td>
                                <td>{{ $job->requisition->department?->name ?? '—' }}</td>
                                <td>{{ $job->requisition->locationLabel() }}</td>
                                <td><x-engineering.job-badges :job="$job" /></td>
                                <td class="text-center {{ $job->daysUnattended() >= $staleDays ? 'text-danger fw-semibold' : '' }}">{{ $job->daysUnattended() }}</td>
                                <td class="text-end"><a href="{{ route('engineering.jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">Open job</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-engineering.page>
