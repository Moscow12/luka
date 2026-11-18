<x-pdf.layout
    title="CHOP Cost Analysis"
    subtitle="Planned amounts vs actual costs per activity"
    :workstation="$workstation"
    :meta="$filters + ['Activities' => number_format($stats['total_activities'])]"
    :signatures="['Prepared by', 'Checked by', 'Approved by']"
    note="Actual cost = sum of quantity × price of each activity's items. Variance = planned − actual; positive means under budget. Amounts in TZS.">

    @php $under = $stats['total_variance'] >= 0; @endphp
    <div class="summary-cards">
        <div class="summary-card" style="width: 25%;">
            <div class="label">Total Planned</div>
            <div class="value">{{ number_format($stats['total_planned'], 2) }}</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Total Actual</div>
            <div class="value">{{ number_format($stats['total_actual'], 2) }}</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Variance</div>
            <div class="value {{ $under ? 'text-success' : 'text-danger' }}">{{ number_format($stats['total_variance'], 2) }}</div>
            <div class="small {{ $under ? 'text-success' : 'text-danger' }}">{{ number_format(abs($stats['variance_percentage']), 1) }}% {{ $under ? 'under' : 'over' }} budget</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Activities</div>
            <div class="value">{{ number_format($stats['total_activities']) }}</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width: 3%;">#</th>
                <th style="width: 23%;">Activity</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 11%;">Source of Fund</th>
                <th style="width: 7%;">FY</th>
                <th style="width: 7%;">Type</th>
                <th class="num" style="width: 10%;">Planned</th>
                <th class="num" style="width: 10%;">Actual</th>
                <th class="num" style="width: 10%;">Variance</th>
                <th class="center" style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                @php $ok = $activity->variance >= 0; @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $activity->planned_activity }}</strong>
                        @if($activity->description)
                            <br><span class="text-muted small">{{ \Illuminate\Support\Str::limit($activity->description, 90) }}</span>
                        @endif
                    </td>
                    <td>{{ $activity->category?->name ?? '-' }}</td>
                    <td>
                        @if($activity->source?->colorcode)
                            <span style="display: inline-block; width: 7px; height: 7px; background-color: {{ $activity->source->colorcode }};"></span>
                        @endif
                        {{ $activity->source?->name ?? '-' }}
                    </td>
                    <td>{{ $activity->financialYear?->name ?? '-' }}</td>
                    <td>{{ ucfirst((string) $activity->activity_type) ?: '-' }}</td>
                    <td class="num">{{ number_format($activity->planned_amount ?? 0, 2) }}</td>
                    <td class="num">{{ number_format($activity->calculated_actual_cost, 2) }}</td>
                    <td class="num {{ $ok ? 'text-success' : 'text-danger' }}">
                        {{ number_format($activity->variance, 2) }}
                        <br><span class="small">({{ number_format(abs($activity->variance_percentage), 1) }}%)</span>
                    </td>
                    <td class="center"><span class="badge {{ $ok ? 'badge-success' : 'badge-danger' }}">{{ $ok ? 'Under' : 'Over' }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="center text-muted" style="padding: 20px;">No activities match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($activities->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6">TOTAL</td>
                    <td class="num">{{ number_format($stats['total_planned'], 2) }}</td>
                    <td class="num">{{ number_format($stats['total_actual'], 2) }}</td>
                    <td class="num">{{ number_format($stats['total_variance'], 2) }}</td>
                    <td class="center">{{ $under ? 'Under' : 'Over' }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</x-pdf.layout>
