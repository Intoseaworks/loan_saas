<?php

namespace Common\Models\Collection;

use Carbon\Carbon;
use Common\Traits\Model\StaticModel;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\CollectionLog
 *
 * @mixin \Eloquent
 * @property int|null $status 状态
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereStatus($value)
 * @property int $id
 * @property int|null $user_id 用户ID
 * @property int|null $merchant_id 商户ID
 * @property int|null $admin_id 操作人ID
 * @property \Illuminate\Support\Carbon|null $created_at 入黑时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionBlackList whereUserId($value)
 */
class CollectionBlackList extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    /**
     * @var string
     */
    protected $table = 'collection_black_list';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var bool
     */
    public $timestamps = false;

    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setAppIdOrMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'admin_id' => LoginHelper::getAdminId() ?? 0,
                'status' => self::STATUS_ACTIVE,
                'created_at',
                'updated_at',
            ]
        ];
    }

    /**
     * 加入黑名单
     * @param $userId
     * @return bool|CollectionBlackList
     */
    public function create($userId)
    {
        $data = [
            'user_id' => $userId,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ];
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    /**
     * 更新拉黑状态
     * @param $userId
     * @param int $status
     * @return bool|int
     */
    public function setStatus($userId, $status = self::STATUS_ACTIVE)
    {
        return self::model()->whereUserId($userId)->update(['status' => $status, 'updated_at' => Carbon::now()->toDateTimeString()]);
    }
}
