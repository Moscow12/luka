<?php

namespace App\Support;

/** Session-backed record of the last administrator password re-entry. */
class AdminPasswordConfirmation
{
    public const SESSION_KEY = 'auth.admin_password_confirmed_at';

    public static function record(): void
    {
        session()->put(self::SESSION_KEY, time());
    }

    public static function isFresh(): bool
    {
        $confirmedAt = (int) session(self::SESSION_KEY, 0);

        return $confirmedAt > 0 && (time() - $confirmedAt) < (int) config('auth.admin_confirmation_timeout', 900);
    }
}
