<?php

return [
    'secret' => env('JWT_SECRET'),
    'algo' => 'HS256',
    'ttl' => 3600,
    'refresh_ttl' => 30,
    'issuer' => env('APP_URL', 'http://localhost'),
    'audience' => 'your-client-app',
];
