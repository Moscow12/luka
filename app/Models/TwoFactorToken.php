<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

class TwoFactorToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }

    /**
     * @throws RandomException
     */
    public static function createForEmail(string $email): self
    {
        self::where('email', $email)->delete();

        return self::create([
            'email' => $email,
            'token' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function markAsUsed(): bool
    {
        // Atomic: only one concurrent request can flip used=false -> true
        $result = self::whereKey($this->getKey())
            ->where('used', false)
            ->update([
                'used' => true,
                'updated_at' => now(),
            ]);

        if ($result > 0) {
            $this->used = true;

            return true;
        }

        return false;
    }

    public static function findValidToken(string $email, string $token): ?self
    {
        $record = self::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // Constant-time comparison instead of matching the code in SQL
        return $record && hash_equals((string) $record->token, $token) ? $record : null;
    }

    /** Burns every outstanding code for the address (e.g. after too many wrong attempts). */
    public static function invalidateForEmail(string $email): void
    {
        self::where('email', $email)->where('used', false)->update(['used' => true, 'updated_at' => now()]);
    }
}
