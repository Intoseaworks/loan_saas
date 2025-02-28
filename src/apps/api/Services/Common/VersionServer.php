<?php

namespace Api\Services\Common;

use Common\Models\Config\Config;

class VersionServer extends \Common\Services\Common\VersionServer
{
    private static $lastVersion = [
        self::PLATFORM_ANDROID => [
            'version' => '1.0.0',
            'updateable' => false,
            'forcible' => true, //是否强制更新
            'title' => 'New Version Update',
            'content' => "Image uploading optimization",
            'hotRelativeVersion' => '',
        ],
        self::PLATFORM_IOS => [
            'version' => '1.0.0',
            'channel' => '',
            'updateable' => false,
            'forcible' => true, //是否强制更新
            'title' => 'New Version Update',
            'content' => "Optimize the login process and UI design",
            'url' => "https://itunes.apple.com/",
            'hotRelativeVersion' => '',
        ],
    ];

    /**
     * 获取最后一个版本信息
     * @param $platform
     * @return mixed
     */
    public function getLastVersionInfo($platform)
    {
        return [
            'version' => Config::model()->getAppVersionNo($platform),
            'updateable' => Config::model()->getAppVersionUpdateable($platform),
            'forcible' => Config::model()->getAppVersionForcible($platform), //是否强制更新
            'title' => Config::model()->getAppVersionTitle($platform),
            'content' => Config::model()->getAppVersionContent($platform),
            'hotRelativeVersion' => Config::model()->getAppVersionHotRelativeVersion($platform),
            'url' => Config::model()->getAppVersionUrl($platform),
        ];
        //return self::$lastVersion[$platform];
    }
}
