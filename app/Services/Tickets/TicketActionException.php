<?php

namespace App\Services\Tickets;

use RuntimeException;

/**
 * A ticket action was refused for a business reason; the message is safe to show to the user.
 */
class TicketActionException extends RuntimeException {}
