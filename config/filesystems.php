<?php

return [

    // Usa el valor del .env; si no existe, cae en 'local'
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // (opcional) se queda por compatibilidad
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // ✅ Wasabi (S3‑compatible)
        'wasabi' => [
            'driver' => 's3',
            'key' => env('WASABI_KEY'),
            'secret' => env('WASABI_SECRET'),
            'region' => env('WASABI_REGION', 'us-central-1'),
            'bucket' => env('WASABI_BUCKET', 'pigo-docs'),
            'endpoint' => env('WASABI_ENDPOINT', 'https://s3.us-central-1.wasabisys.com'),
            'use_path_style_endpoint' => true,
            'throw' => true,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
