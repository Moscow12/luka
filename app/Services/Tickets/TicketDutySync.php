<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\EmployeeAssignedDuty;
use App\Models\Ticket;

/**
 * Recalculates a duty's achievement from the tickets linked to it.
 * Uses the same score formula as Myduties::updateProgress(): min(actual / target * 100, 100).
 */
class TicketDutySync
{
    public function sync(?EmployeeAssignedDuty $duty): void
    {
        // Reviewed or cancelled duties are frozen
        if (! $duty || $duty->reviewed_at || $duty->status === 'cancelled') {
            return;
        }

        $tickets = Ticket::where('employee_assigned_duty_id', $duty->id)
            ->where('status', '!=', TicketStatus::Cancelled->value)
            ->get(['id', 'status', 'due_date', 'resolved_at', 'closed_at', 'quality_rating']);

        if ($tickets->isEmpty()) {
            return;
        }

        $closed = $tickets->filter(fn (Ticket $t) => $t->status === TicketStatus::Closed);
        $allClosed = $closed->count() === $tickets->count();
        $target = $duty->target_value !== null ? (float) $duty->target_value : null;

        switch ($duty->measurement_type) {
            case 'percentage':
                $onTime = $closed->filter(fn (Ticket $t) => static::closedOnTime($t))->count();
                $actual = round($onTime / $tickets->count() * 100, 2);
                $score = $target > 0 ? min($actual / $target * 100, 100) : $actual;
                $completed = $allClosed;
                break;

            case 'rating_scale':
                $scaleMax = $duty->rating_scale_max ?: 5;
                $actual = $closed->isEmpty() ? 0 : round($closed->avg('quality_rating') / 5 * $scaleMax, 2);
                $denominator = $target > 0 ? $target : $scaleMax;
                $score = min($actual / $denominator * 100, 100);
                $completed = $allClosed;
                break;

            case 'boolean':
                $actual = $allClosed ? 1 : 0;
                $score = $actual * 100;
                $completed = $allClosed;
                break;

            default: // numeric: count of closed tickets
                $actual = $closed->count();
                $score = $target > 0 ? min($actual / $target * 100, 100) : null;
                $completed = $target > 0 ? $actual >= $target : $allClosed;
        }

        $duty->update([
            'actual_achievement' => $actual,
            'score' => $score !== null ? round($score, 2) : null,
            'status' => $completed ? 'completed' : 'in_progress',
            'achievement_notes' => sprintf(
                'Auto-calculated from %d linked ticket(s): %d closed.',
                $tickets->count(),
                $closed->count()
            ),
        ]);
    }

    public static function closedOnTime(Ticket $ticket): bool
    {
        $finishedAt = $ticket->resolved_at ?? $ticket->closed_at;

        return $finishedAt && $ticket->due_date && $finishedAt->lte($ticket->due_date->copy()->endOfDay());
    }
}
