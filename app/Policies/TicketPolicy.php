<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\TicketAccess;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        return TicketAccess::canView($user, $ticket);
    }

    public function create(User $user): bool
    {
        return $user->can('create-tickets');
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return TicketAccess::canManage($user, $ticket);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return TicketAccess::canManage($user, $ticket);
    }

    public function viewPrivateNotes(User $user, Ticket $ticket): bool
    {
        return TicketAccess::canViewPrivateNotes($user, $ticket);
    }
}
