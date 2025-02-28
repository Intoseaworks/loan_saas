<?php

namespace Common\Services\UFileStorage;

use Common\Services\UFileStorage\Plugins\GetFile;
use Common\Services\UFileStorage\Plugins\GetOssUrl;
use Common\Services\UFileStorage\Plugins\PutAs;
use Common\Services\UFileStorage\Plugins\PutRemoteFile;
use Common\Services\UFileStorage\Plugins\SetBucket;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class UFileServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // oss 外网节点
        Storage::extend('ufile', function ($app, $config) {
            return $this->initFilesystem($config, 'ufile');
        });

        app(Factory::class)->extend('ufile', function ($config) {
            return Storage::disk('ufile');
        });
    }

    /**
     * @param $config
     * @param string $symbol
     * @return Filesystem
     */
    public function initFilesystem($config, $symbol = 'ufile')
    {
        $publicKey = $config['public_key'];
        $privateKey = $config['private_key'];
        $bucket = $config['bucket'];
        $proxySuffix = $config['proxy_suffix'];
        $https = $config['https'] ?? false;

        $debug = empty($config['debug']) ? false : $config['debug'];
        if ($debug) {
            Log::debug('UFile config:', $config);
        }

        $adapter = new UFileAdapter($bucket, $publicKey, $privateKey, $proxySuffix, null, $symbol, $https);
        $filesystem = new Filesystem($adapter);

        // $filesystem->addPlugin(new PutFile()); putFile 方法已存在，不能自定义
        $filesystem->addPlugin(new PutRemoteFile());
        $filesystem->addPlugin(new SetBucket());
        $filesystem->addPlugin(new GetOssUrl());
        $filesystem->addPlugin(new PutAs());
        $filesystem->addPlugin(new GetFile());

        return $filesystem;
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }

}
