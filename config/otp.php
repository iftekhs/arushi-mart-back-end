<?php

return [
    'length' => env('OTP_LENGTH', 6),

    'ttl_minutes' => env('OTP_TTL_MINUTES', 10),

    'request_max' => env('OTP_REQUEST_MAX', 6),
    'request_decay_seconds' => env('OTP_REQUEST_DECAY', 300),

    'verify_max' => env('OTP_VERIFY_MAX', 5),
    'verify_decay_seconds' => env('OTP_VERIFY_DECAY', 300),
];
