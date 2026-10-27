@props(['user' => null, 'size' => 36, 'presence' => false])
@php
    $name = $user?->full_name ?: 'Former member';
    $initials = $user ? strtoupper(mb_substr($user->first_name ?? '', 0, 1).mb_substr($user->surname ?? '', 0, 1)) : '?';
    $photo = $user?->profile_picture_url;
@endphp
<span {{ $attributes->merge(['class' => 'chat-avatar']) }} style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ round($size * 0.38) }}px;" title="{{ $name }}">
    @if($photo)
        <img src="{{ $photo }}" alt="" loading="lazy">
    @else
        {{ $initials ?: '?' }}
    @endif
    @if($presence && $user)
        <span class="chat-presence" x-data :class="$store.presence.status('{{ $user->id }}')" :title="$store.presence.label('{{ $user->id }}')"></span>
    @endif
</span>
