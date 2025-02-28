<?php

namespace Common\Models\Clm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Clm\ClmChangeLog
 *
 * @property int $id
 * @property int|null $merchant_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property string|null $old_level
 * @property string|null $new_level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereNewLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereOldLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmChangeLog whereUserId($value)
 * @mixin \Eloquent
 */
class ClmChangeLog extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'clm_change_log';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

}
