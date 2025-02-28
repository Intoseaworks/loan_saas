<?php

namespace Common\Services\Common;

use Common\Services\BaseService;

class VersionServer extends BaseService
{
    /** android */
    const PLATFORM_ANDROID = 'android';
    /** ios */
    const PLATFORM_IOS = 'ios';
    /** 平台 */
    const PLATFORM = [
        self::PLATFORM_ANDROID => 'android',
        self::PLATFORM_IOS => 'ios',
    ];

    /**
     * 比较版本信息
     * @param $versionInfo
     * @param $version
     * @param $platform
     * @return mixed
     */
    public function compareVersion(&$versionInfo, $version, $platform)
    {
        $currentVersion = $this->versionToNumber($version);  //App当前版本号
        $lastVersion = $this->versionToNumber($versionInfo['version']); //最新版本号
        $hotRelativeVersion = $this->versionToNumber($versionInfo['hotRelativeVersion']); //热更新对应版本号

        $versionInfo["updateable"] = false;
        if ($currentVersion && $lastVersion) {
            $versionInfo["updateable"] = $currentVersion < $lastVersion;
            if ($platform == self::PLATFORM_ANDROID) {
                $versionInfo["hotUpdateable"] = $currentVersion == $hotRelativeVersion;
            }
        }
        return $versionInfo;
    }

    public function gteVersion($left, $right)
    {
        $leftVersion = $this->versionToNumber($left);
        $rightVersion = $this->versionToNumber($right);

        return $leftVersion >= $rightVersion;
    }

    private function versionToNumber($versionStr)
    {
        $versionArr = explode(".", $versionStr);
        $versionNumber = 0;

        $versionArr = array_map(function ($item) {
            return (int)$item;
        }, $versionArr);

        foreach ($versionArr as $item) {
            $versionNumber = $versionNumber * 1000 + $item;
        }
        return $versionNumber;
    }
}
