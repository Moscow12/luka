<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Salary Slip - {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0d6efd;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
        }

        .header .period {
            font-size: 14px;
            color: #6c757d;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 10px;
            vertical-align: top;
        }

        .info-table .label {
            color: #6c757d;
            width: 130px;
            font-weight: 600;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-card {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
        }

        .summary-card .label {
            font-size: 10px;
            color: #6c757d;
        }

        .summary-card.gross .value { color: #198754; }
        .summary-card.deduction .value { color: #dc3545; }
        .summary-card.net .value { color: #0d6efd; }

        .breakdown-container {
            display: table;
            width: 100%;
        }

        .breakdown-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }

        .breakdown-column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .breakdown-box {
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }

        .breakdown-header {
            padding: 8px 12px;
            font-weight: bold;
            font-size: 11px;
        }

        .breakdown-header.earnings {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .breakdown-header.deductions {
            background-color: #f8d7da;
            color: #842029;
        }

        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .breakdown-table th,
        .breakdown-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 10px;
        }

        .breakdown-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: left;
        }

        .breakdown-table th.amount {
            text-align: right;
        }

        .breakdown-table td.amount {
            text-align: right;
            font-weight: 500;
        }

        .breakdown-table .sub-row {
            background-color: #fafafa;
        }

        .breakdown-table .sub-row td {
            padding-left: 20px;
        }

        .breakdown-table .total-row {
            font-weight: bold;
        }

        .breakdown-table .total-row.earnings {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .breakdown-table .total-row.deductions {
            background-color: #f8d7da;
            color: #842029;
        }

        .net-salary-box {
            background-color: #cfe2ff;
            border: 2px solid #0d6efd;
            padding: 15px;
            margin-top: 15px;
        }

        .net-salary-box .label {
            font-weight: bold;
            color: #0d6efd;
            font-size: 12px;
        }

        .net-salary-box .calculation {
            font-size: 10px;
            color: #6c757d;
            margin-top: 3px;
        }

        .net-salary-box .amount {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .footer-note {
            font-size: 9px;
            color: #6c757d;
        }

        .signature-area {
            margin-top: 40px;
            text-align: right;
        }

        .signature-line {
            display: inline-block;
            border-top: 1px solid #333;
            padding-top: 5px;
            width: 150px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-success { background-color: #d1e7dd; color: #0f5132; }
        .badge-warning { background-color: #fff3cd; color: #664d03; }
        .badge-info { background-color: #cff4fc; color: #055160; }
        .badge-secondary { background-color: #e9ecef; color: #495057; }

        .text-muted { color: #6c757d; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }

        .small { font-size: 9px; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name', 'STAFF MANAGEMENT SYSTEM') }}</h1>
        <div class="subtitle">HRP SYSTEM</div>
        <h2>SALARY SLIP</h2>
        <div class="period">Period: {{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</div>
    </div>

    <!-- Employee Information -->
    <div class="section">
        <div class="section-title">Employee Information</div>
        <table class="info-table">
            <tr>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="label">Employee Name:</td>
                            <td><strong>{{ $payroll->employee->first_name }} {{ $payroll->employee->middle_name }} {{ $payroll->employee->last_name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Employee No:</td>
                            <td><span class="badge badge-secondary">{{ $payroll->employee->employee_number ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td class="label">Department:</td>
                            <td>{{ $payroll->employee->department->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Designation:</td>
                            <td>{{ $payroll->employee->designation->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="info-table">
                        <tr>
                            <td class="label">Pay Period:</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Payment Status:</td>
                            <td>
                                @php
                                    $statusClass = match($payroll->status) {
                                        'paid' => 'badge-success',
                                        'processed' => 'badge-info',
                                        default => 'badge-warning',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="label">Contract Type:</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payroll->contract->contract_type ?? 'N/A')) }}</td>
                        </tr>
                        @if($payroll->processed_at)
                        <tr>
                            <td class="label">Processed Date:</td>
                            <td>{{ \Carbon\Carbon::parse($payroll->processed_at)->format('d M Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card gross">
            <div class="label">Gross Salary</div>
            <div class="value">{{ format_tzs($payroll->gross_salary) }}</div>
        </div>
        <div class="summary-card deduction">
            <div class="label">Total Deductions</div>
            <div class="value">{{ format_tzs($payroll->total_deductions) }}</div>
        </div>
        <div class="summary-card net">
            <div class="label">Net Salary</div>
            <div class="value">{{ format_tzs($payroll->net_salary) }}</div>
        </div>
    </div>

    <!-- Salary Breakdown -->
    <div class="breakdown-container">
        <!-- Earnings Column -->
        <div class="breakdown-column">
            <div class="breakdown-box">
                <div class="breakdown-header earnings">EARNINGS</div>
                <table class="breakdown-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="amount">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Basic Salary</strong></td>
                            <td class="amount"><strong>{{ format_tzs($payroll->basic_salary) }}</strong></td>
                        </tr>
                        @php
                            $allowances = $payroll->items->where('type', 'allowance');
                        @endphp
                        @if($allowances->count() > 0)
                            <tr class="sub-row">
                                <td colspan="2"><strong class="small text-muted">ALLOWANCES ({{ $allowances->count() }})</strong></td>
                            </tr>
                            @foreach($allowances as $item)
                                <tr class="sub-row">
                                    <td>{{ $item->name }}</td>
                                    <td class="amount">{{ format_tzs($item->amount) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td><strong>Total Allowances</strong></td>
                                <td class="amount"><strong>{{ format_tzs($payroll->total_allowances) }}</strong></td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row earnings">
                            <td>GROSS SALARY</td>
                            <td class="amount">{{ format_tzs($payroll->gross_salary) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Deductions Column -->
        <div class="breakdown-column">
            <div class="breakdown-box">
                <div class="breakdown-header deductions">DEDUCTIONS</div>
                <table class="breakdown-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="amount">Amount (TZS)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $deductions = $payroll->items->where('type', 'deduction');
                        @endphp
                        @if($deductions->count() > 0)
                            @foreach($deductions as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td class="amount">{{ format_tzs($item->amount) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" style="text-align: center; color: #6c757d; padding: 20px;">No deductions</td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row deductions">
                            <td>TOTAL DEDUCTIONS</td>
                            <td class="amount">{{ format_tzs($payroll->total_deductions) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Net Salary Box -->
    <div class="net-salary-box">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="label">NET SALARY PAYABLE</div>
                    <div class="calculation">
                        Gross Salary ({{ format_tzs($payroll->gross_salary) }}) - Total Deductions ({{ format_tzs($payroll->total_deductions) }})
                    </div>
                </td>
                <td class="amount">
                    {{ format_tzs($payroll->net_salary) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <p class="footer-note"><strong>Note:</strong></p>
                    <p class="footer-note">
                        This is a computer-generated salary slip. All amounts are in Tanzanian Shillings (TZS).
                        For any queries, please contact the HR department.
                    </p>
                    <p class="footer-note" style="margin-top: 10px;">
                        Generated on: {{ now()->format('d F Y \a\t H:i') }}
                    </p>
                </td>
                <td style="width: 40%;">
                    <div class="signature-area">
                        <div class="signature-line">Authorized Signature</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
