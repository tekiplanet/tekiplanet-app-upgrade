<?php

return [
    'default' => env('FIREBASE_PROJECT', 'app'),
    
    'projects' => [
        'app' => [
            'credentials' => env('FIREBASE_CREDENTIALS'),
            'database_url' => env('FIREBASE_DATABASE_URL'),
        ],
    ],
    
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS'),
        'auto_discovery' => true,
    ],
]; 