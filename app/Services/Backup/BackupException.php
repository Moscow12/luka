<?php

namespace App\Services\Backup;

use RuntimeException;

/** A backup operation was refused or failed; the message is safe to show to an administrator. */
class BackupException extends RuntimeException {}
