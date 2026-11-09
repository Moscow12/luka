{{--
    Approval timeline for one leave request: what has happened, who must act now, and what comes after.
    Expects: $progress (from LeaveApprovalChain::progress()), optional $ownRequest (bool, default true)
--}}
@php
    $look = [
        'submitted'     => ['color' => 'primary',   'icon' => 'fa-paper-plane',          'badge' => 'Submitted'],
        'approved'      => ['color' => 'success',   'icon' => 'fa-check',                'badge' => 'Approved'],
        'rejected'      => ['color' => 'danger',    'icon' => 'fa-times',                'badge' => 'Rejected'],
        'current'       => ['color' => 'warning',   'icon' => 'fa-hourglass-half',       'badge' => 'Waiting now'],
        'blocked'       => ['color' => 'danger',    'icon' => 'fa-triangle-exclamation', 'badge' => 'No approver'],
        'upcoming'      => ['color' => 'secondary', 'icon' => 'fa-ellipsis',             'badge' => 'Next'],
        'final'         => ['color' => 'success',   'icon' => 'fa-flag-checkered',       'badge' => 'Done'],
        'final_pending' => ['color' => 'secondary', 'icon' => 'fa-flag-checkered',       'badge' => 'Not yet'],
    ];
    $muted = ['upcoming', 'final_pending'];
@endphp

<div class="position-relative">
    @foreach($progress['steps'] as $step)
        @php($s = $look[$step['state']] ?? $look['upcoming'])
        <div class="d-flex gap-3 mb-4 position-relative" wire:key="progress-step-{{ $loop->index }}">
            @if(!$loop->last)
                <div class="position-absolute" style="left: 18px; top: 40px; bottom: -20px; width: 2px; background: var(--bs-border-color);"></div>
            @endif

            <div class="flex-shrink-0">
                <div class="avatar avatar-md rounded-circle d-flex align-items-center justify-content-center
                            {{ in_array($step['state'], $muted) ? 'bg-'.$s['color'].'-subtle text-'.$s['color'].'-emphasis' : 'bg-'.$s['color'].' text-white' }}"
                     style="z-index: 1; position: relative;">
                    <i class="fa-solid {{ $s['icon'] }}"></i>
                </div>
            </div>

            <div class="flex-grow-1">
                <div class="card {{ in_array($step['state'], $muted) ? 'border-secondary-subtle' : 'border-'.$s['color'] }} {{ $step['state'] === 'current' ? 'shadow-sm' : '' }}"
                     @if(in_array($step['state'], $muted)) style="border-style: dashed;" @endif>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h6 class="mb-1 {{ in_array($step['state'], $muted) ? 'text-muted' : '' }}">
                                    {{ $step['title'] }}
                                    <span class="badge bg-{{ $s['color'] }}-subtle text-{{ $s['color'] }}-emphasis ms-2">{{ $s['badge'] }}</span>
                                </h6>

                                @if($step['by'])
                                    <small class="text-muted"><i class="fa-solid fa-user me-1"></i>{{ $step['by'] }}</small>
                                @endif

                                @if(in_array($step['state'], ['current', 'upcoming']) && !empty($step['approvers']))
                                    <small class="text-muted d-block">
                                        <i class="fa-solid fa-user-check me-1"></i>
                                        {{ $step['state'] === 'current' ? 'Waiting for' : 'Will go to' }}:
                                        <span class="{{ $step['state'] === 'current' ? 'fw-semibold text-body' : '' }}">{{ implode(', ', $step['approvers']) }}</span>
                                    </small>
                                @elseif(in_array($step['state'], ['blocked', 'upcoming']))
                                    <small class="text-danger-emphasis d-block">
                                        <i class="fa-solid fa-user-slash me-1"></i>Nobody is assigned to approve this level for {{ ($ownRequest ?? true) ? 'your' : 'this' }} department. Please contact HR.
                                    </small>
                                @endif
                            </div>

                            @if($step['at'])
                                <small class="text-muted text-nowrap" title="{{ $step['at']->format('d M Y, H:i') }}">
                                    <i class="fa-solid fa-clock me-1"></i>{{ $step['at']->diffForHumans() }}
                                </small>
                            @endif
                        </div>

                        @if($step['comments'])
                            <div class="border-top pt-2 mt-2">
                                <small class="text-muted d-block mb-1">
                                    <i class="fa-solid fa-comment me-1"></i>{{ $step['state'] === 'submitted' ? (($ownRequest ?? true) ? 'Your comment' : "Requester's comment") : 'Comments' }}:
                                </small>
                                <p class="mb-0 small">{{ $step['comments'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
