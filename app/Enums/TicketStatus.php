<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case PendingResponse = 'pending_response';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';
    case Cancelled = 'cancelled';

    // Who may move a ticket INTO a given status (PRD state matrix)
    public const ACTOR_ASSIGNEE = 'assignee';

    public const ACTOR_MANAGER = 'manager';     // creator, department supervisor, admin

    public const ACTOR_PARTICIPANT = 'participant';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::Accepted => 'Accepted',
            self::InProgress => 'In Progress',
            self::PendingResponse => 'Pending Response',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-secondary',
            self::Assigned => 'bg-info',
            self::Accepted => 'bg-primary',
            self::InProgress => 'bg-warning text-dark',
            self::PendingResponse => 'bg-light text-dark border',
            self::Resolved => 'bg-success',
            self::Closed => 'bg-dark',
            self::Reopened => 'bg-danger',
            self::Cancelled => 'bg-secondary opacity-75',
        };
    }

    /**
     * Statuses this status may move to.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::Assigned, self::Cancelled],
            self::Assigned => [self::Accepted, self::Cancelled],
            self::Accepted => [self::InProgress, self::PendingResponse, self::Resolved, self::Cancelled],
            self::InProgress => [self::PendingResponse, self::Resolved, self::Cancelled],
            self::PendingResponse => [self::InProgress, self::Resolved, self::Cancelled],
            self::Resolved => [self::Closed, self::Reopened],
            self::Reopened => [self::Accepted, self::InProgress, self::PendingResponse, self::Resolved, self::Cancelled],
            self::Closed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Actor groups allowed to move a ticket into this status.
     *
     * @return array<int, string>
     */
    public function permittedActors(): array
    {
        return match ($this) {
            self::Accepted, self::InProgress, self::Resolved => [self::ACTOR_ASSIGNEE],
            self::PendingResponse => [self::ACTOR_ASSIGNEE, self::ACTOR_MANAGER, self::ACTOR_PARTICIPANT],
            self::Assigned, self::Closed, self::Reopened, self::Cancelled => [self::ACTOR_MANAGER],
            self::Open => [],
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed, self::Cancelled], true);
    }

    /**
     * Statuses counted as the live pipeline.
     *
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return [self::Open->value, self::Assigned->value, self::Accepted->value, self::InProgress->value, self::PendingResponse->value, self::Reopened->value];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
