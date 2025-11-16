<div>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('chop.settings') }}">CHOP</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cost Analysis</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-1">CHOP Cost Analysis</h2>
        <p class="text-muted">Compare planned amounts vs actual costs for activities</p>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Planned</h6>
                            <h4 class="mb-0">{{ number_format($stats['total_planned'], 0) }}</h4>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-clipboard-list fa-2x text-primary"></i>
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
                            <h6 class="text-muted mb-1">Total Actual</h6>
                            <h4 class="mb-0">{{ number_format($stats['total_actual'], 0) }}</h4>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-calculator fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-{{ $stats['total_variance'] >= 0 ? 'success' : 'danger' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Variance</h6>
                            <h4 class="mb-0 text-{{ $stats['total_variance'] >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($stats['total_variance'], 0) }}
                            </h4>
                            <small class="text-muted">
                                {{ number_format(abs($stats['variance_percentage']), 1) }}%
                                @if($stats['total_variance'] >= 0)
                                    <i class="fa-solid fa-arrow-down text-success"></i> Under budget
                                @else
                                    <i class="fa-solid fa-arrow-up text-danger"></i> Over budget
                                @endif
                            </small>
                        </div>
                        <div class="bg-{{ $stats['total_variance'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-chart-line fa-2x text-{{ $stats['total_variance'] >= 0 ? 'success' : 'danger' }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Activities</h6>
                            <h4 class="mb-0">{{ $stats['total_activities'] }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 p-3 rounded">
                            <i class="fa-solid fa-tasks fa-2x text-secondary"></i>
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
                <div class="col-md-3">
                    <input type="text" wire:model.live="search" class="form-control"
                           placeholder="Search activities...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterFinancialYear">
                        <option value="">All Financial Years</option>
                        @foreach($financialYears as $fy)
                            <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterSource">
                        <option value="">All Sources</option>
                        @foreach($sources as $source)
                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterActivityType">
                        <option value="">All Types</option>
                        @foreach($activityTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-secondary w-100" wire:click="$set('filterFinancialYear', ''); $set('filterCategory', ''); $set('filterSource', ''); $set('filterActivityType', ''); $set('search', '');" title="Clear Filters">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Activities Cost Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 2%"></th>
                            <th style="width: 20%">Activity</th>
                            <th style="width: 10%">Category</th>
                            <th style="width: 10%">Source of Fund</th>
                            <th style="width: 8%">Financial Year</th>
                            <th style="width: 6%">Type</th>
                            <th style="width: 12%">Planned Amount</th>
                            <th style="width: 12%">Actual Cost</th>
                            <th style="width: 12%">Variance</th>
                            <th style="width: 5%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr class="activity-row">
                                <td>
                                    <button class="btn btn-sm btn-link p-0" wire:click="toggleActivity('{{ $activity->id }}')">
                                        <i class="fa-solid fa-{{ $expandedActivity === $activity->id ? 'chevron-down' : 'chevron-right' }}"></i>
                                    </button>
                                </td>
                                <td>
                                    <strong>{{ $activity->planned_activity }}</strong>
                                    @if($activity->description)
                                        <br><small class="text-muted">{{ Str::limit($activity->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>{{ $activity->category->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-dark border border-info">
                                        {{ $activity->source->name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $activity->financialYear->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $activity->activity_type === 'expenditure' ? 'danger' : 'success' }}">
                                        {{ ucfirst($activity->activity_type ?? '-') }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ number_format($activity->planned_amount ?? 0, 2) }} TZS</td>
                                <td class="fw-bold text-info">{{ number_format($activity->calculated_actual_cost ?? 0, 2) }} TZS</td>
                                <td>
                                    <span class="fw-bold text-{{ $activity->variance >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($activity->variance, 2) }} TZS
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        ({{ number_format(abs($activity->variance_percentage), 1) }}%)
                                    </small>
                                </td>
                                <td>
                                    @if($activity->variance >= 0)
                                        <span class="badge bg-success" title="Under budget">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-danger" title="Over budget">
                                            <i class="fa-solid fa-exclamation"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Expanded Items Detail Row -->
                            @if($expandedActivity === $activity->id && $activity->items->count() > 0)
                                <tr class="table-info">
                                    <td colspan="10" class="p-0">
                                        <div class="p-3">
                                            <h6 class="mb-3"><i class="fa-solid fa-list me-2"></i>Activity Items Breakdown</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th style="width: 5%">#</th>
                                                            <th style="width: 40%">Item Name</th>
                                                            <th style="width: 15%">Quantity</th>
                                                            <th style="width: 20%">Price (TZS)</th>
                                                            <th style="width: 20%">Total Cost (TZS)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($activity->items as $index => $item)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $item->item->name ?? 'N/A' }}</td>
                                                                <td class="text-center">{{ $item->quantity ?? 0 }}</td>
                                                                <td class="text-end">{{ number_format($item->price ?? 0, 2) }}</td>
                                                                <td class="text-end fw-bold">
                                                                    {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="table-active fw-bold">
                                                            <td colspan="4" class="text-end">Total Actual Cost:</td>
                                                            <td class="text-end text-info">
                                                                {{ number_format($activity->calculated_actual_cost, 2) }} TZS
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @elseif($expandedActivity === $activity->id)
                                <tr class="table-warning">
                                    <td colspan="10" class="text-center py-3">
                                        <i class="fa-solid fa-info-circle me-2"></i>No items found for this activity
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-3x mb-3 d-block"></i>
                                    No activities found matching your criteria.
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
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="fa-solid fa-info-circle me-2"></i>Legend</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <span class="badge bg-success me-2"><i class="fa-solid fa-check"></i></span>
                        <strong>Under Budget:</strong> Actual cost is less than or equal to planned amount
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <span class="badge bg-danger me-2"><i class="fa-solid fa-exclamation"></i></span>
                        <strong>Over Budget:</strong> Actual cost exceeds planned amount
                    </p>
                </div>
            </div>
            <hr>
            <p class="mb-0 text-muted">
                <i class="fa-solid fa-calculator me-2"></i>
                <strong>Calculation:</strong> Actual Cost = Sum of (Item Quantity × Item Price) for all activity items
            </p>
        </div>
    </div>

    <!-- Inline styles -->
    <style>
        .activity-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .activity-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</div>
