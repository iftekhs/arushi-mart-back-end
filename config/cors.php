<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth',
        'resend-otp',
        'logout',
        'check-otp',
        'verify-otp',
        'check-auth-type',
        'verify-password'
    ],

    'allowed_methods' => ['*'],

    // TEMPORARY: Allow all origins for testing
    // WARNING: This breaks authentication! Only use for testing.
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [
        // '/^https?:\/\/(www\.)?arushimart\.com$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // IMPORTANT: Must be false when using wildcard '*'
    // This will break authentication/sessions!
    'supports_credentials' => false,

];
