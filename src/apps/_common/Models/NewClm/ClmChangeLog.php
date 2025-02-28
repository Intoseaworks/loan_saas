<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmChangeLog
 *
 * @property int $id id
 * @property int|null $merchant_id 商户ID
 * @property string $clm_customer_id 客户clm_id
 * @property int $applyid 申请流水号
 * @property int $old_level 调整前等级
 * @property int $new_level 调整后等级
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereApplyid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereClmCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereNewLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereOldLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmChangeLog extends Model
{
    use StaticModel;

    protected $table = 'new_clm_change_log';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
