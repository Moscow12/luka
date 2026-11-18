<?php

namespace App\Logging;

use App\Support\SecretRedactor;
use Illuminate\Log\Logger;
use Monolog\Logger as Monolog;
use Monolog\LogRecord;

/**
 * Logging tap: masks credentials in every record's message, context and extra
 * before any handler writes it. Registered on the file/stream channels in
 * config/logging.php.
 */
class RedactSecrets
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if (! $monolog instanceof Monolog) {
            return;
        }

        $monolog->pushProcessor(function (LogRecord $record): LogRecord {
            return $record->with(
                message: SecretRedactor::text($record->message),
                context: SecretRedactor::data($record->context),
                extra: SecretRedactor::data($record->extra),
            );
        });
    }
}
