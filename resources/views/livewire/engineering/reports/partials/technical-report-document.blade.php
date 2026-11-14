@php use App\Support\Engineering\Eng; @endphp

{{-- The technical report as a plain document: shown on screen, printed, and downloaded for MS Word --}}
<h2>{{ config('app.name') }}</h2>
<h3>Engineering technical report · {{ $job->number }}</h3>

<table style="width: 100%; border-collapse: collapse;" border="1" cellpadding="6">
    <tr><th style="width: 32%; text-align: left;">Date reported</th><td>{{ Eng::date($report->date_reported) }}</td></tr>
    <tr><th style="text-align: left;">Device / equipment / machine</th><td>{{ $job->equipmentName() }}</td></tr>
    <tr><th style="text-align: left;">Model</th><td>{{ $job->equipment?->model ?? '—' }}</td></tr>
    <tr><th style="text-align: left;">Serial number / hospital ID code</th><td>{{ $job->equipment?->serial_number ?? '—' }} / {{ $job->equipmentCode() }}</td></tr>
    <tr><th style="text-align: left;">Location</th><td>{{ $job->requisition->department?->name }} · {{ $job->equipment?->room?->label() ?? $job->requisition->locationLabel() }}</td></tr>
    <tr><th style="text-align: left;">Manufacturer</th><td>{{ $job->equipment?->manufacturer ?? '—' }}</td></tr>
    <tr><th style="text-align: left;">Problem background and current status</th><td>{!! nl2br(e($report->problem_background)) !!}</td></tr>
    <tr><th style="text-align: left;">What the technician / engineer did</th><td>{!! nl2br(e($report->work_done)) !!}</td></tr>
    <tr><th style="text-align: left;">What the equipment requires now</th><td>{!! nl2br(e($report->equipment_requirement)) !!}</td></tr>
    <tr><th style="text-align: left;">What the technician / engineer needs</th><td>{!! nl2br(e($report->engineer_needs)) ?: '—' !!}</td></tr>
    <tr><th style="text-align: left;">Cost of the equipment now</th><td>{{ Eng::money($report->current_value) }}</td></tr>
    <tr><th style="text-align: left;">Expected cost to buy a new item</th><td>{{ Eng::money($report->new_purchase_cost) }}</td></tr>
    <tr><th style="text-align: left;">Cost to bring an external expert</th><td>{{ Eng::money($report->external_expert_cost) }}</td></tr>
    <tr><th style="text-align: left;">Comment to management</th><td>{!! nl2br(e($report->comment_to_management)) !!}</td></tr>
    <tr><th style="text-align: left;">Reported by</th><td>{{ $report->reportedBy?->full_name }}{{ $report->reporter_role ? ' · '.$report->reporter_role : '' }}</td></tr>
</table>
