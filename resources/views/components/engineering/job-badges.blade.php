@props(['job', 'status' => true])

{{-- Priority, status and the permanent RE-WORK marker of a job --}}
@if($job->requisition->isEmergency())
    <span class="badge bg-danger"><i class="fa-solid fa-bolt me-1"></i>Emergency</span>
@endif
@if($status)
    <span class="badge {{ $job->status->badgeClass() }}">{{ $job->status->label() }}</span>
@endif
@if($job->is_rework)
    <span class="badge bg-danger">RE-WORK{{ $job->rework_count > 1 ? ' ×'.$job->rework_count : '' }}</span>
@endif
