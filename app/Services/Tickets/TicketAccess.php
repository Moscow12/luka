<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Employee;
use App\Models\Ticket;
use App\Models\User;
use App\Support\DepartmentSupervision;
use Illuminate\Database\Eloquent\Builder;

/**
 * Central answer to "what may this user do with this ticket?".
 */
class TicketAccess
{
    public static function employeeFor(User $user): ?Employee
    {
        return Employee::where('user_id', $user->id)->first();
    }

    public static function isAdmin(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view-all-tickets');
    }

    /**
     * Departments whose ticket queue this user supervises: their own department when they
     * hold manage-department-tickets, plus any department whose supervisor title they hold.
     *
     * @return array<int, string>
     */
    public static function supervisedDepartmentIds(User $user): array
    {
        return DepartmentSupervision::departmentIdsFor($user);
    }

    public static function isSupervisorOf(User $user, Ticket $ticket): bool
    {
        return in_array($ticket->department_id, static::supervisedDepartmentIds($user), true);
    }

    public static function isCreator(User $user, Ticket $ticket): bool
    {
        return $ticket->created_by === $user->id;
    }

    public static function isAssignee(User $user, Ticket $ticket): bool
    {
        $employee = static::employeeFor($user);

        return $employee && $ticket->assignee_employee_id === $employee->id;
    }

    public static function participantRole(User $user, Ticket $ticket): ?string
    {
        return $ticket->participants()->where('user_id', $user->id)->value('role');
    }

    /** Creator, department supervisor or admin. */
    public static function canManage(User $user, Ticket $ticket): bool
    {
        return static::isCreator($user, $ticket) || static::isSupervisorOf($user, $ticket) || static::isAdmin($user);
    }

    public static function canView(User $user, Ticket $ticket): bool
    {
        if (static::canManage($user, $ticket) || static::isAssignee($user, $ticket)) {
            return true;
        }

        if (static::participantRole($user, $ticket) !== null) {
            return true;
        }

        if ($ticket->visibility === 'department') {
            $employee = static::employeeFor($user);

            return $employee && $employee->department_id === $ticket->department_id;
        }

        return false;
    }

    /** Private notes are hidden from the creator unless they also supervise or administer. */
    public static function canViewPrivateNotes(User $user, Ticket $ticket): bool
    {
        if (static::isAssignee($user, $ticket) || static::isSupervisorOf($user, $ticket) || static::isAdmin($user)) {
            return true;
        }

        return ! static::isCreator($user, $ticket)
            && static::participantRole($user, $ticket) === 'collaborator';
    }

    /**
     * Actor groups (TicketStatus::ACTOR_*) the user belongs to for this ticket.
     *
     * @return array<int, string>
     */
    public static function actorGroups(User $user, Ticket $ticket): array
    {
        $groups = [];

        if (static::isAssignee($user, $ticket)) {
            $groups[] = TicketStatus::ACTOR_ASSIGNEE;
        }

        if (static::canManage($user, $ticket)) {
            $groups[] = TicketStatus::ACTOR_MANAGER;
        }

        if (static::participantRole($user, $ticket) !== null) {
            $groups[] = TicketStatus::ACTOR_PARTICIPANT;
        }

        return $groups;
    }

    public static function canMoveTo(User $user, Ticket $ticket, TicketStatus $target): bool
    {
        return $ticket->status->canTransitionTo($target)
            && array_intersect($target->permittedActors(), static::actorGroups($user, $ticket)) !== [];
    }

    /**
     * Statuses the user can move this ticket to right now.
     *
     * @return array<int, TicketStatus>
     */
    public static function availableTransitions(User $user, Ticket $ticket): array
    {
        return array_values(array_filter(
            $ticket->status->allowedTransitions(),
            fn (TicketStatus $target) => $target !== TicketStatus::Assigned && static::canMoveTo($user, $ticket, $target)
        ));
    }

    /** Restrict a ticket query to the rows this user may see. */
    public static function scopeVisible(Builder $query, User $user): Builder
    {
        if (static::isAdmin($user)) {
            return $query;
        }

        $employee = static::employeeFor($user);
        $supervised = static::supervisedDepartmentIds($user);

        return $query->where(function (Builder $q) use ($user, $employee, $supervised) {
            $q->where('created_by', $user->id)
                ->orWhereHas('participants', fn (Builder $p) => $p->where('user_id', $user->id));

            if ($employee) {
                $q->orWhere('assignee_employee_id', $employee->id)
                    ->orWhere(fn (Builder $d) => $d->where('visibility', 'department')->where('department_id', $employee->department_id));
            }

            if ($supervised !== []) {
                $q->orWhereIn('department_id', $supervised);
            }
        });
    }
}
