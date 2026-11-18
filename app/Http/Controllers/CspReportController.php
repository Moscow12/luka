<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/** Receives browser Content-Security-Policy violation reports and logs a trimmed summary. */
class CspReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = json_decode(substr((string) $request->getContent(), 0, 8192), true);
        $report = is_array($payload) ? ($payload['csp-report'] ?? $payload) : [];

        if (is_array($report)) {
            $clip = fn ($value) => is_scalar($value) ? mb_substr((string) $value, 0, 300) : null;

            Log::warning('CSP violation', [
                // Path only: query strings can carry tokens
                'document' => $clip(parse_url((string) ($report['document-uri'] ?? ''), PHP_URL_PATH)),
                'directive' => $clip($report['effective-directive'] ?? $report['violated-directive'] ?? null),
                'blocked' => $clip(strtok((string) ($report['blocked-uri'] ?? ''), '?')),
                'source' => $clip(strtok((string) ($report['source-file'] ?? ''), '?')),
                'line' => $clip($report['line-number'] ?? null),
                'sample' => $clip($report['script-sample'] ?? null),
                'disposition' => $clip($report['disposition'] ?? null),
            ]);
        }

        return response()->noContent();
    }
}
