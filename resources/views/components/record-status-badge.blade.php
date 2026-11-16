@props(['active' => true, 'inUse' => false])

<span class="badge {{ $active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
@if($inUse)
    <span class="badge bg-light text-muted border" title="Used in other records; it can be deactivated but not deleted">In use</span>
@endif
