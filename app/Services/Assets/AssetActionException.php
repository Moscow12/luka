<?php

namespace App\Services\Assets;

use RuntimeException;

/**
 * A business rule refused the action. The message is safe to show to the user.
 */
class AssetActionException extends RuntimeException {}
