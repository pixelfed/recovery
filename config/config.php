<?php

return [
    'token' => env('RECOVERY_APP_TOKEN', '069fc8a0-8e5a-417b-9cfa-0e97db1cbc73'),

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
