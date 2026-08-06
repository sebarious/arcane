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

    // Used by Rapid Intake's live camera scanner to OCR a card's set number
    // straight off a captured frame — see App\Services\Vision\GoogleVisionClient.
    // Plain API key, not a service account — this org's Cloud org policy blocks
    // downloadable service account keys (iam.disableServiceAccountKeyCreation),
    // but that doesn't affect a project-level API key, which is a separate credential type.
    'google_vision' => [
        'key' => env('GOOGLE_VISION_API_KEY'),
        // Logs each frame's raw OCR text plus the extracted number/set code —
        // toggle on only while diagnosing a scan format that isn't matching
        // (e.g. an unfamiliar promo layout), then back off; a live scan session
        // fires this every ~1.5s and the log line is per-frame.
        'debug_ocr' => env('RAPID_INTAKE_DEBUG_OCR', false),
    ],

];
