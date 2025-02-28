<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmSelectLog
 *
 * @property int $id id
 * @property int|null $merchant_id 商户ID
 * @property string $clm_customer_id 客户clm_id
 * @property int $applyid 申请流水号
 * @property int $clm_level 调整前等级
 * @property int $current_amount 可用额度
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereApplyid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereClmCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereNewLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereOldLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmSelectLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmSelectLog extends Model
{
    use StaticModel;

    protected $table = 'new_clm_select_log';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
