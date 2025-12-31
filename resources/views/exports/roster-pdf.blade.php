<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Employee Roster - {{ $summary['month_name'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 10px;
            color: #6c757d;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .summary-card {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 5px;
        }

        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #0d6efd;
        }

        .summary-card .label {
            font-size: 8px;
            color: #6c757d;
        }

        .legend {
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        .legend-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 10px;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }

        th {
            background-color: #0d6efd;
            color: white;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #0d6efd;
        }

        th.employee-header {
            text-align: left;
            min-width: 100px;
        }

        th.date-header {
            width: 20px;
            font-size: 6px;
        }

        th.summary-header {
            min-width: 60px;
        }

        td {
            padding: 3px 2px;
            text-align: center;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        td.employee-cell {
            text-align: left;
            font-weight: 500;
        }

        td.employee-cell .department {
            font-size: 6px;
            color: #6c757d;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .shift-badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 7px;
        }

        .shift-morning { background-color: #0d6efd; color: white; }
        .shift-afternoon { background-color: #198754; color: white; }
        .shift-night { background-color: #6f42c1; color: white; }
        .shift-default { background-color: #6c757d; color: white; }
        .shift-off { color: #6c757d; }

        .summary-cell {
            font-size: 6px;
            text-align: center;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EMPLOYEE ROSTER</h1>
        <div class="subtitle">{{ $summary['month_name'] }}</div>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <div class="value">{{ $summary['month_name'] }}</div>
            <div class="label">Current Period</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ number_format($summary['total_employees']) }}</div>
            <div class="label">Total Employees</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ number_format($summary['total_rosters']) }}</div>
            <div class="label">Roster Entries</div>
        </div>
    </div>

    @if($shifts->count() > 0)
        <div class="legend">
            <span class="legend-title">Legend:</span>
            @foreach($shifts as $index => $shift)
                @php
                    $colors = ['shift-morning', 'shift-afternoon', 'shift-night', 'shift-default'];
                    $colorClass = $colors[$index % count($colors)];
                @endphp
                <span class="legend-item {{ $colorClass }}">
                    {{ strtoupper(substr($shift->name, 0, 1)) }} - {{ $shift->name }}
                </span>
            @endforeach
            <span class="legend-item" style="background-color: #e9ecef;">OFF - Day Off</span>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="employee-header">Employee</th>
                @foreach($dates as $date)
                    <th class="date-header">
                        {{ $date->format('d') }}<br>{{ $date->format('D') }}
                    </th>
                @endforeach
                <th class="summary-header">Summary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                <tr>
                    <td class="employee-cell">
                        {{ $employee->first_name }} {{ $employee->last_name }}
                        @if($employee->department)
                            <div class="department">{{ $employee->department->name }}</div>
                        @endif
                    </td>
                    @foreach($dates as $date)
                        @php
                            $dateStr = $date->format('Y-m-d');
                            $key = $employee->id . '_' . $dateStr;
                            $roster = $rosterData->get($key)?->first();

                            if ($roster && $roster->shift) {
                                $shiftAbbr = strtoupper(substr($roster->shift->name, 0, 1));
                                $shifts_array = $shifts->toArray();
                                $shift_index = array_search($roster->shift->id, array_column($shifts_array, 'id'));
                                $colors = ['shift-morning', 'shift-afternoon', 'shift-night', 'shift-default'];
                                $colorClass = $shift_index !== false ? $colors[$shift_index % count($colors)] : 'shift-default';
                            } else {
                                $shiftAbbr = '-';
                                $colorClass = 'shift-off';
                            }
                        @endphp
                        <td>
                            <span class="shift-badge {{ $colorClass }}">{{ $shiftAbbr }}</span>
                        </td>
                    @endforeach
                    <td class="summary-cell">
                        @php
                            $empSummary = $employeeSummaries[$employee->id] ?? ['shifts' => [], 'off_days' => 0];
                        @endphp
                        @if(!empty($empSummary['shifts']))
                            @foreach($empSummary['shifts'] as $shiftName => $count)
                                {{ substr($shiftName, 0, 1) }}:{{ $count }}
                            @endforeach
                            @if($empSummary['off_days'] > 0)
                                <br>OFF:{{ $empSummary['off_days'] }}
                            @endif
                        @else
                            No shifts
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, H:i') }} | Employee Roster Report
    </div>
</body>
</html>
