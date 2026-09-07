<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'database_id' => env('FIREBASE_DATABASE_ID', '(default)'),
    'credentials' => env(
        'FIREBASE_CREDENTIALS',
        storage_path('app/firebase/service-account.json')
    ),
    'default_collection' => env('FIREBASE_DEFAULT_COLLECTION'),
    'timeout' => (int) env('FIREBASE_HTTP_TIMEOUT', 15),
];
