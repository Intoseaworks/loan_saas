<?php

namespace Common\Services\AliOssStorage;

use Common\Services\AliOssStorage\Plugins\GetFile;
use Common\Services\AliOssStorage\Plugins\GetOssUrl;
use Common\Services\AliOssStorage\Plugins\PutAs;
use Common\Services\AliOssStorage\Plugins\SetBucket;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Jacobcyl\AliOSS\Plugins\PutFile;
use Jacobcyl\AliOSS\Plugins\PutRemoteFile;
use League\Flysystem\Filesystem;
use OSS\OssClient;

class AliOssServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $config = $buckets = config('filesystems.disks.oss');
        config(['filesystems.disks.inner-oss' => array_merge($config, ['driver' => 'inner-oss'])]);

        // oss 外网节点
        Storage::extend('oss', function ($app, $config) {
            return $this->initFilesystem($config, 'oss');
        });

        // oss 内网节点
        Storage::extend('inner-oss', function ($app, $config) {
            return $this->initFilesystem($config, 'inner-oss', true);
        });

        app(Factory::class)->extend('oss', function ($config) {
            return Storage::disk('oss');
        });

        app(Factory::class)->extend('inner-oss', function ($config) {
            return Storage::disk('inner-oss');
        });
    }

    /**
     * @param $config
     * @param $symbol
     * @param bool $internal
     * @return Filesystem
     * @throws \OSS\Core\OssException
     */
    public function initFilesystem($config, $symbol, $internal = false)
    {
        $accessId = $config['access_id'];
        $accessKey = $config['access_key'];

        $cdnDomain = empty($config['cdnDomain']) ? '' : $config['cdnDomain'];
        $bucket = array_keys($config['buckets'])[0];
        $ssl = empty($config['ssl']) ? false : $config['ssl'];
        $isCname = empty($config['isCName']) ? false : $config['isCName'];
        $debug = empty($config['debug']) ? false : $config['debug'];

        // 判断使用哪个节点
        $endPoint = $isCname ? $cdnDomain : ($internal ? $config['endpoint_internal'] : $config['endpoint']);

        if ($debug) Log::debug('OSS config:', $config);

        $client = new OssClient($accessId, $accessKey, $endPoint, $isCname);
        $adapter = new AliOssAdapter($client, $bucket, $endPoint, $ssl, $symbol, $isCname, $debug, $cdnDomain);

        //Log::debug($client);
        $filesystem = new Filesystem($adapter);

        $filesystem->addPlugin(new PutFile());
        $filesystem->addPlugin(new PutRemoteFile());
        $filesystem->addPlugin(new SetBucket());
        $filesystem->addPlugin(new GetOssUrl());
        $filesystem->addPlugin(new PutAs());
        $filesystem->addPlugin(new GetFile());
        //$filesystem->addPlugin(new CallBack());
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
