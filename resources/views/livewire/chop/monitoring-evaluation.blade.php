<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">Monitoring & Evaluation</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-1"><i class="fa-solid fa-chart-line text-primary"></i> CHOP Monitoring & Evaluation</h2>
        <p class="text-muted">Track and monitor activities across the financial year</p>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Activities</h6>
                            <h4 class="mb-0">{{ $stats['total_activities'] }}</h4>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-tasks fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h4 class="mb-0">{{ $stats['approved_activities'] }}</h4>
                            <small class="text-muted">Activities</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Approval</h6>
                            <h4 class="mb-0">{{ $stats['pending_approval'] }}</h4>
                            <small class="text-muted">Activities</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Budget</h6>
                            <h4 class="mb-0">{{ number_format($stats['total_planned_amount'], 0) }}</h4>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-money-bill-wave fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" wire:model.live="search" class="form-control"
                           placeholder="Search activities...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterFinancialYear">
                        <option value="">All Financial Years</option>
                        @foreach($financialYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-secondary w-100" wire:click="$set('filterFinancialYear', ''); $set('filterCategory', ''); $set('search', '');" title="Clear Filters">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($selectedFY && count($months) > 0)
        <!-- Monitoring Matrix -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-calendar-alt"></i> Monitoring Calendar - {{ $selectedFY->name }}</h5>
                    <small class="text-muted">Color-coded by Source of Funds</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 monitoring-matrix">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 250px; position: sticky; left: 0; z-index: 10; background: #f8f9fa;" class="border-end">
                                    <i class="fa-solid fa-list-check"></i> Activity Details
                                </th>
                                <th style="width: 150px;" class="text-center">Personnel</th>
                                <th style="width: 120px;" class="text-end">Budget</th>
                                <th style="width: 100px;" class="text-center">Frequency</th>
                                @foreach($months as $month)
                                    <th class="text-center month-header" style="min-width: 70px;">
                                        <div>{{ $month['name'] }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $month['year'] }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $activity)
                                <tr class="activity-row">
                                    <td style="position: sticky; left: 0; z-index: 5; background: white;" class="border-end">
                                        <div class="d-flex align-items-start">
                                            <div
                                                class="activity-color-indicator me-2"
                                                style="background-color: {{ $activity->source->colorcode ?? '#6c757d' }}; width: 4px; height: 100%; min-height: 40px;"
                                                title="{{ $activity->source->name ?? 'No Source' }}"
                                            ></div>
                                            <div class="flex-grow-1">
                                                <strong>{{ $activity->planned_activity }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-tag"></i> {{ $activity->category->name ?? 'N/A' }}
                                                </small>
                                                @if($activity->is_approved)
                                                    <br>
                                                    <span class="badge bg-success-subtle text-success mt-1" style="font-size: 0.65rem;">
                                                        <i class="fa-solid fa-check"></i> Approved
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($activity->personels->count() > 0)
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                @foreach($activity->personels->take(3) as $personnel)
                                                    <span class="badge bg-info-subtle text-info" style="font-size: 0.7rem;" title="{{ $personnel->title->name ?? 'N/A' }}">
                                                        {{ Str::limit($personnel->title->name ?? 'N/A', 15) }}
                                                    </span>
                                                @endforeach
                                                @if($activity->personels->count() > 3)
                                                    <span class="badge bg-secondary" style="font-size: 0.7rem;">+{{ $activity->personels->count() - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ number_format($activity->planned_amount, 0) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @if($activity->frequence_monitoring)
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">
                                                {{ $frequencyOptions[$activity->frequence_monitoring] ?? '-' }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    @foreach($months as $month)
                                        @php
                                            $monthYear = sprintf('%04d-%02d', $month['year'], $month['number']);
                                            $report = $this->getReportForActivityMonth($activity->id, $monthYear);
                                            $shouldMonitor = $this->shouldMonitorInMonth($activity, $month['number']);
                                        @endphp
                                        <td class="text-center monitoring-cell">
                                            @if($shouldMonitor)
                                                @if($report)
                                                    @php
                                                        $reportStatusConfig = [
                                                            'completed' => ['color' => '#28a745', 'icon' => 'check-circle'],
                                                            'partially_completed' => ['color' => '#ffc107', 'icon' => 'exclamation-circle'],
                                                            'not_completed' => ['color' => '#dc3545', 'icon' => 'times-circle'],
                                                            'cancelled' => ['color' => '#6c757d', 'icon' => 'ban'],
                                                        ];
                                                        $config = $reportStatusConfig[$report->status] ?? ['color' => '#6c757d', 'icon' => 'question'];
                                                    @endphp
                                                    <div
                                                        class="monitoring-indicator {{ $report->is_approved ? 'border border-2 border-success' : '' }}"
                                                        style="background-color: {{ $config['color'] }};"
                                                        title="Status: {{ ucfirst(str_replace('_', ' ', $report->status)) }}
Amount Spent: {{ number_format($report->amount_spent, 2) }} TZS
{{ $report->is_approved ? 'Approved' : 'Pending Approval' }}
Reported by: {{ $report->reporter->name ?? 'N/A' }}"
                                                    >
                                                        <i class="fa-solid fa-{{ $config['icon'] }} text-white"></i>
                                                        @if($report->is_approved)
                                                            <i class="fa-solid fa-check-double position-absolute top-0 end-0 text-success bg-white rounded-circle" style="font-size: 0.6rem; padding: 2px;"></i>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div
                                                        class="monitoring-indicator opacity-50"
                                                        style="background-color: {{ $activity->source->colorcode ?? '#6c757d' }};"
                                                        title="Monitor in {{ $month['full_name'] }} - Not yet reported"
                                                    >
                                                        <i class="fa-solid fa-clock text-white"></i>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted" style="font-size: 0.8rem;">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + count($months) }}" class="text-center py-5">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 d-block text-muted opacity-25"></i>
                                        <h5 class="text-muted">No activities found</h5>
                                        <p class="mb-0">Adjust your filters to see activities</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($activities->hasPages())
                <div class="card-footer bg-white">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

        <!-- Legend -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><i class="fa-solid fa-info-circle me-2"></i>Legend - Source of Funds</h6>
                <div class="row">
                    @php
                        $sources = \App\Models\sourceoffunds::orderBy('name')->get();
                    @endphp
                    @foreach($sources as $source)
                        <div class="col-md-3 col-sm-4 col-6 mb-2">
                            <div class="d-flex align-items-center">
                                <div style="width: 20px; height: 20px; background-color: {{ $source->colorcode ?? '#6c757d' }}; border-radius: 3px;" class="me-2"></div>
                                <small>{{ $source->name }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-2"><i class="fa-solid fa-calendar-check me-2"></i>Monitoring Frequency Guide</h6>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($frequencyOptions as $key => $label)
                                <div>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $label }}</span>
                                    <small class="text-muted ms-1">
                                        @if($key === 'weekly')
                                            (Every week)
                                        @elseif($key === 'monthly')
                                            (Every month)
                                        @elseif($key === 'quarterly')
                                            (Every 3 months)
                                        @elseif($key === 'bi_annually' || $key === 'biannual')
                                            (Twice a year)
                                        @elseif($key === 'annually' || $key === 'annual')
                                            (Once a year)
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-2"><i class="fa-solid fa-flag-checkered me-2"></i>Report Status Indicators</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="monitoring-indicator" style="background-color: #28a745; width: 25px; height: 25px;">
                                    <i class="fa-solid fa-check-circle text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="ms-2">Completed</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="monitoring-indicator" style="background-color: #ffc107; width: 25px; height: 25px;">
                                    <i class="fa-solid fa-exclamation-circle text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="ms-2">Partial</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="monitoring-indicator" style="background-color: #dc3545; width: 25px; height: 25px;">
                                    <i class="fa-solid fa-times-circle text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="ms-2">Not Done</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="monitoring-indicator opacity-50" style="background-color: #6c757d; width: 25px; height: 25px;">
                                    <i class="fa-solid fa-clock text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="ms-2">Pending</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="monitoring-indicator border border-2 border-success" style="background-color: #28a745; width: 25px; height: 25px;">
                                    <i class="fa-solid fa-check text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                <small class="ms-2">Approved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Financial Year Selected -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-calendar-xmark fa-4x mb-3 text-muted opacity-25"></i>
                <h5 class="text-muted">No Financial Year Selected</h5>
                <p class="mb-0">Please select a financial year to view the monitoring calendar</p>
            </div>
        </div>
    @endif

    <!-- Inline Styles -->
    <style>
        .monitoring-matrix {
            font-size: 0.9rem;
        }

        .monitoring-matrix th {
            font-weight: 600;
            vertical-align: middle;
        }

        .month-header {
            background-color: #f8f9fa;
            border-left: 2px solid #dee2e6;
        }

        .activity-row {
            transition: background-color 0.2s;
        }

        .activity-row:hover {
            background-color: #f8f9fa;
        }

        .activity-color-indicator {
            border-radius: 2px;
        }

        .monitoring-cell {
            vertical-align: middle;
            padding: 0.5rem;
        }

        .monitoring-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .monitoring-indicator:hover {
            transform: scale(1.1);
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 20;
        }

        @media (max-width: 768px) {
            .monitoring-matrix {
                font-size: 0.8rem;
            }

            .monitoring-indicator {
                width: 25px;
                height: 25px;
            }

            .month-header div {
                font-size: 0.8rem;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .monitoring-matrix {
                font-size: 0.7rem;
            }

            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
    </style>
</div>
