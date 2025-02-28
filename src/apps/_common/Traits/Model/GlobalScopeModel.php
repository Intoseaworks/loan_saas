<?php

namespace Common\Traits\Model;

use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait GlobalScopeModel
 * @package Common\Traits\Model
 */
trait GlobalScopeModel
{
    /** @var string model 全局作用域 merchant 名称 */
    public static $bootScopeMerchant = 'merchant';
    /** @var string model 全局作用域 APP 名称 */
    public static $bootScopeApp = 'app';

    /** @var string 关联 merchant 表的字段 */
    public static $relationMerchantField = 'merchant_id';
    /** @var string 关联 app 表的字段 */
    public static $relationAppField = 'app_id';

    /**
     * 为 model 添加全局作用域，app_id or merchant_id
     * 此方法适用于有 app_id ，merchant_id 作为关联条件的表
     * 优先使用 app_id 作为条件
     * app_id 不存在时使用 merchant_id 作为条件
     */
    public static function setAppIdOrMerchantIdBootScope()
    {
        $tableName = (new static)->getTable();
        if ($appId = self::getAppId()) {
            static::addGlobalScope(static::$bootScopeApp, function (Builder $builder) use ($appId, $tableName) {
                $builder->where($tableName . '.' . static::$relationAppField, $appId);
            });
        } else {
            static::setMerchantIdBootScope();
        }
    }

    /**
     * 为 model 添加全局作用域，app_id
     * 此方法适用于只有 app_id 作为关联条件的表
     * 优先使用 app_id 作为条件
     * app_id 不存在时使用 merchant_id 作为条件
     */
    public static function setAppIdBootScope()
    {
        $tableName = (new static)->getTable();
        if ($appId = self::getAppId()) {
            static::addGlobalScope(static::$bootScopeApp, function (Builder $builder) use ($appId, $tableName) {
                $builder->where($tableName . '.' . static::$relationAppField, $appId);
            });
        } elseif ($merchantId = MerchantHelper::getMerchantId()) {
            $appIds = MerchantHelper::helper()->getAllAppByMerchantId($merchantId);

            static::addGlobalScope(static::$bootScopeApp, function (Builder $builder) use ($appIds, $tableName) {
                if ($appIds) {
                    $builder->whereIn($tableName . '.' . static::$relationAppField, $appIds);
                } else {
                    // 当不存在 appId 时，不获取数据
                    $builder->where($tableName . '.' . static::$relationAppField, -1);
                }
            });
        }
    }

    private static function getAppId()
    {
//        return false; // 不区分app
        return MerchantHelper::getAppId();
    }

    /**
     * 为 model 添加全局作用域，merchant_id
     * 此方法适用于只有 merchant_id 作为关联条件的表
     */
    public static function setMerchantIdBootScope()
    {
        $tableName = (new static)->getTable();
        if ($merchantId = MerchantHelper::getMerchantId()) {
            static::addGlobalScope(static::$bootScopeMerchant, function (Builder $builder) use ($merchantId, $tableName) {
                $builder->where($tableName . '.' . static::$relationMerchantField, $merchantId);
            });
        }
    }
}
