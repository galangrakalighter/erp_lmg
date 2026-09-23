<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'database_id' => env('FIREBASE_DATABASE_ID', '(default)'),
    'credentials' => env(
        'FIREBASE_CREDENTIALS',
        storage_path('app/firebase/service-account.json')
    ),
    'default_collection' => env('FIREBASE_DEFAULT_COLLECTION'),
    'activity_log_collection' => env('FIREBASE_ACTIVITY_LOG_COLLECTION', 'logs'),
    'timeout' => (int) env('FIREBASE_HTTP_TIMEOUT', 30),
    'connect_timeout' => (int) env('FIREBASE_CONNECT_TIMEOUT', 5),
    'retry_times' => (int) env('FIREBASE_RETRY_TIMES', 1),
    'token_cache_seconds' => (int) env('FIREBASE_TOKEN_CACHE_SECONDS', 3300),
    'web' => [
        'apiKey' => env('FIREBASE_WEB_API_KEY'),
        'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'projectId' => env('FIREBASE_WEB_PROJECT_ID', env('FIREBASE_PROJECT_ID')),
        'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'appId' => env('FIREBASE_WEB_APP_ID'),
    ],
];
