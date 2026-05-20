<div class="container-fluid py-4">
    @if(! $hasEmployee)
        {{-- No employee profile linked to this account (e.g. admin) --}}
        <div class="row justify-content-center">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-id-badge text-muted fa-3x mb-3"></i>
                        <h5 class="fw-bold mb-2">No employee profile linked</h5>
                        <p class="text-muted mb-0">
                            Your account isn't linked to an employee record yet, so there's no personal
                            dashboard to show. Please ask HR to link your account.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
    {{-- Greeting --}}
    <div class="mb-4">
        <h5 class="fw-bold mb-0">Welcome, {{ $employeeName }}</h5>
        <small class="text-muted">{{ $departmentName }} department overview</small>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="fa-solid fa-people-group text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">My Department</p>
                            <h4 class="mb-0 fw-bold">{{ $departmentName }}</h4>
                            <small class="text-success">
                                <i class="fa-solid fa-circle-check"></i> {{ number_format($deptHeadcount) }} active members
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
                                <i class="fa-solid fa-calendar-check text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">My Leave Days Used</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($myLeaveDaysUsed) }}</h4>
                            <small class="text-muted">Approved, this year</small>
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
                            <h4 class="mb-0 fw-bold">{{ number_format($deptOnLeaveToday) }}</h4>
                            <small class="text-muted">In my department</small>
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
                                <i class="fa-solid fa-file-contract text-info fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">My Contract</p>
                            @if(! is_null($myContractDaysRemaining))
                                <h4 class="mb-0 fw-bold">{{ number_format($myContractDaysRemaining) }}</h4>
                                <small class="text-muted">days remaining</small>
                            @else
                                <h4 class="mb-0 fw-bold">&mdash;</h4>
                                <small class="text-muted">No active contract</small>
                            @endif
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
                            <small class="text-muted">My department &bull; last 7 days</small>
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
                            <small class="text-muted">Average performance score &bull; my department</small>
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
                            <small class="text-muted">My department &bull; employee scores breakdown</small>
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
                            <small class="text-muted">My department &bull; based on performance scores</small>
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

    @endif

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
