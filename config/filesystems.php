<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        /*
        | Disk 'local' (PRIVAT: foto anak, dokumentasi, bukti bayar, lampiran chat) dan
        | disk 'public' (galeri, logo, berita, foto guru). Di laptop (Laragon) keduanya
        | memakai folder storage/. Di Vercel, isi SUPABASE_STORAGE=true agar keduanya
        | memakai Supabase Storage, karena sistem file server Vercel tidak permanen.
        */
        'local' => env('SUPABASE_STORAGE', false) ? [
            'driver' => 'supabase',
            'url_project' => env('SUPABASE_URL'),
            'key' => env('SUPABASE_SECRET_KEY'),
            'bucket' => env('SUPABASE_BUCKET_PRIVAT', 'tk-ceria-privat'),
            'public' => false,
            'throw' => true,
            'report' => true,
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => env('SUPABASE_STORAGE', false) ? [
            'driver' => 'supabase',
            'url_project' => env('SUPABASE_URL'),
            'key' => env('SUPABASE_SECRET_KEY'),
            'bucket' => env('SUPABASE_BUCKET_PUBLIK', 'tk-ceria-publik'),
            'public' => true,
            'throw' => true,
            'report' => true,
        ] : [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/storage', // relatif: tetap benar walau APP_URL berbeda dengan alamat yang dibuka
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Folder lokal asli, dipakai perintah tk:pindah-file-ke-supabase
        'arsip_privat' => ['driver' => 'local', 'root' => storage_path('app/private'), 'throw' => false],
        'arsip_publik' => ['driver' => 'local', 'root' => storage_path('app/public'), 'throw' => false],

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

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
