<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers and Content-Security-Policy for web responses.
 *
 * script-src uses a per-request nonce (applied to Vite, Livewire and inline <script>
 * tags through Vite::cspNonce()) and no 'unsafe-inline', so injected <script> tags,
 * inline on* handlers and javascript: URLs are blocked. 'unsafe-eval' is required by
 * Livewire 3 / Alpine, which evaluate wire:* and x-* expressions with new Function();
 * those expressions must therefore never be built from unescaped data (use Js::from()).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Must be set before views render so Vite/Livewire/inline scripts get the nonce
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $headers = $response->headers;
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');
        $headers->remove('X-Powered-By');

        if (! $headers->has('Referrer-Policy')) {
            $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        $maxAge = (int) config('security.hsts_max_age', 0);
        if ($maxAge > 0 && $request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age='.$maxAge.(config('security.hsts_include_subdomains') ? '; includeSubDomains' : ''));
        }

        if (config('security.csp.enabled', true)) {
            $name = config('security.csp.report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $headers->set($name, $this->policy($nonce, $request));
        }

        return $response;
    }

    public function policy(string $nonce, Request $request): string
    {
        $dev = $this->viteDevOrigins();

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-{$nonce}'", "'unsafe-eval'", ...$dev['http']],
            'style-src' => ["'self'", "'unsafe-inline'", ...$dev['http']],
            'img-src' => ["'self'", 'data:', 'blob:'],
            'font-src' => ["'self'", 'data:'],
            'connect-src' => ["'self'", ...array_filter([config('security.csp.reverb_origin')]), ...$dev['http'], ...$dev['ws']],
            'media-src' => ["'self'", 'blob:'],
            'worker-src' => ["'self'", 'blob:'],
            'frame-src' => ["'self'"],
            'manifest-src' => ["'self'"],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
        ];

        foreach ((array) config('security.csp.extra', []) as $directive => $sources) {
            foreach (preg_split('/\s+/', (string) $sources, -1, PREG_SPLIT_NO_EMPTY) as $source) {
                $directives[$directive][] = $source;
            }
        }

        $parts = [];
        foreach ($directives as $directive => $sources) {
            $parts[] = $directive.' '.implode(' ', array_unique($sources));
        }

        if ($request->isSecure() && ! config('security.csp.report_only')) {
            $parts[] = 'upgrade-insecure-requests';
        }

        if ($reportUri = config('security.csp.report_uri')) {
            $parts[] = 'report-uri '.$reportUri;
        }

        return implode('; ', $parts);
    }

    /** Origins of the Vite dev server while `npm run dev` is running (public/hot exists). */
    private function viteDevOrigins(): array
    {
        if (! Vite::isRunningHot()) {
            return ['http' => [], 'ws' => []];
        }

        $url = trim((string) @file_get_contents(public_path('hot')));
        $parts = parse_url($url);

        if (empty($parts['host'])) {
            return ['http' => [], 'ws' => []];
        }

        $host = (str_contains($parts['host'], ':') ? '['.$parts['host'].']' : $parts['host']).(isset($parts['port']) ? ':'.$parts['port'] : '');
        $secure = ($parts['scheme'] ?? 'http') === 'https';

        return [
            'http' => [($secure ? 'https://' : 'http://').$host],
            'ws' => [($secure ? 'wss://' : 'ws://').$host],
        ];
    }
}
