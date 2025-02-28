<?php

namespace Common\Utils;

use Common\Models\Merchant\App;
use Common\Models\Merchant\Merchant;
use Common\Redis\Merchant\MerchantRedis;
use Illuminate\Database\Eloquent\Model;

class MerchantHelper
{
    use Helper;

    /** @var string 商户ID接口标识字段 */
    const MERCHANT_ID_FIELD = 'merchant-id';
    /** @var string App ID接口标识字段 */
    const APP_ID_FIELD = 'app-key';

    /** @var string|null 商户ID */
    protected static $merchantId;
    /** @var string|null App ID */
    protected static $appId;

    public static function setMerchantId($merchantId)
    {
        // 清空 Model 存储的boot缓存
        self::clearMerchantId();

        static::$merchantId = $merchantId;

        // 设置当前商户的 APP_KEY & APP_SECRET_KEY
        self::setServicesConfig();
    }

    public static function getMerchantId()
    {
        $merchantId = static::$merchantId;

        return $merchantId;
    }

    public static function clearMerchantId()
    {
        static::$merchantId = null;
        static::$appId = null;

        // 清空 Model 存储的boot缓存
        Model::clearBootedModels();

        static::restoreServicesConfig();
    }

    public static function setAppId($appId, $merchantId = null)
    {
        if (isset($merchantId)) {
            self::setMerchantId($merchantId);
        }

        static::$appId = $appId;

        // 清空 Model 存储的boot缓存
        Model::clearBootedModels();
    }

    public static function getAppId()
    {
        $appId = static::$appId;

        return $appId;
    }

    public function safeSetMerchantId($merchantId)
    {
        $merchant = Merchant::model()->getNormalById($merchantId);

        if (!$merchant) {
            return false;
        }

        static::setMerchantId($merchant->id);

        return true;
    }

    public function safeSetAppIdByKey($appKey)
    {
        $app = App::model()->getNormalAppByKey($appKey);

        if (!$app) {
            return false;
        }

        static::setAppId($app->id, $app->merchant->id);

        return true;
    }

    public function safeSetAppId($appId)
    {
        $app = App::model()->getNormalAppById($appId);

        if (!$app) {
            return false;
        }

        static::setAppId($app->id, $app->merchant->id);

        return true;
    }

    public function getAllAppByMerchantId($appId)
    {
//        if ($appIds = MerchantRedis::redis()->getByMerchantId($appId)) {
//            return $appIds;
//        }

        $appIds = App::model()->getAllSameBelong($appId)->pluck('id')->toArray();
        if ($appIds) {
            MerchantRedis::redis()->addAppToMerchant($appId, $appIds);
            return $appIds;
        }

        return null;
    }

    public static function callback(callable $function, $id = null)
    {
        $oldMerchantId = static::getMerchantId();

        $merchants = Merchant::model()->getNormalAll($id);
        foreach ($merchants as $merchant) {
            static::setMerchantId($merchant->id);

            $function($merchant);
        }

        if (isset($oldMerchantId)) {
            static::setMerchantId($oldMerchantId);
        } else {
            static::clearMerchantId();
        }
    }

    public static function callbackOnce(callable $function)
    {
        $oldMerchantId = static::getMerchantId();

        $result = $function();

        if (isset($oldMerchantId)) {
            static::setMerchantId($oldMerchantId);
        } else {
            static::clearMerchantId();
        }

        return $result;
    }

    protected static function setServicesConfig()
    {
        $merchantId = MerchantHelper::getMerchantId();
        $merchant = (new Merchant())->getById($merchantId);

        if (!$merchant || !$merchant->app_key || !$merchant->app_secret_key) {
            throw new \Exception('Services APP_KEY not exists!');
        }

        // 将当前商户的 印牛服务key 配置覆盖默认配置
        config([
            'domain.public_services_config.app_key' => $merchant->app_key,
            'domain.public_services_config.app_secret_key' => $merchant->app_secret_key,
        ]);
    }

    protected static function restoreServicesConfig()
    {
        config([
            'domain.public_services_config.app_key' => env('SERVICES_APP_KEY', ''),
            'domain.public_services_config.app_secret_key' => env('SERVICES_APP_SECRET_KEY', ''),
        ]);
    }
}
