<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'pulseapi' => [
        'url'               => env('PULSEAPI_URL', 'https://q.pulseapi.dev'),
        'key'               => env('POKEPULSE_API_KEY'),
        'price_ttl_days'    => env('PULSEAPI_PRICE_TTL_DAYS', 5),
        // Default intake cost as a fraction of market value, used to auto-fill "Cost (£)"
        // on Rapid Intake when it's left blank at fetch time.
        'default_cost_ratio' => (float) env('PULSEAPI_DEFAULT_COST_RATIO', 0.9),
    ],

];
