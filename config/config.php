<?php

return [
    'token' => env('RECOVERY_APP_TOKEN', ''),
    'token_temp' => env('RECOVERY_APP_TOKEN_TEMP', '85c50dfd-9511-4f86-a218-f0da01e07452'),

    'captcha' => [
        'enabled' => env('RECOVERY_CAPTCHA_ENABLED', false),
        'sitekey' => env('RECOVERY_CAPTCHA_SITEKEY', ''),
        'secret' => env('RECOVERY_CAPTCHA_SECRET', '')
    ],

    'rate_limit' => [
        'ttl' => env('RECOVERY_RATELIMIT_TTL', 86400),
        'attempts' => env('RECOVERY_RATELIMIT_ATTEMPTS', 10),
    ],
];
