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

    'pagofacil' => [
        'api_url' => env('PAGOFACIL_API_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr'),
        'query_url' => env('PAGOFACIL_QUERY_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction'),
        'api_token' => env('PAGOFACIL_API_TOKEN'),
        'client_code_prefix' => env('PAGOFACIL_CLIENT_CODE_PREFIX', 'Grupo04SA'),
        'callback_url' => env('PAGOFACIL_CALLBACK_URL', 'https://tu-dominio.com/api/pagofacil/callback'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'USD'),
        'bob_to_usd_rate' => env('STRIPE_BOB_TO_USD_RATE', 0.145),
    ],

];
