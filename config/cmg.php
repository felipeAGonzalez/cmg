<?php

return [
    'url' => env('CMG_URL', ''),
    'delegated_auth' => [
        'secret' => env('CMG_DELEGATED_AUTH_SECRET', ''),
        'ttl' => (int) env('CMG_DELEGATED_AUTH_TTL', 60),
        'audience' => 'cmg-warehouse',
    ],
];
