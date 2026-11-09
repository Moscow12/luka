{{--
    "View" modal: one leave request's details and approval timeline.
    Expects: $progressLeave (Employeeleaves with ->progress, or null), $ownRequest (bool: the viewer raised it)
    The host component must use App\Livewire\Hr\Leave\Concerns\ShowsLeaveProgress.
--}}
@if($progressLeave)
    @php
        $progress = $progressLeave->progress;
        $alert = ['approved' => 'success', 'rejected' => 'danger', 'blocked' => 'danger', 'unconfigured' => 'secondary'][$progress['state']] ?? 'warning';
        $alertIcon = ['approved' => 'check-circle', 'rejected' => 'times-circle', 'blocked' => 'triangle-exclamation', 'unconfigured' => 'circle-info'][$progress['state']] ?? 'clock';
    @endphp
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="leave-progress-title" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape.window="closeProgressModal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title mb-0" id="leave-progress-title"><i class="fa-solid fa-list-check me-2"></i>Leave Request Progress</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeProgressModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card bg-light mb-3">
                        <div class="card-body py-2">
                            <div class="row small g-2">
                                @unless($ownRequest ?? true)
                                    <div class="col-sm-6"><strong>Employee:</strong> {{ $progressLeave->employee->first_name ?? '' }} {{ $progressLeave->employee->last_name ?? '' }}</div>
                                @endunless
                                <div class="col-sm-6"><strong>Leave Type:</strong> {{ $progressLeave->leave->name ?? 'N/A' }}</div>
                                <div class="col-sm-6"><strong>Days:</strong> {{ $progressLeave->days }}</div>
                                <div class="col-sm-6"><strong>Period:</strong> {{ \Carbon\Carbon::parse($progressLeave->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($progressLeave->end_date)->format('d M Y') }}</div>
                                <div class="col-sm-6"><strong>Travel To:</strong> {{ $progressLeave->travel_to ?: '—' }}</div>
                                <div class="col-sm-6"><strong>Submitted:</strong> {{ $progressLeave->created_at?->format('d M Y, H:i') }}</div>
                                @if($progressLeave->actingAssignment?->actingEmployee)
                                    <div class="col-sm-6"><strong>Acting:</strong> {{ $progressLeave->actingAssignment->actingEmployee->first_name }} {{ $progressLeave->actingAssignment->actingEmployee->last_name }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-{{ $alert }} mb-4">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <strong>Where it stands:</strong>
                                <span class="ms-1">{{ $progress['summary'] }}</span>
                                @if($progress['total'] > 0)
                                    <div class="small mt-1">{{ $progress['done'] }} of {{ $progress['total'] }} approval {{ \Illuminate\Support\Str::plural('level', $progress['total']) }} done</div>
                                @endif
                            </div>
                            <i class="fa-solid fa-{{ $alertIcon }} fa-2x flex-shrink-0"></i>
                        </div>
                    </div>

                    @include('livewire.hr.leave.partials.approval-progress', ['progress' => $progress, 'ownRequest' => $ownRequest ?? true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeProgressModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
