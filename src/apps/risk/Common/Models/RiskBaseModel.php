<?php

namespace Risk\Common\Models;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Risk\Common\Models\RiskBaseModel
 *
 * @method static Builder|RiskBaseModel newModelQuery()
 * @method static Builder|RiskBaseModel newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static Builder|RiskBaseModel query()
 * @mixin \Eloquent
 */
class RiskBaseModel extends Model
{
    use StaticModel;

    /** @var string 风控关联 merchant 表的字段 */
    public static $riskRelationMerchantField = 'app_id';
    /** @var string 风控关联 app 表的字段 */
    public static $riskRelationAppField = 'business_app_id';
    protected $connection = 'mysql_risk';

    /**
     * 为 model 添加全局作用域，merchant_id
     * 此方法适用于只有 merchant_id 作为关联条件的表
     */
    public static function setMerchantIdBootScope()
    {
        $tableName = (new static)->getTable();
        if ($merchantId = MerchantHelper::getMerchantId()) {
            static::addGlobalScope(static::$bootScopeMerchant, function (Builder $builder) use ($merchantId, $tableName) {
                $builder->where($tableName . '.' . static::$riskRelationMerchantField, $merchantId);
            });
        }
    }
}
