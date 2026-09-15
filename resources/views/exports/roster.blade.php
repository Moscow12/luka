<table>
    <tr>
        <td colspan="{{ count($dates) + 2 }}" style="text-align: center; font-size: 16px; font-weight: bold;">
            EMPLOYEE ROSTER - {{ strtoupper($summary['month_name']) }}
        </td>
    </tr>
    <tr>
        <td colspan="{{ count($dates) + 2 }}" style="text-align: center; font-size: 12px;">
            Total Employees: {{ $summary['total_employees'] }} | Total Roster Entries: {{ $summary['total_rosters'] }}
        </td>
    </tr>
    <tr>
        <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">Employee</th>
        @foreach($dates as $date)
            <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">
                {{ $date->format('d') }}<br>{{ $date->format('D') }}
            </th>
        @endforeach
        <th style="background-color: #0d6efd; color: white; font-weight: bold; text-align: center;">Summary</th>
    </tr>
    @foreach($employees as $employee)
        <tr>
            <td style="font-weight: 500;">
                {{ $employee->first_name }} {{ $employee->last_name }}
                @if($employee->department)
                    <br><small style="color: #6c757d;">{{ $employee->department->name }}</small>
                @endif
            </td>
            @foreach($dates as $date)
                @php
                    $dateStr = $date->format('Y-m-d');
                    $key = $employee->id . '_' . $dateStr;
                    $roster = $rosterData->get($key)?->first();
                    $shiftAbbr = $roster && $roster->shift ? strtoupper(substr($roster->shift->name, 0, 1)) : '-';
                @endphp
                <td style="text-align: center; font-weight: bold;">
                    {{ $shiftAbbr }}
                </td>
            @endforeach
            <td style="text-align: center; font-size: 10px;">
                @php
                    $empSummary = $employeeSummaries[$employee->id] ?? ['shifts' => [], 'off_days' => 0];
                @endphp
                @if(!empty($empSummary['shifts']))
                    @foreach($empSummary['shifts'] as $shiftName => $count)
                        {{ substr($shiftName, 0, 1) }}:{{ $count }}
                    @endforeach
                    @if($empSummary['off_days'] > 0)
                        OFF:{{ $empSummary['off_days'] }}
                    @endif
                @else
                    -
                @endif
            </td>
        </tr>
    @endforeach
</table>
