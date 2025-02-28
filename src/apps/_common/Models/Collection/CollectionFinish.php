<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionFinish
 *
 * @property int $id 完结ID
 * @property int $collection_id 催收记录ID
 * @property int $order_id 催收记录ID
 * @property int $admin_id 案件催收员ID
 * @property string $from_status 完结前案子状态
 * @property string $to_status 完结后案子状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\CollectionFinish whereMerchantId($value)
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionFinish whereCollectionAssignId($value)
 */
class CollectionFinish extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'collection_finish';
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

    public function create($data)
    {
        return self::model($data)->save();
    }

}
