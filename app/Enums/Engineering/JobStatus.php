<?php

namespace App\Enums\Engineering;

enum JobStatus: string
{
    case Submitted = 'submitted';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case AwaitingSpares = 'awaiting_spares';
    case AssistanceNeeded = 'assistance_needed';
    case Rework = 'rework';
    case Completed = 'completed';
    case Closed = 'closed';
    case BeyondRepair = 'beyond_repair';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::AwaitingSpares => 'Awaiting spares',
            self::AssistanceNeeded => 'Assistance needed',
            self::Rework => 'Re-work',
            self::Completed => 'Completed',
            self::Closed => 'Closed',
            self::BeyondRepair => 'Beyond repair',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'bg-secondary',
            self::Assigned => 'bg-info',
            self::InProgress => 'bg-primary',
            self::AwaitingSpares => 'bg-warning text-dark',
            self::AssistanceNeeded => 'bg-warning text-dark',
            self::Rework => 'bg-danger',
            self::Completed => 'bg-success',
            self::Closed => 'bg-dark',
            self::BeyondRepair => 'bg-dark',
        };
    }

    /** Still waiting for engineering: everything before completion. */
    public function isOpen(): bool
    {
        return in_array($this->value, self::openValues(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function openValues(): array
    {
        return [...self::workableValues(), self::Submitted->value];
    }

    /**
     * Open and sitting with an engineer.
     *
     * @return array<int, string>
     */
    public static function workableValues(): array
    {
        return [
            self::Assigned->value, self::InProgress->value, self::AwaitingSpares->value,
            self::AssistanceNeeded->value, self::Rework->value,
        ];
    }

    /**
     * The engineer has signed the job out; the TAT clock has stopped.
     *
     * @return array<int, string>
     */
    public static function finishedValues(): array
    {
        return [self::Completed->value, self::Closed->value, self::BeyondRepair->value];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
