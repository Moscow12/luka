@props(['colspan' => 1, 'icon' => 'fa-inbox', 'text' => 'Nothing to show.'])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5 text-muted">
        <i class="fa-solid {{ $icon }} fa-2x mb-2 opacity-50"></i>
        <p class="mb-0">{{ $text }}</p>
    </td>
</tr>
