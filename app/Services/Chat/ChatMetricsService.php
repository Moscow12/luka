<?php

namespace App\Services\Chat;

use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Workspace analytics (PRD 6.2): department activity index, media volume, ticket interconnects.
 */
class ChatMetricsService
{
    public function summary(Carbon $from, Carbon $to): array
    {
        $messages = ChatMessage::where('type', 'text')->whereBetween('created_at', [$from, $to]);

        return [
            'messages' => (clone $messages)->count(),
            'active_users' => (clone $messages)->distinct()->count('user_id'),
            'media_bytes' => (int) ChatAttachment::whereHas('message', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->sum('size'),
            'interconnects' => (int) (clone $messages)->sum('ticket_ref_count'),
            'groups' => ChatConversation::where('type', 'group')->where('status', 'active')->count(),
        ];
    }

    /**
     * AI_d = average over days of (distinct senders in the channel that day ÷ channel members).
     */
    public function departmentActivity(Carbon $from, Carbon $to, ?string $departmentId = null): Collection
    {
        $channels = ChatConversation::where('type', 'department')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->withCount('members')
            ->get(['id', 'name', 'department_id']);

        $daily = ChatMessage::query()
            ->whereIn('conversation_id', $channels->pluck('id'))
            ->where('type', 'text')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('conversation_id, DATE(created_at) as day, COUNT(DISTINCT user_id) as active')
            ->groupBy('conversation_id', 'day')
            ->get()
            ->groupBy('conversation_id');

        $days = max(1, (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1);

        return $channels->map(function (ChatConversation $channel) use ($daily, $days) {
            $rows = $daily->get($channel->id, collect());
            $members = max(1, $channel->members_count);

            return [
                'name' => $channel->name,
                'members' => $channel->members_count,
                'active_days' => $rows->count(),
                'peak_active' => (int) $rows->max('active'),
                'index' => round($rows->sum('active') / $days / $members * 100, 1),
            ];
        })->sortByDesc('index')->values();
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function dailyMessages(Carbon $from, Carbon $to): array
    {
        $counts = ChatMessage::where('type', 'text')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];
        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $labels[] = $day->format('d M');
            $values[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function mediaByType(Carbon $from, Carbon $to): array
    {
        $attachments = ChatAttachment::whereHas('message', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->get(['mime_type', 'size']);

        return [
            'images' => (int) $attachments->filter(fn ($a) => str_starts_with((string) $a->mime_type, 'image/'))->sum('size'),
            'documents' => (int) $attachments->reject(fn ($a) => str_starts_with((string) $a->mime_type, 'image/'))->sum('size'),
            'files' => $attachments->count(),
        ];
    }
}
