<?php

return [
    'secret' => env('JWT_SECRET'),
    'algo' => 'HS256',
    'ttl' => 2592000, // access token TTL (seconds)
    'refresh_ttl' => 2592000, // refresh token TTL (30 days)
    'issuer' => env('APP_URL', 'http://localhost'),
    'audience' => 'your-client-app',
];
