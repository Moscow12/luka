<div class="container-fluid py-4">
    {{-- Summary Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fa-solid fa-users text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Employees</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalEmployees) }}</h4>
                            <small class="text-success">
                                <i class="fa-solid fa-circle-check"></i> {{ $activeEmployees }} active
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="fa-solid fa-building text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Departments</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($totalDepartments) }}</h4>
                            <small class="text-muted">Organization units</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                <i class="fa-solid fa-umbrella-beach text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">On Leave Today</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($onLeaveToday) }}</h4>
                            <small class="text-muted">Employees away</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="fa-solid fa-chart-line text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Avg Attendance</p>
                            <h4 class="mb-0 fw-bold">{{ $attendanceChartData['average_rate'] ?? 0 }}%</h4>
                            <small class="text-muted">Last 7 days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="row g-3 mb-4">
        {{-- Attendance Rate Chart --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Attendance Rate</h6>
                            <small class="text-muted">Daily attendance for the last 7 days</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary">Weekly</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="attendanceChart" style="height: 280px;"></div>
                </div>
            </div>
        </div>

        {{-- Leave Distribution --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Leave Coverage</h6>
                            <small class="text-muted">{{ now()->year }} distribution</small>
                        </div>
                        <span class="badge bg-success-subtle text-success">{{ $leaveDistributionData['coverageRate'] ?? 0 }}% used</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="leaveChart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="row g-3 mb-4">
        {{-- Department Performance --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Departmental Performance</h6>
                            <small class="text-muted">Average performance score by department</small>
                        </div>
                        <i class="fa-solid fa-chart-bar text-muted"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div id="departmentChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        {{-- Employee Performance Distribution --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Performance Distribution</h6>
                            <small class="text-muted">Employee scores breakdown</small>
                        </div>
                        <i class="fa-solid fa-chart-pie text-muted"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div id="performanceDistChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="row g-3 mb-4">
        {{-- Expiring Contracts --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                <i class="fa-solid fa-file-contract text-danger me-2"></i>
                                Expiring Contracts
                            </h6>
                            <small class="text-muted">Contracts expiring within {{ $expiringContractsDays }} days</small>
                        </div>
                        <span class="badge bg-danger">{{ count($expiringContracts) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($expiringContracts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Employee</th>
                                        <th class="border-0 d-none d-md-table-cell">Department</th>
                                        <th class="border-0">Expires</th>
                                        <th class="border-0 text-end">Days Left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringContracts as $contract)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2">
                                                        <span class="avatar-initials small">
                                                            {{ substr($contract['employee_name'], 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium small">{{ $contract['employee_name'] }}</div>
                                                        <div class="text-muted d-md-none" style="font-size: 11px;">{{ $contract['department'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <small class="text-muted">{{ $contract['department'] }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $contract['expire_date'] }}</small>
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $daysClass = $contract['days_remaining'] <= 7 ? 'danger' :
                                                                ($contract['days_remaining'] <= 14 ? 'warning' : 'info');
                                                @endphp
                                                <span class="badge bg-{{ $daysClass }}-subtle text-{{ $daysClass }}">
                                                    {{ $contract['days_remaining'] }} days
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-check-circle text-success fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No contracts expiring soon</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                <i class="fa-solid fa-trophy text-warning me-2"></i>
                                Top Performers
                            </h6>
                            <small class="text-muted">Based on performance scores</small>
                        </div>
                        <span class="badge bg-warning-subtle text-warning">{{ count($topPerformers) }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($topPerformers) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topPerformers as $index => $performer)
                                <div class="list-group-item border-0 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            @if($index < 3)
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}" style="font-size: 10px; z-index: 1;">
                                                    {{ $index + 1 }}
                                                </span>
                                            @endif
                                            @if($performer['photo'])
                                                <img src="{{ asset('storage/' . $performer['photo']) }}" alt="" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <div class="avatar avatar-sm bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }}-subtle text-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }} rounded-circle">
                                                    <span class="avatar-initials">{{ substr($performer['name'], 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium small">{{ $performer['name'] }}</div>
                                            <small class="text-muted">{{ $performer['department'] }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">{{ $performer['score'] }}%</div>
                                            <div class="progress" style="width: 60px; height: 4px;">
                                                <div class="progress-bar bg-success" style="width: {{ $performer['score'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-chart-simple text-muted fa-3x mb-3"></i>
                            <p class="text-muted mb-0">No performance data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Leaves & Monthly Trend --}}
    <div class="row g-3">
        {{-- Monthly Leave Trend --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">Monthly Leave Trend</h6>
                            <small class="text-muted">Approved leaves per month in {{ now()->year }}</small>
                        </div>
                        <span class="badge bg-info-subtle text-info">{{ $leaveDistributionData['totalLeaveDays'] ?? 0 }} total days</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="monthlyLeaveChart" style="height: 250px;"></div>
                </div>
            </div>
        </div>

        {{-- Recent Leaves --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                <i class="fa-solid fa-clock-rotate-left text-info me-2"></i>
                                Recent Leaves
                            </h6>
                            <small class="text-muted">Latest leave requests</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($recentLeaves) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentLeaves as $leave)
                                <div class="list-group-item border-0 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium small">{{ $leave['employee'] }}</div>
                                            <small class="text-muted">{{ $leave['type'] }} &bull; {{ $leave['days'] }} days</small>
                                        </div>
                                        <span class="badge bg-{{ $leave['status'] == 'approved' ? 'success' : ($leave['status'] == 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $leave['status'] == 'approved' ? 'success' : ($leave['status'] == 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($leave['status']) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $leave['start'] }} - {{ $leave['end'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa-solid fa-inbox text-muted fa-2x mb-2"></i>
                            <p class="text-muted small mb-0">No recent leaves</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Rate Chart
    const attendanceData = @json($attendanceChartData);
    if (attendanceData.labels && attendanceData.labels.length > 0) {
        new ApexCharts(document.querySelector("#attendanceChart"), {
            series: [{
                name: 'Attendance Rate',
                type: 'area',
                data: attendanceData.rate
            }, {
                name: 'Present',
                type: 'column',
                data: attendanceData.present
            }],
            chart: {
                height: 280,
                type: 'line',
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            stroke: {
                width: [3, 0],
                curve: 'smooth'
            },
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1
                }
            },
            colors: ['#0d6efd', '#198754'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%'
                }
            },
            xaxis: {
                categories: attendanceData.labels,
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: [{
                title: { text: 'Rate (%)' },
                min: 0,
                max: 100
            }, {
                opposite: true,
                title: { text: 'Present' }
            }],
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            tooltip: {
                shared: true,
                intersect: false
            }
        }).render();
    }

    // Leave Distribution Chart
    const leaveData = @json($leaveDistributionData);
    if (leaveData.typeLabels && leaveData.typeLabels.length > 0) {
        new ApexCharts(document.querySelector("#leaveChart"), {
            series: leaveData.typeCounts,
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'inherit'
            },
            labels: leaveData.typeLabels,
            colors: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Leaves',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '12px'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: { position: 'bottom' }
                }
            }]
        }).render();
    } else {
        document.querySelector("#leaveChart").innerHTML = '<div class="text-center py-5 text-muted"><i class="fa-solid fa-chart-pie fa-3x mb-3 opacity-25"></i><p>No leave data</p></div>';
    }

    // Department Performance Chart
    const deptData = @json($departmentPerformanceData);
    if (deptData.labels && deptData.labels.length > 0) {
        new ApexCharts(document.querySelector("#departmentChart"), {
            series: [{
                name: 'Performance Score',
                data: deptData.scores
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#0d6efd'],
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val + '%'; },
                offsetX: 20,
                style: { fontSize: '11px', colors: ['#333'] }
            },
            xaxis: {
                categories: deptData.labels,
                max: 100,
                labels: {
                    formatter: function(val) { return val + '%'; }
                }
            },
            yaxis: {
                labels: { style: { fontSize: '11px' } }
            },
            tooltip: {
                y: {
                    formatter: function(val, opts) {
                        return val + '% (Employees: ' + deptData.employees[opts.dataPointIndex] + ')';
                    }
                }
            }
        }).render();
    } else {
        document.querySelector("#departmentChart").innerHTML = '<div class="text-center py-5 text-muted"><i class="fa-solid fa-chart-bar fa-3x mb-3 opacity-25"></i><p>No department data</p></div>';
    }

    // Performance Distribution Chart
    const perfData = @json($employeePerformanceData);
    if (perfData.distribution && perfData.distribution.values.some(v => v > 0)) {
        new ApexCharts(document.querySelector("#performanceDistChart"), {
            series: [{
                name: 'Employees',
                data: perfData.distribution.values
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '60%',
                    distributed: true
                }
            },
            colors: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'],
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontWeight: 'bold' }
            },
            xaxis: {
                categories: perfData.distribution.labels,
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                title: { text: 'Number of Employees' }
            },
            legend: { show: false },
            tooltip: {
                y: {
                    formatter: function(val) { return val + ' employees'; }
                }
            }
        }).render();
    } else {
        document.querySelector("#performanceDistChart").innerHTML = '<div class="text-center py-5 text-muted"><i class="fa-solid fa-chart-pie fa-3x mb-3 opacity-25"></i><p>No performance data</p></div>';
    }

    // Monthly Leave Trend Chart
    if (leaveData.monthLabels && leaveData.monthLabels.length > 0) {
        new ApexCharts(document.querySelector("#monthlyLeaveChart"), {
            series: [{
                name: 'Leaves',
                data: leaveData.monthCounts
            }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                fontFamily: 'inherit',
                sparkline: { enabled: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0.1
                }
            },
            colors: ['#6f42c1'],
            xaxis: {
                categories: leaveData.monthLabels,
                labels: { style: { fontSize: '11px' } }
            },
            yaxis: {
                title: { text: 'Leave Count' }
            },
            markers: {
                size: 4,
                colors: ['#6f42c1'],
                strokeColors: '#fff',
                strokeWidth: 2
            },
            tooltip: {
                y: {
                    formatter: function(val) { return val + ' leaves'; }
                }
            }
        }).render();
    }
});
</script>
    @endpush

    <style>
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }

    .avatar-initials {
        font-weight: 600;
        text-transform: uppercase;
    }

    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }

    .list-group-item {
        transition: background-color 0.2s ease;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        h4 {
            font-size: 1.25rem;
        }
    }
    </style>
</div>
