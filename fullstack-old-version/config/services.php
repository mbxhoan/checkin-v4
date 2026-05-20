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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'n8n_chatbot' => [
        'enabled' => env('N8N_CHATBOT_ENABLED', true),
        'webhook_url' => env('N8N_CHATBOT_WEBHOOK_URL'),
        'auth' => env('N8N_CHATBOT_AUTH'),
    ],

    'direct_print' => [
        'enabled' => env('DIRECT_PRINT_ENABLED', false),
        'provider' => env('DIRECT_PRINT_PROVIDER', 'browser'),
        'fallback_to_browser' => env('DIRECT_PRINT_FALLBACK_TO_BROWSER', true),
        'qz_script_url' => env('DIRECT_PRINT_QZ_SCRIPT_URL', 'https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js'),
        'qz_printer' => env('DIRECT_PRINT_QZ_PRINTER'),
    ],

];
