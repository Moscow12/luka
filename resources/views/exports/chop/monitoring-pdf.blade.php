@use('App\Reports\Chop\MonitoringReport')
@php
    $frequencies = \App\Models\chopactivities::getFrequencyOptions();
    $textOn = ['completed' => '#fff', 'not_completed' => '#fff', 'cancelled' => '#fff', 'partially_completed' => '#333', 'pending' => '#495057'];
@endphp

<x-pdf.layout
    :title="'CHOP Monitoring & Evaluation'"
    subtitle="Monitoring calendar of active activities"
    :workstation="$workstation"
    :meta="$filters"
    :signatures="['Prepared by', 'Checked by', 'Approved by']"
    note="Pending = monitoring due but not yet reported. A dash means the activity is not due for monitoring that month. Amounts in TZS.">

    <style>
        .calendar th.month, .calendar td.month { width: {{ count($months) ? round(46 / count($months), 2) : 4 }}%; text-align: center; padding: 3px 1px; font-size: 7px; }
        .cell { display: block; padding: 2px 0; border-radius: 2px; font-size: 7px; font-weight: bold; }
        .legend td { padding: 2px 6px; font-size: 8px; }
        .legend .swatch { display: inline-block; width: 9px; height: 9px; vertical-align: middle; margin-right: 3px; }
    </style>

    <div class="summary-cards">
        <div class="summary-card" style="width: 25%;">
            <div class="label">Active Activities</div>
            <div class="value">{{ number_format($stats['total_activities']) }}</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Total Budget</div>
            <div class="value">{{ number_format($stats['total_planned_amount'], 2) }}</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Approved</div>
            <div class="value text-success">{{ number_format($stats['approved_activities']) }}</div>
        </div>
        <div class="summary-card" style="width: 25%;">
            <div class="label">Pending Approval</div>
            <div class="value text-danger">{{ number_format($stats['pending_approval']) }}</div>
        </div>
    </div>

    @if(count($months))
        <table class="legend" style="margin-bottom: 6px;">
            <tr>
                @foreach(MonitoringReport::STATUS_SHORT as $status => $label)
                    <td><span class="swatch" style="background-color: #{{ MonitoringReport::STATUS_COLOURS[$status] }};"></span>{{ $label }}</td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="data-table calendar">
        <thead>
            <tr>
                <th class="center" style="width: 3%;">#</th>
                <th style="width: 20%;">Activity</th>
                <th style="width: 11%;">Personnel</th>
                <th class="num" style="width: 9%;">Budget</th>
                <th class="center" style="width: 7%;">Frequency</th>
                @forelse($months as $month)
                    <th class="month">{{ $month['name'] }}<br>{{ substr($month['year'], 2) }}</th>
                @empty
                    <th class="center">Reports</th>
                    <th class="num">Amount Spent</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        @if($activity->source?->colorcode)
                            <span style="display: inline-block; width: 7px; height: 7px; background-color: {{ $activity->source->colorcode }};"></span>
                        @endif
                        <strong>{{ $activity->planned_activity }}</strong>
                        <br><span class="small text-muted">{{ $activity->category?->name ?? '-' }}{{ $activity->source ? ' · '.$activity->source->name : '' }}</span>
                        @if($activity->is_approved)
                            <span class="badge badge-success">Approved</span>
                        @endif
                    </td>
                    <td class="small">{{ $activity->personels->map(fn ($p) => $p->title?->name)->filter()->implode(', ') ?: '-' }}</td>
                    <td class="num">{{ number_format($activity->planned_amount ?? 0, 2) }}</td>
                    <td class="center small">{{ $frequencies[$activity->frequence_monitoring] ?? '-' }}</td>
                    @forelse($months as $month)
                        @php $status = MonitoringReport::cellStatus($activity, $month); @endphp
                        <td class="month">
                            @if($status)
                                <span class="cell" style="background-color: #{{ MonitoringReport::STATUS_COLOURS[$status] ?? 'E9ECEF' }}; color: {{ $textOn[$status] ?? '#333' }};">
                                    {{ MonitoringReport::STATUS_SHORT[$status] ?? ucfirst($status) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @empty
                        <td class="center">{{ $activity->reports->count() }}</td>
                        <td class="num">{{ number_format($activity->reports->sum('amount_spent'), 2) }}</td>
                    @endforelse
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + max(count($months), 2) }}" class="center text-muted" style="padding: 20px;">No activities match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @unless(count($months))
        <p class="doc-note">Select a financial year to print the monthly monitoring calendar.</p>
    @endunless
</x-pdf.layout>
