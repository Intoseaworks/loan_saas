<?php

return \YunhanDev\Phan\PhanConfig::getConfig([
    // 忽略部分报错
    'suppress' => [
        'PhanAccessNonStaticToStatic' => [
            'newModelQuery',
            'newQuery',
            'query',
        ]
    ],

    // 解析的目录（包含依赖，依赖需要从exclude剔除）
    'directory_list' => [
        'vendor/jmd-backend/php-libs/src',
        'vendor/wushunyi/aliyun-sdk-mns/AliyunMNS',
        'vendor/psr/http-message/src',
        'vendor/spatie/laravel-permission/src',
        'vendor/maatwebsite/excel',
        'vendor/phpoffice/phpspreadsheet/src',
        'vendor/mpdf/mpdf/src',
        'vendor/jenssegers/agent/src'
    ],

    // 不需要解析的目录
    'exclude_analysis_directory_list' => [
        'vendor',
        '.phan',
        'apps/_common/Services/Rbac',
    ],

    // 需要解析的单独的文件
    'file_list' => [
    ],

    // 需要剔除的文件列表
    'exclude_file_list' => [
    ],

    // 自动加载的内部类库，一般用于加载扩展stub，下面引入了laravel的stub
    'autoload_internal_extension_signatures' => [
        'laravelIdeHelper' => '_ide_helper.php',
        'laravelMeta' => '.phpstorm.meta.php'
    ],
]);
