<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Rate limits for the guest authentication flows (login, forgot password,
 * reset code, 2FA). Every Livewire action is a public HTTP endpoint, so the
 * limits are enforced inside the component actions.
 *
 * Limits are given as [key => [maxAttempts, decaySeconds]].
 */
class AuthThrottle
{
    public const LOGIN_PER_ACCOUNT = [5, 60];          // per login identifier + IP

    public const LOGIN_PER_IP = [20, 60];              // credential stuffing across accounts

    public const RESET_REQUEST_PER_EMAIL = [3, 900];   // reset e-mails per address

    public const RESET_REQUEST_PER_IP = [10, 900];

    public const RESET_VERIFY_PER_EMAIL = [5, 900];    // wrong codes before the code is burned

    public const RESET_VERIFY_PER_IP = [20, 900];

    public const TWO_FACTOR_VERIFY_PER_EMAIL = [5, 600];

    public const TWO_FACTOR_VERIFY_PER_IP = [20, 600];

    public const TWO_FACTOR_SEND_PER_EMAIL = [3, 600]; // includes the automatic send on page load

    public static function key(string $scope, string ...$parts): string
    {
        return 'auth:'.$scope.':'.implode('|', array_map(fn ($p) => Str::lower(trim($p)), $parts));
    }

    /** Seconds until the first exhausted limit resets, or null when none is exhausted. */
    public static function blockedFor(array $limits): ?int
    {
        foreach ($limits as $key => [$max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return max(1, RateLimiter::availableIn($key));
            }
        }

        return null;
    }

    /** @throws ValidationException when any limit is exhausted */
    public static function ensureNotBlocked(array $limits, string $field): void
    {
        $seconds = self::blockedFor($limits);

        if ($seconds !== null) {
            throw ValidationException::withMessages([
                $field => __('Too many attempts. Please try again in :time.', ['time' => self::humanize($seconds)]),
            ]);
        }
    }

    public static function hit(array $limits): void
    {
        foreach ($limits as $key => [, $decay]) {
            RateLimiter::hit($key, $decay);
        }
    }

    public static function clear(string ...$keys): void
    {
        foreach ($keys as $key) {
            RateLimiter::clear($key);
        }
    }

    /** True once the key has used up its attempts (checked after a hit). */
    public static function exhausted(string $key, int $max): bool
    {
        return RateLimiter::tooManyAttempts($key, $max);
    }

    private static function humanize(int $seconds): string
    {
        return $seconds < 60 ? $seconds.' seconds' : (int) ceil($seconds / 60).' minute(s)';
    }
}
