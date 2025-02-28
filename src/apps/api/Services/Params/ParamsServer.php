<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Params;

use Api\Services\BaseService;

class ParamsServer extends BaseService
{
    const LAST_VERSION = [
        'android' => [
            'version' => '1.0.0',
            'hotRelativeVersion' => '1.0.0',
            'updateable' => false,
            'forcible' => true, //是否强制更新
            'title' => 'New edition update',
            'content' => "\r\nImprove Address & ID Proof and PAN card uploading progress.\r\nFix bugs.\r\n",
            'size' => '10 MB',
        ],
        'ios' => [
            'version' => '1.0.0',
            'hotRelativeVersion' => '1.0.0',
            'updateable' => false,
            'forcible' => true, //是否强制更新
            'title' => 'New edition update',
            'content' => "\r\nImprove Address & ID Proof and PAN card uploading progress.\r\nFix bugs.\r\n",
            'url' => "https://itunes.apple.com/cn/app/id1400654316?mt=8",
            'size' => '10 MB',
        ]
    ];

    public function getVersion($platform)
    {
        return self::LAST_VERSION[$platform] ?? false;
    }
}
