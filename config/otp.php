<?php

return [
    'length' => env('OTP_LENGTH', 6),

    'ttl_minutes' => env('OTP_TTL_MINUTES', 10),

    'verify_max' => env('OTP_VERIFY_MAX', 5),
];
