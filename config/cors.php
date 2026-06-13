<?php

$frontend = rtrim((string) env('FRONTEND_URL', ''), '/');

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_unique([
        $frontend,
        $frontend ? preg_replace('#^https://www\.#', 'https://', $frontend) : null,
        $frontend ? preg_replace('#^https://#', 'https://www.', $frontend) : null,
        'http://localhost:8000',
        'http://localhost:8010',
        'http://127.0.0.1:8000',
        'http://127.0.0.1:8010',
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
