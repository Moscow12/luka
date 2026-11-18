<?php

return [

    /*
    | Hosts the SMS gateway URLs may point to. A URL is accepted when its host
    | equals an entry or is a subdomain of it (e.g. "beem.africa" allows
    | "apisms.beem.africa"). Only https on port 443 is allowed, and the host
    | must resolve to public IP addresses. Comma-separated in the env.
    */
    'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('SMS_ALLOWED_HOSTS', 'beem.africa'))))),

    'timeout' => (int) env('SMS_TIMEOUT', 30),

    'connect_timeout' => (int) env('SMS_CONNECT_TIMEOUT', 10),

];
