<?php

namespace App\Services\Engineering;

use RuntimeException;

/**
 * A business rule refused the action. The message is safe to show to the user.
 */
class EngineeringActionException extends RuntimeException {}
