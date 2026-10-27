<?php

return [
    // Edit/delete window for your own messages (PRD 4.1)
    'edit_window_minutes' => (int) env('CHAT_EDIT_WINDOW_MINUTES', 15),

    // Maximum members in an ad-hoc group (PRD 2.2)
    'group_member_cap' => (int) env('CHAT_GROUP_MEMBER_CAP', 250),

    // Messages loaded per scroll page (PRD 5.2)
    'page_size' => 50,

    // Messages and files older than this are pruned by chat:prune (PRD 7)
    'retention_years' => (int) env('CHAT_RETENTION_YEARS', 7),

    // Directory search (PRD 3.1)
    'search_min_chars' => 3,
    'search_limit' => 15,

    // Voice/video (phase 2): STUN plus a coturn TURN relay using short-lived credentials
    'ice' => [
        'stun_urls' => array_filter(explode(',', env('CHAT_STUN_URLS', 'stun:stun.l.google.com:19302'))),
        'turn_urls' => array_filter(explode(',', env('CHAT_TURN_URLS', ''))),
        'turn_secret' => env('CHAT_TURN_SECRET'),
        'turn_ttl' => (int) env('CHAT_TURN_TTL', 3600),
    ],
];
