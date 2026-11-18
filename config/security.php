<?php

$reverbHost = env('VITE_REVERB_HOST', env('REVERB_HOST'));
$reverbScheme = env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'https')) === 'https' ? 'wss' : 'ws';
$reverbPort = env('VITE_REVERB_PORT', env('REVERB_PORT'));

return [

    'csp' => [
        // Send a Content-Security-Policy header on web responses
        'enabled' => (bool) env('CSP_ENABLED', true),

        // true = Content-Security-Policy-Report-Only (log violations, block nothing). Use while rolling out.
        'report_only' => (bool) env('CSP_REPORT_ONLY', false),

        // Violations are POSTed here and logged (route: csp.report). Empty to disable reporting.
        'report_uri' => env('CSP_REPORT_URI', '/csp-report'),

        // Websocket origin for Laravel Reverb / Echo
        'reverb_origin' => $reverbHost ? $reverbScheme.'://'.$reverbHost.($reverbPort ? ':'.$reverbPort : '') : null,

        // Extra sources (space-separated), e.g. a CDN or analytics host you add later
        'extra' => [
            'script-src' => env('CSP_EXTRA_SCRIPT_SRC', ''),
            'style-src' => env('CSP_EXTRA_STYLE_SRC', ''),
            'img-src' => env('CSP_EXTRA_IMG_SRC', ''),
            'connect-src' => env('CSP_EXTRA_CONNECT_SRC', ''),
            'font-src' => env('CSP_EXTRA_FONT_SRC', ''),
        ],
    ],

    // Strict-Transport-Security, only sent on HTTPS requests. 0 disables.
    'hsts_max_age' => (int) env('HSTS_MAX_AGE', 31536000),

    'hsts_include_subdomains' => (bool) env('HSTS_INCLUDE_SUBDOMAINS', false),

];
