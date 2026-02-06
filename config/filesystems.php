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
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'authors' => [
            'driver' => 'local',
            'root' => storage_path('app/public/authors'),
            'url' => env('APP_URL').'/storage/authors',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'categories' => [
            'driver' => 'local',
            'root' => storage_path('app/public/categories'),
            'url' => env('APP_URL').'/storage/categories',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'tags' => [
            'driver' => 'local',
            'root' => storage_path('app/public/tags'),
            'url' => env('APP_URL').'/storage/tags',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'articles' => [
            'driver' => 'local',
            'root' => storage_path('app/public/articles'),
            'url' => env('APP_URL').'/storage/articles',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'types' => [
            'driver' => 'local',
            'root' => storage_path('app/public/types'),
            'url' => env('APP_URL').'/storage/types',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'materials' => [
            'driver' => 'local',
            'root' => storage_path('app/public/materials'),
            'url' => env('APP_URL').'/storage/materials',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],


        'mp4' => [
            'driver' => 'local',
            'root' => storage_path('app/public/mp4'),
            'url' => '/storage/mp4',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'm4r30' => [
            'driver' => 'local',
            'root' => storage_path('app/public/m4r30'),
            'url' => '/storage/m4r30',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'm4r40' => [
            'driver' => 'local',
            'root' => storage_path('app/public/m4r40'),
            'url' => '/storage/m4r40',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

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
