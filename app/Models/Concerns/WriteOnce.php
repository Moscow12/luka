<?php

namespace App\Models\Concerns;

use LogicException;

/**
 * Makes a model append-only: rows can be inserted but never changed or removed.
 */
trait WriteOnce
{
    public static function bootWriteOnce(): void
    {
        static::updating(function () {
            throw new LogicException(static::class.' records are immutable.');
        });

        static::deleting(function () {
            throw new LogicException(static::class.' records are immutable.');
        });
    }
}
