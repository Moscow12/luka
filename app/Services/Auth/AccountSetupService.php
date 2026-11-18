<?php

namespace App\Services\Auth;

use App\Models\AccountSetupToken;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Notifications\AccountSetupLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * One-time setup links for new accounts, sent instead of passwords.
 *
 * Only a SHA-256 hash of the token is stored. A link is single-use, expires after
 * auth.account_setup_expire_hours, and issuing a new link revokes older unused ones.
 * Links are built from APP_URL, never from the request's Host header.
 */
class AccountSetupService
{
    public const TOKEN_LENGTH = 48;

    public const SESSION_KEY = 'account_setup_token';

    /** Random password for accounts that will be activated through a setup link (never disclosed). */
    public static function unusablePassword(): string
    {
        return Hash::make(Str::password(64));
    }

    /** Creates a fresh link for the user and returns its URL. */
    public function issue(User $user, ?User $issuedBy = null): string
    {
        $token = Str::random(self::TOKEN_LENGTH);

        DB::transaction(function () use ($user, $issuedBy, $token) {
            AccountSetupToken::where('user_id', $user->id)->whereNull('used_at')->delete();

            AccountSetupToken::create([
                'user_id' => $user->id,
                'token_hash' => self::hash($token),
                'expires_at' => now()->addHours(max(1, (int) config('auth.account_setup_expire_hours', 72))),
                'created_by' => $issuedBy?->id,
            ]);
        });

        return self::url($token);
    }

    public static function url(string $token): string
    {
        return rtrim((string) config('app.url'), '/').route('account.setup.link', ['token' => $token], false);
    }

    public static function isWellFormed(string $token): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9]{'.self::TOKEN_LENGTH.'}$/', $token);
    }

    public function findValid(?string $token): ?AccountSetupToken
    {
        if (! is_string($token) || ! self::isWellFormed($token)) {
            return null;
        }

        return AccountSetupToken::with('user')
            ->where('token_hash', self::hash($token))
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    /** Sets the password and burns the link. Returns null if the link is invalid, expired or already used. */
    public function complete(?string $token, string $password): ?User
    {
        $record = $this->findValid($token);

        if (! $record || ! $record->user) {
            return null;
        }

        return DB::transaction(function () use ($record, $password) {
            // Atomic: only one concurrent request can consume the link
            $consumed = AccountSetupToken::whereKey($record->id)->whereNull('used_at')->update(['used_at' => now()]);

            if ($consumed !== 1) {
                return null;
            }

            $user = $record->user;
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            AccountSetupToken::where('user_id', $user->id)->whereNull('used_at')->delete();
            TrustedDevice::where('user_id', $user->id)->update(['is_active' => false]);

            return $user;
        });
    }

    public static function smsMessage(string $firstName, string $username, string $url): string
    {
        $hours = (int) config('auth.account_setup_expire_hours', 72);

        return "Dear {$firstName},\n"
            ."Your HRP account is ready. Username: {$username}\n"
            ."Set your password here (one-time link, valid {$hours}h):\n"
            .$url;
    }

    /** Sends the link by SMS. Returns true when the gateway accepted it. */
    public function sendSms(User $user, string $phone, string $url, ?string $firstName = null): bool
    {
        $result = send_sms($phone, self::smsMessage($firstName ?? $user->first_name ?? 'Staff', $user->username, $url));

        if (empty($result['success'])) {
            Log::warning('Account setup SMS failed', ['user_id' => $user->id, 'error' => $result['message'] ?? null]);

            return false;
        }

        return true;
    }

    /** Sends the link by e-mail. Returns true on success. */
    public function sendEmail(User $user, string $url, ?string $firstName = null): bool
    {
        if (blank($user->email)) {
            return false;
        }

        try {
            $user->notify(new AccountSetupLink($url, $user->username, $firstName ?? $user->first_name ?? 'Staff'));

            return true;
        } catch (\Throwable $e) {
            Log::error('Account setup e-mail failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
