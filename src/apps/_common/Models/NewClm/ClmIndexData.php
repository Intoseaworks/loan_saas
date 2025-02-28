<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmIndexData
 *
 * @property int $id id
 * @property int $merchant_id 商户ID
 * @property string $clm_customer_id 客户clm_id
 * @property int $applyid 申请流水号
 * @property string|null $index_rule 指标规则名称
 * @property int|null $index_value 指标值
 * @property int|null $old_level 旧level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereApplyid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereClmCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereIndexRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereIndexValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereOldLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmIndexData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmIndexData extends Model
{
    use StaticModel;

    protected $table = 'new_clm_index_data';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
