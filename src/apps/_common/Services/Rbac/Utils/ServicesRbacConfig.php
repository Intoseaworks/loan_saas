<?php
/**
 * Created by PhpStorm.
 * User: Summer
 * Date: 2018/8/9
 * Time: 16:15
 */

namespace Common\Services\Rbac\Utils;

use Auth;
use Common\Services\Rbac\Library\Contracts\Config;
use JMD\Utils\SignHelper;

class ServicesRbacConfig implements Config
{

    /**
     * 获取认证域名
     *
     * @return string
     */
    public function getDomain(): string
    {
        return config('domain.public_services_config.endpoint');
    }

    /**
     * 获取AppKey
     *
     * @return string
     */
    public function getAppKey(): string
    {
        return config('domain.public_services_config.app_key');
    }

    /**
     * 获取用户id
     *
     * @return int
     */
    public function getUserId(): int
    {
        return Auth::user()->id;
    }

    /**
     * 获取签名
     *
     * @param array $params
     * @return string
     */
    public function getSign(&$params): string
    {
        $config = config('domain.public_services_config');
        $params['app_key'] = $config['app_key'];

        return SignHelper::sign($params, $config['app_secret_key']);
    }

    /**
     * 获取项目url
     *
     * @return string
     */
    public function getProjectName(): string
    {
        return config('domain.project_name', '');
    }

    /**
     * 获取功能文件地址
     *
     * @return string
     */
    public function getOperationFilePath(): string
    {
        return '';
    }
}
