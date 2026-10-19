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

    'meta' => [
        'client_id' => env('META_CLIENT_ID'),
        'client_secret' => env('META_CLIENT_SECRET'),
        'config_id' => env('META_CONFIG_ID'),
        'redirect_uri' => env('META_REDIRECT_URI'),
        'graph_version' => env('META_GRAPH_VERSION', 'v23.0'),
    ],

    'crm_purchase_webhook' => [
        'url' => env('CRM_PURCHASE_WEBHOOK_URL'),
        'secret' => env('CRM_PURCHASE_WEBHOOK_SECRET'),
        'timeout' => (int) env('CRM_PURCHASE_WEBHOOK_TIMEOUT', 10),
    ],

    'crm_human_refresh_webhook' => [
        'url' => env(
            'CRM_HUMAN_REFRESH_WEBHOOK_URL',
            'https://bmglbl3.n8n.bocindonesia.com/webhook-test/get-human-chat'
        ),
        'timeout' => (int) env('CRM_HUMAN_REFRESH_WEBHOOK_TIMEOUT', 600),
    ],

    'threads' => [
        'client_id' => env('THREADS_CLIENT_ID'),
        'client_secret' => env('THREADS_CLIENT_SECRET'),
        'redirect_uri' => env('THREADS_REDIRECT_URI'),
        'graph_version' => env('THREADS_GRAPH_VERSION', 'v1.0'),
    ],

    'tiktok' => [
        'client_key' => env('TIKTOK_CLIENT_KEY'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect_uri' => env('TIKTOK_REDIRECT_URI'),
        'scopes' => env('TIKTOK_SCOPES', 'user.info.basic,video.list'),
    ],

    'instagram' => [
        'token' => env('INSTAGRAM_ACCESS_TOKEN'),
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'chatgpt' => [
        'erp_app_url' => env('CHATGPT_ERP_APP_URL'),
    ],

    'waha' => [
        'api_key' => env('WAHA_API_KEY'),
    ],

    'cloudflare_turn' => [
        'key_id' => env('CLOUDFLARE_TURN_KEY_ID'),
        'api_token' => env('CLOUDFLARE_TURN_API_TOKEN'),
        'ttl' => (int) env('CLOUDFLARE_TURN_TTL', 3600),
    ],

];
