<table>
    <thead>
        <tr>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">#</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Employee Number</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Employee Name</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Department</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Period</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Basic Salary</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Gross Salary</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Total Deductions</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Net Salary</th>
            <th style="background-color: #0d6efd; color: white; font-weight: bold;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payrolls as $index => $payroll)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $payroll->employee->employee_number ?? 'N/A' }}</td>
                <td>{{ $payroll->employee->first_name }} {{ $payroll->employee->middle_name }} {{ $payroll->employee->last_name }}</td>
                <td>{{ $payroll->employee->department->name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</td>
                <td>{{ $payroll->basic_salary }}</td>
                <td>{{ $payroll->gross_salary }}</td>
                <td>{{ $payroll->total_deductions }}</td>
                <td>{{ $payroll->net_salary }}</td>
                <td>{{ ucfirst($payroll->status) }}</td>
            </tr>
        @endforeach
        <!-- Summary Row -->
        <tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="5" style="text-align: right;">TOTAL</td>
            <td></td>
            <td>{{ $summary['total_gross'] }}</td>
            <td>{{ $summary['total_deductions'] }}</td>
            <td>{{ $summary['total_net'] }}</td>
            <td>{{ $summary['total_payrolls'] }} Records</td>
        </tr>
    </tbody>
</table>
