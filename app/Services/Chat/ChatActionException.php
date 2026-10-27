<?php

namespace App\Services\Chat;

use RuntimeException;

/** A chat action was refused for a business reason; the message is safe to show to the user. */
class ChatActionException extends RuntimeException {}
