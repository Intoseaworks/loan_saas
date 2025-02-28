<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. A "local" driver, as well as a variety of cloud
    | based drivers are available for your choosing. Just store away!
    |
    | Supported: "local", "ftp", "s3", "rackspace"
    |
    */
    'default' => 'local',
    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */
    'cloud' => 's3',
    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],
        'uploads' => [ // used for Backpack/CRUD (in elFinder)
            'driver' => 'local',
            'root' => public_path('uploads'),
        ],
        'backups' => [ // used for Backpack/BackupManager
            'driver' => 'local',
            'root' => storage_path('backups'), // that's where your backups are stored by default: storage/backups
        ],
        'storage' => [ // used for Backpack/LogManager
            'driver' => 'local',
            'root' => storage_path(),
        ],
        'demo-sql' => [
            'driver' => 'local',
            'root' => storage_path('demo-sql'),
        ],
        's3' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],
        'oss' => [
            'driver' => 'oss',
            'access_id' => env('OSS_ACCESS_ID'),
            'access_key' => env('OSS_ACCESS_KEY'),
            // bucket => 访问权限  true 公共仓库,false私有仓库.默认使用第一个bucket.如果使用其他的需要声明:Storage::disk('oss')->setBucket($bucket);
            'buckets' => json_decode(env('OSS_BUCKETS', '{}'), true),
            'endpoint' => env('OSS_ENDPOINT'), // OSS 外网节点或自定义外部域名
            'endpoint_internal' => env('OSS_ENDPOINT_INTERNAL'), // OSS 内网节点
            'cdnDomain' => env('OSS_CDNDOMAIN', ''), // 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
            'ssl' => env('OSS_SSL', true), // true to use 'https://' and false to use 'http://'. default is false,
            'isCName' => env('OSS_ISCNAME', false), // 是否使用自定义域名,true: 则Storage.url()会使用自定义的cdn或域名生成文件url， false: 则使用外部节点生成url
            'debug' => env('OSS_DEBUG', false),
        ],
        'ufile' => [
            'driver' => 'ufile',
            'proxy_suffix' => '.ind-mumbai.ufileos.com',
            'bucket' => '',
            'public_key' => '',
            'private_key' => '',
            'https' => true,
        ],
    ],
];
