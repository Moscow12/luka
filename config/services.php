<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    | Shared-secret token guarding the third-party sync API endpoints
    | (employees / leaves / assets). Requests must present this token via
    | an Authorization: Bearer header, an X-Export-Token header, or a
    | "token" field in the request body. If unset, the guarded endpoints
    | fail closed (HTTP 503).
    */
    'api' => [
        'sync_token' => env('EXPORT_API_TOKEN'),
    ],

    /*
    | External Python service that talks to the fingerprint devices (ZKTeco /
    | Anviz) on the LAN. HRP never runs device code itself; it calls this API
    | with a bearer token. Device credentials travel only in the JSON request
    | body, so use an https:// URL whenever the service is not on localhost.
    | If the URL or token is unset, device actions fail closed.
    */
    'attendance_bridge' => [
        'url' => env('ATTENDANCE_BRIDGE_URL'),
        'token' => env('ATTENDANCE_BRIDGE_TOKEN'),
        'timeout' => (int) env('ATTENDANCE_BRIDGE_TIMEOUT', 200),
        // TLS certificates are always verified. For an internal CA, point this at its PEM bundle.
        'ca_bundle' => env('ATTENDANCE_BRIDGE_CA_BUNDLE'),
    ],

];
