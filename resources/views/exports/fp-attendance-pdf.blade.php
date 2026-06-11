<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 8px; margin: 0; }
    h2 { text-align: center; margin: 0 0 2px; font-size: 13px; }
    .sub { text-align: center; color: #555; margin: 0 0 8px; font-size: 9px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #0d6efd; color: #fff; padding: 4px 3px; text-align: center; font-size: 8px; white-space: nowrap; }
    th.name-col { text-align: left; min-width: 100px; }
    td { padding: 3px; border: 1px solid #dee2e6; vertical-align: middle; white-space: nowrap; }
    td.name-col { font-weight: 600; min-width: 100px; }
    td.center { text-align: center; }
    .present  { background: #d1e7dd; color: #0a3622; }
    .late     { background: #fff3cd; color: #664d03; }
    .incomplete { background: #e2e3e5; color: #41464b; }
    .no-show  { background: #fde8e8; color: #842029; font-weight: bold; }
    .rate-good { color: #198754; font-weight: bold; }
    .rate-warn { color: #856404; font-weight: bold; }
    .rate-bad  { color: #842029; font-weight: bold; }
    tr:nth-child(even) td { background-color: inherit; }
</style>
</head>
<body>
    <h2>STAFF ATTENDANCE REPORT</h2>
    <p class="sub">
        Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
        &nbsp;&bull;&nbsp; Total Staff: {{ count($rows) }}
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>FP ID</th>
                <th class="name-col">Name</th>
                @foreach($dates as $date)
                    <th>
                        {{ \Carbon\Carbon::parse($date)->format('d M') }}<br>
                        {{ \Carbon\Carbon::parse($date)->format('D') }}
                    </th>
                @endforeach
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="center">{{ $row['fp_id'] }}</td>
                    <td class="name-col">{{ $row['name'] }}</td>
                    @foreach($dates as $date)
                        @php
                            $cell   = $row['dates'][$date] ?? null;
                            $status = $cell['status'] ?? 'no_show';
                            $in     = $cell['clock_in']  ? \Carbon\Carbon::parse($cell['clock_in'])->format('H:i')  : '';
                            $out    = ($cell['clock_out'] && ($cell['has_checkout'] ?? false))
                                            ? \Carbon\Carbon::parse($cell['clock_out'])->format('H:i')
                                            : '';
                            $attendStatus = $cell['attendance_status'] ?? '';

                            if ($status === 'no_show') {
                                $cls  = 'no-show';
                                $text = 'NO SHOW';
                            } elseif ($attendStatus === 'Late') {
                                $cls  = 'late';
                                $text = ($in ? "IN {$in}" : '') . ($out ? "\nOUT {$out}" : '') . "\n[LATE]";
                            } elseif ($attendStatus === 'Incomplete') {
                                $cls  = 'incomplete';
                                $text = ($in ? "IN {$in}" : 'No IN') . "\n[INC]";
                            } else {
                                $cls  = 'present';
                                $text = ($in ? "IN {$in}" : '') . ($out ? "\nOUT {$out}" : '');
                            }
                        @endphp
                        <td class="center {{ $cls }}" style="font-size:7px;">
                            {!! nl2br(e($text)) !!}
                        </td>
                    @endforeach
                    @php
                        $rate      = $row['attendance_rate'];
                        $rateClass = $rate >= 90 ? 'rate-good' : ($rate >= 60 ? 'rate-warn' : 'rate-bad');
                    @endphp
                    <td class="center {{ $rateClass }}">{{ $rate }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
