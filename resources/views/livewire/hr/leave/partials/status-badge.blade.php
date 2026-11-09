{{-- Leave status badge. Stored statuses vary in case ("Approved" / "approved"), so they are matched case-insensitively. Expects: $status --}}
@php
    $look = [
        'awaiting' => ['label' => 'Awaiting approval', 'color' => 'warning'],
        'pending'  => ['label' => 'Awaiting approval', 'color' => 'warning'],
        'active'   => ['label' => 'In progress',       'color' => 'info'],
        'approved' => ['label' => 'Approved',          'color' => 'success'],
        'rejected' => ['label' => 'Rejected',          'color' => 'danger'],
    ][strtolower((string) $status)] ?? ['label' => ucfirst((string) $status), 'color' => 'secondary'];
@endphp
<span class="badge bg-{{ $look['color'] }}">{{ $look['label'] }}</span>
