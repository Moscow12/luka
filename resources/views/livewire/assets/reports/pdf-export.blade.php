<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fixed Asset Registry Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #7f8c8d;
        }

        .header .generated-at {
            font-size: 9px;
            color: #95a5a6;
            margin-top: 5px;
        }

        .filters-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .filters-section h3 {
            font-size: 10px;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .filters-list {
            font-size: 9px;
        }

        .filters-list span {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 3px;
            margin: 2px;
        }

        .statistics-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-row {
            display: table-row;
        }

        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .stat-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-box .label {
            font-size: 8px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .stat-box.primary {
            background: #3498db;
            color: white;
        }

        .stat-box.primary .value,
        .stat-box.primary .label {
            color: white;
        }

        .stat-box.success {
            background: #27ae60;
            color: white;
        }

        .stat-box.success .value,
        .stat-box.success .label {
            color: white;
        }

        .stat-box.warning {
            background: #f39c12;
            color: white;
        }

        .stat-box.warning .value,
        .stat-box.warning .label {
            color: white;
        }

        .stat-box.info {
            background: #17a2b8;
            color: white;
        }

        .stat-box.info .value,
        .stat-box.info .label {
            color: white;
        }

        table.asset-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.asset-table th {
            background-color: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.asset-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #dee2e6;
            font-size: 8px;
        }

        table.asset-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table.asset-table tr:hover {
            background-color: #e9ecef;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .totals-section {
            margin-top: 20px;
            border-top: 2px solid #2c3e50;
            padding-top: 15px;
        }

        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals-table th,
        .totals-table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }

        .totals-table th {
            background-color: #f8f9fa;
            text-align: left;
            font-weight: bold;
        }

        .totals-table td {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
        }

        .page-break {
            page-break-after: always;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }

        .section-title {
            font-size: 12px;
            color: #2c3e50;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fixed Asset Registry Report</h1>
        <div class="subtitle">
            @if($reportType === 'registry')
                Complete Asset Register
            @elseif($reportType === 'depreciation')
                Depreciation Schedule
            @elseif($reportType === 'summary')
                Summary Report
            @elseif($reportType === 'by_department')
                Assets by Department
            @elseif($reportType === 'by_class')
                Assets by Classification
            @else
                Asset Report
            @endif
        </div>
        <div class="generated-at">
            Generated on: {{ $generatedAt->format('F d, Y \a\t h:i A') }}
        </div>
    </div>

    @if(count($filters) > 0)
    <div class="filters-section">
        <h3>Applied Filters</h3>
        <div class="filters-list">
            @foreach($filters as $key => $value)
                <span><strong>{{ $key }}:</strong> {{ $value }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="statistics-grid">
        <div class="stat-row">
            <div class="stat-box primary">
                <div class="value">{{ number_format($statistics['total_assets']) }}</div>
                <div class="label">Total Assets</div>
            </div>
            <div class="stat-box success">
                <div class="value">{{ number_format($statistics['total_value'], 2) }}</div>
                <div class="label">Total Value</div>
            </div>
            <div class="stat-box warning">
                <div class="value">{{ number_format($statistics['total_depreciation'], 2) }}</div>
                <div class="label">Total Depreciation</div>
            </div>
            <div class="stat-box info">
                <div class="value">{{ number_format($statistics['net_book_value'], 2) }}</div>
                <div class="label">Net Book Value</div>
            </div>
        </div>
    </div>

    <h3 class="section-title">Asset Register</h3>

    @if($assets->count() > 0)
    <table class="asset-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 7%;">Code</th>
                <th style="width: 10%;">Asset Name</th>
                <th style="width: 7%;">Class</th>
                <th style="width: 8%;">Serial No.</th>
                <th style="width: 9%;">Department</th>
                <th style="width: 7%;">Location</th>
                <th style="width: 5%;">Status</th>
                <th style="width: 5%;">Condition</th>
                <th style="width: 6%;">Purch. Date</th>
                <th style="width: 7%;" class="text-right">Cost</th>
                <th style="width: 5%;" class="text-center">Method</th>
                <th style="width: 4%;" class="text-center">Life</th>
                <th style="width: 7%;" class="text-right">Depreciation</th>
                <th style="width: 7%;" class="text-right">Net Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $index => $asset)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $asset->codeno ?? '-' }}</td>
                <td>{{ $asset->asset?->name ?? '-' }}</td>
                <td>{{ $asset->assetClass?->name ?? '-' }}</td>
                <td>{{ $asset->serial_number ?? '-' }}</td>
                <td>{{ $asset->department?->name ?? '-' }}</td>
                <td>{{ $asset->building?->name ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = match($asset->status) {
                            'active' => 'badge-success',
                            'disposed' => 'badge-danger',
                            'under_maintenance' => 'badge-warning',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($asset->status ?? '-') }}</span>
                </td>
                <td>
                    @php
                        $conditionClass = match($asset->condition) {
                            'new' => 'badge-success',
                            'good' => 'badge-info',
                            'fair' => 'badge-warning',
                            'poor', 'bad', 'worse' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                    @endphp
                    <span class="badge {{ $conditionClass }}">{{ ucfirst($asset->condition ?? '-') }}</span>
                </td>
                <td>{{ $asset->purchase_date?->format('Y-m-d') ?? '-' }}</td>
                <td class="text-right">{{ number_format($asset->purchase_cost ?? 0, 2) }}</td>
                <td class="text-center">
                    <span class="badge {{ $asset->effective_method === 'straight_line' ? 'badge-info' : 'badge-warning' }}">
                        {{ $asset->effective_method === 'straight_line' ? 'SL' : 'RB' }}{{ $asset->has_override ? '*' : '' }}
                    </span>
                </td>
                <td class="text-center">{{ $asset->effective_life }}{{ ($asset->useful_life_years !== null) ? '*' : '' }}</td>
                <td class="text-right">{{ number_format($asset->calculated_depreciation ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format(($asset->purchase_cost ?? 0) - ($asset->calculated_depreciation ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="font-size: 8px; margin-top: 5px; color: #666;">
        <strong>Legend:</strong> SL = Straight Line, RB = Reducing Balance, * = Asset-level override (not using class defaults)
    </div>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <th>Total Purchase Cost</th>
                <td>{{ number_format($statistics['total_value'], 2) }}</td>
            </tr>
            <tr>
                <th>Total Accumulated Depreciation</th>
                <td>{{ number_format($statistics['total_depreciation'], 2) }}</td>
            </tr>
            <tr>
                <th>Total Net Book Value</th>
                <td><strong>{{ number_format($statistics['net_book_value'], 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <h3 class="section-title">Status Summary</h3>
        <table class="asset-table" style="width: 50%;">
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-success">Active</span></td>
                    <td class="text-right">{{ number_format($statistics['active_assets']) }}</td>
                </tr>
                <tr>
                    <td><span class="badge badge-danger">Disposed</span></td>
                    <td class="text-right">{{ number_format($statistics['disposed_assets']) }}</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Under Maintenance</span></td>
                    <td class="text-right">{{ number_format($statistics['under_maintenance']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <h3 class="section-title">Condition Summary</h3>
        <table class="asset-table" style="width: 50%;">
            <thead>
                <tr>
                    <th>Condition</th>
                    <th class="text-right">Count</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-success">New</span></td>
                    <td class="text-right">{{ number_format($statistics['by_condition']['new'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><span class="badge badge-info">Good</span></td>
                    <td class="text-right">{{ number_format($statistics['by_condition']['good'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><span class="badge badge-warning">Fair</span></td>
                    <td class="text-right">{{ number_format($statistics['by_condition']['fair'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><span class="badge badge-danger">Poor</span></td>
                    <td class="text-right">{{ number_format($statistics['by_condition']['poor'] ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @else
    <div class="no-data">
        No assets found matching the specified criteria.
    </div>
    @endif

    <div class="footer">
        <p>Fixed Asset Registry Report - Confidential</p>
        <p>This report was automatically generated by the Asset Management System</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
