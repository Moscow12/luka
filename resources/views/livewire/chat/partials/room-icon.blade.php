{{-- Icon/avatar for a conversation row or header. Expects $conversation and $size. --}}
@if($conversation->isDirect())
    <x-chat.avatar :user="$conversation->otherUser(auth()->id())" :size="$size ?? 36" :presence="true" />
@else
    <span class="chat-room-icon" style="width: {{ $size ?? 36 }}px; height: {{ $size ?? 36 }}px;">
        <i class="fa-solid {{ $conversation->isDepartment() ? 'fa-building' : 'fa-users' }}"></i>
    </span>
@endif
