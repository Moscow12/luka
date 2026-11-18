<?php

namespace App\Support;

use Illuminate\Support\Facades\Vite;

class Csp
{
    /**
     * Adds the request's CSP nonce to every <script> tag in trusted, package-generated
     * markup (e.g. ToastMagic::scripts()). Never use it on user-supplied HTML.
     */
    public static function withNonce(string $html): string
    {
        $nonce = Vite::cspNonce();

        if (! $nonce) {
            return $html;
        }

        return (string) preg_replace('/<script\b(?![^>]*\bnonce=)/i', '<script nonce="'.e($nonce).'"', $html);
    }
}
