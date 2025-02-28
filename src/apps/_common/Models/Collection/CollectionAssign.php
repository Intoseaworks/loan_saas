<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionAssign
 *
 * @property int $id 完结ID
 * @property int $collection_id 催收记录ID
 * @property int $order_id 催收记录ID
 * @property int $admin_id 案件催收员ID
 * @property int $from_admin_id 来源催收员ID
 * @property string $from_status 分配前案子状态
 * @property string $to_status 分配后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $setting_id 分单配置id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign orderByCustom($column = null, $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereFromAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionAssign whereMerchantId($value)
 * @property int|null $parent_id 手动分单人id
 * @property string $from_level 分配前案子等级
 * @property string $level 分配后案子等级
 * @property int|null $overdue_days 当前逾期天数
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAssign whereFromLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAssign whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAssign whereOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionAssign whereParentId($value)
 */
class CollectionAssign extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'collection_assign';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];
    /**
     * @var bool
     */
    //public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

}
