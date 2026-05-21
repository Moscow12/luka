<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared-secret guard for the third-party sync API endpoints
 * (employees / leaves / assets).
 *
 * Mirrors the partner system's export_guard(): a server-side token is
 * required, supplied as either an `Authorization: Bearer <token>` header,
 * an `X-Export-Token` header, or a `token` field in the request body, and
 * is compared in constant time. If no token is configured server-side the
 * request fails closed rather than failing open.
 */
class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.api.sync_token');

        // No server-side secret configured: fail closed.
        if (empty($expected)) {
            Log::error('Sync API token not configured; rejecting request.', [
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'TOKEN_NOT_CONFIGURED',
                'message' => 'Sync API token is not configured on the server.',
            ], 503);
        }

        $provided = $this->providedToken($request);

        if (! is_string($provided) || $provided === '' || ! hash_equals($expected, $provided)) {
            Log::warning('Sync API request rejected: invalid or missing token.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'UNAUTHORIZED',
                'message' => 'Unauthorized: invalid or missing API token.',
            ], 401);
        }

        return $next($request);
    }

    /**
     * Extract the token from (in order) the Bearer header, the
     * X-Export-Token header, or a "token" field in the request body.
     */
    private function providedToken(Request $request): string
    {
        if ($bearer = $request->bearerToken()) {
            return trim($bearer);
        }

        if ($header = $request->header('X-Export-Token')) {
            return trim($header);
        }

        if (is_string($token = $request->input('token')) && $token !== '') {
            return trim($token);
        }

        return '';
    }
}
