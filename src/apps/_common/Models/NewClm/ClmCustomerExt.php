<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmCustomerExt
 *
 * @property int $id id
 * @property int $merchant_id 商户ID
 * @property string $clm_customer_id 客户clm_id
 * @property int|null $current_level 客户当前等级
 * @property int $status 状态 1:正常 2:冻结
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereClmCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereCurrentLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmCustomerExt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmCustomerExt extends Model
{
    use StaticModel;

    protected $table = 'new_clm_customer_ext';

    protected $guarded = [];

    // 状态：正常
    const STATUS_NORMAL = 1;
    // 状态：冻结
    const STATUS_FROZEN = 2;
    // 状态
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_FROZEN => '冻结',
    ];

    /**
     * 强限制，必须在设置了商户ID的情况下，才可进行查询
     * @var bool
     */
    protected static $forceRestrict = true;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();

        // 拿回数据后判断当前是否设置了商户ID，未拿到数据不会触发
        // 保证是在设置了商户ID的情况下获取到记录
        static::retrieved(function ($model) {
            if (self::$forceRestrict && !MerchantHelper::getMerchantId()) {
                throw new \Exception('clm ext must specify merchant.');
            }
        });
    }

    /*************************************************************************
     * relations
     ************************************************************************/

    /*************************************************************************
     * attributes
     ************************************************************************/

    /**
     * 冻结
     *
     * @return bool
     */
    public function freeze(): bool
    {
        return $this->update([
            'status' => self::STATUS_FROZEN,
        ]);
    }

    /**
     * 解冻
     *
     * @return bool
     */
    public function unfreeze(): bool
    {
        return $this->update([
            'status' => self::STATUS_NORMAL,
        ]);
    }

    /**
     * 变更等级
     *
     * @param int $level
     *
     * @return bool
     */
    public function changeLevel(int $level): bool
    {
        return $this->update([
            'current_level' => $level,
        ]);
    }

    /*************************************************************************
     * static
     ************************************************************************/

    /**
     * 获取客户在所有商户的最大等级
     *
     * @param string $clmCustomerId
     *
     * @return int
     */
    public static function getMaxLevel(string $clmCustomerId): int
    {
        $origin = self::$forceRestrict;

        self::$forceRestrict = false;

        $maxLevel = self::query()->where('clm_customer_id', $clmCustomerId)->max('current_level');

        self::$forceRestrict = $origin;

        return $maxLevel;
    }
}
