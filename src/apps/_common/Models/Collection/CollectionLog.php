<?php

namespace Common\Models\Collection;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionLog
 *
 * @property int $id 催收日志id
 * @property int|null $merchant_id merchant_id
 * @property int|null $user_id 0表示系统，其他为用户的user_id
 * @property int|null $order_id 订单id
 * @property int|null $collection_id 催收id
 * @property int|null $admin_id 管理员id
 * @property string|null $from_status 更新前状态
 * @property string|null $to_status 更新后状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $collection_assign_id 催收分单ID
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereCollectionAssignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereFromStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereToStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionLog whereUserId($value)
 * @mixin \Eloquent
 */
class CollectionLog extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'collection_log';
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

    public function textRules()
    {
        return [];
    }

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'user_id',
                'order_id',
                'collection_id',
                'admin_id',
                'from_status',
                'to_status',
                'collection_assign_id',
            ]
        ];
    }

    public function addLog($collection, $fromStatus, $toStatus)
    {
        return CollectionLog::model(CollectionLog::SCENARIO_CREATE)
            ->saveModel([
                'merchant_id' => $collection->merchant_id,
                'user_id' => $collection->user_id,
                'order_id' => $collection->order_id,
                'collection_id' => $collection->id,
                'admin_id' => $collection->admin_id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'collection_assign_id' => $collection->collectionAssign->id ?? "0",
            ]);
    }

}
