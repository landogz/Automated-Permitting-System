<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Authenticated APICS APIs must not use wildcard origins. Pin to APP_URL
    | (and optional extra hosts via CORS_ALLOWED_ORIGINS comma list).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        static fn (string $origin): string => rtrim(trim($origin), '/'),
        array_merge(
            [env('APP_URL', 'http://localhost')],
            array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))),
        ),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
