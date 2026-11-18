<?php

namespace App\Support;

/**
 * Masks credentials before text or structured data is logged or stored.
 *
 * - Array keys that name a secret (password, secret, api_key, token, authorization, ...) are replaced.
 * - In free text, "Password: xyz", "api_key=xyz", "Authorization: Basic xyz" and
 *   "Bearer xyz" keep their label and lose the value.
 * - Exact secret values passed in $known are replaced wherever they appear.
 */
class SecretRedactor
{
    public const MASK = '[REDACTED]';

    /** Whole words (key split on _ - . space and camelCase) that mark a key as secret. */
    private const SENSITIVE_WORDS = ['password', 'passwd', 'passphrase', 'pwd', 'secret', 'apikey', 'token', 'authorization', 'cookie', 'credential', 'credentials', 'otp', 'signature'];

    private const SENSITIVE_PAIRS = ['api_key', 'access_key', 'private_key', 'auth_header'];

    private const TEXT_PATTERNS = [
        // "Authorization: Basic abc" / "Authorization: Bearer abc"
        '/(authorization["\']?\s*[:=]\s*["\']?(?:basic|bearer|digest|token)?\s*)[^\s"\',}]+/i',
        '/(\bbearer\s+)[A-Za-z0-9._~+\/=-]+/i',
        // "Password: abc", "password=abc", "secret_key": "abc", "api-key = abc"
        '/(\b(?:pass(?:word|wd|phrase)?|pwd|secret(?:[_-]?key)?|api[_-]?key|access[_-]?key|token|otp)["\']?\s*[:=]\s*["\']?)[^\s"\',}&]+/i',
    ];

    /** @param  array<int, string|null>  $known */
    public static function text(?string $text, array $known = []): string
    {
        $text = (string) $text;

        foreach ($known as $secret) {
            if (is_string($secret) && strlen($secret) >= 4) {
                $text = str_replace([$secret, base64_encode($secret), rawurlencode($secret)], self::MASK, $text);
            }
        }

        return (string) preg_replace(self::TEXT_PATTERNS, '$1'.self::MASK, $text);
    }

    /** @param  array<int, string|null>  $known */
    public static function data(mixed $value, array $known = [], int $depth = 0): mixed
    {
        if ($depth > 8) {
            return $value;
        }

        if (is_string($value)) {
            return self::text($value, $known);
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = is_string($key) && self::isSensitiveKey($key) && (is_scalar($item) || $item === null)
                ? self::MASK
                : self::data($item, $known, $depth + 1);
        }

        return $value;
    }

    public static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower((string) preg_replace('/(?<=[a-z0-9])(?=[A-Z])/', '_', $key));
        $words = preg_split('/[^a-z0-9]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        if (array_intersect($words, self::SENSITIVE_WORDS) !== []) {
            return true;
        }

        $joined = implode('_', $words);
        foreach (self::SENSITIVE_PAIRS as $pair) {
            if (str_contains('_'.$joined.'_', '_'.$pair.'_')) {
                return true;
            }
        }

        return false;
    }
}
