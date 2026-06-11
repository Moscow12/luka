<table>
    <tr>
        <td colspan="{{ 3 + $dates->count() + 1 }}" style="text-align:center;font-size:14px;font-weight:bold;">
            STAFF ATTENDANCE REPORT
        </td>
    </tr>
    <tr>
        <td colspan="{{ 3 + $dates->count() + 1 }}" style="text-align:center;font-size:10px;">
            Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            &nbsp;|&nbsp; Total Staff: {{ count($rows) }}
        </td>
    </tr>
    <tr>
        <th style="background-color:#0d6efd;color:#fff;font-weight:bold;text-align:center;">#</th>
        <th style="background-color:#0d6efd;color:#fff;font-weight:bold;text-align:center;">FP ID</th>
        <th style="background-color:#0d6efd;color:#fff;font-weight:bold;text-align:left;">Name</th>
        @foreach($dates as $date)
            <th style="background-color:#0d6efd;color:#fff;font-weight:bold;text-align:center;">
                {{ \Carbon\Carbon::parse($date)->format('d M') }} ({{ \Carbon\Carbon::parse($date)->format('D') }})
            </th>
        @endforeach
        <th style="background-color:#0d6efd;color:#fff;font-weight:bold;text-align:center;">Rate</th>
    </tr>
    @foreach($rows as $i => $row)
        <tr>
            <td style="text-align:center;">{{ $i + 1 }}</td>
            <td style="text-align:center;font-weight:600;">{{ $row['fp_id'] }}</td>
            <td style="font-weight:500;">{{ $row['name'] }}</td>
            @foreach($dates as $date)
                @php
                    $cell = $row['dates'][$date] ?? null;
                    $status = $cell['status'] ?? 'no_show';
                    $in  = $cell['clock_in']  ? \Carbon\Carbon::parse($cell['clock_in'])->format('H:i')  : '';
                    $out = ($cell['clock_out'] && ($cell['has_checkout'] ?? false))
                                ? \Carbon\Carbon::parse($cell['clock_out'])->format('H:i')
                                : '';
                    $attendStatus = $cell['attendance_status'] ?? '';

                    if ($status === 'no_show') {
                        $bg   = 'fde8e8';
                        $text = 'NO SHOW';
                    } elseif ($attendStatus === 'Late') {
                        $bg   = 'fff3cd';
                        $text = ($in ? "IN {$in}" : '') . ($out ? " | OUT {$out}" : '') . ' [LATE]';
                    } elseif ($attendStatus === 'Incomplete') {
                        $bg   = 'e2e3e5';
                        $text = ($in ? "IN {$in}" : 'No IN') . ' [INC]';
                    } else {
                        $bg   = 'd1e7dd';
                        $text = ($in ? "IN {$in}" : '') . ($out ? " | OUT {$out}" : '');
                    }
                @endphp
                <td style="background-color:#{{ $bg }};text-align:center;font-size:9px;">{{ $text }}</td>
            @endforeach
            <td style="text-align:center;font-weight:bold;">{{ $row['attendance_rate'] }}%</td>
        </tr>
    @endforeach
</table>
