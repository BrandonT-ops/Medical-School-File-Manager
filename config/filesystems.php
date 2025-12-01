<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'public',
            'throw' => false,
        ],

        'logos' => [
            'driver' => 'local',
            'root' => public_path('storage/logos'),
            'url' => env('APP_URL').'/storage/logos',
            'visibility' => 'public',
            'throw' => false,
        ],

        'temp' => [
            'driver' => 'local',
            'root' => storage_path('app/temp'),
            'throw' => false,
        ],

        'google' => [
            'driver' => 'google',
            'clientId' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folder' => env('GOOGLE_DRIVE_FOLDER_ID'),
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
