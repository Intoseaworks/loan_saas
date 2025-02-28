<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmInitLevel
 *
 * @property int $id id
 * @property int $merchant_id 商户ID
 * @property int $user_type 用户类型 1:白名单 2:非白名单
 * @property int $payment_type 收款方式 1:有卡 2:无卡
 * @property int $clm_level 等级
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|ClmInitLevel newModelQuery()
 * @method static Builder|ClmInitLevel newQuery()
 * @method static Builder|ClmInitLevel orderByCustom($defaultSort = null)
 * @method static Builder|ClmInitLevel query()
 * @method static Builder|ClmInitLevel whereClmLevel($value)
 * @method static Builder|ClmInitLevel whereCreatedAt($value)
 * @method static Builder|ClmInitLevel whereId($value)
 * @method static Builder|ClmInitLevel whereMerchantId($value)
 * @method static Builder|ClmInitLevel whereNonWhitelistAndIsCard()
 * @method static Builder|ClmInitLevel whereNonWhitelistAndNonCard()
 * @method static Builder|ClmInitLevel wherePaymentType($value)
 * @method static Builder|ClmInitLevel whereUpdatedAt($value)
 * @method static Builder|ClmInitLevel whereUserType($value)
 * @method static Builder|ClmInitLevel whereWhitelistAndIsCard()
 * @method static Builder|ClmInitLevel whereWhitelistAndNonCard()
 * @mixin \Eloquent
 */
class ClmInitLevel extends Model
{
    use StaticModel;

    protected $table = 'new_clm_init_level';

    protected $guarded = [];

    // 用户类型：白名单
    const USER_TYPE_WHITELIST = 1;
    // 用户类型：非白名单
    const USER_TYPE_NON_WHITELIST = 2;
    // 用户类型：s1历史老客户名单
    const USER_TYPE_OLD_CUSTOMER_S1 = 3;
    // 用户类型：跨品牌白名单
    const USER_TYPE_GLOBAL_MERCHANT= 4;
    // 用户类型
    const USER_TYPE = [
        self::USER_TYPE_WHITELIST => '白名单',
        self::USER_TYPE_NON_WHITELIST => '非白名单',
        self::USER_TYPE_OLD_CUSTOMER_S1 => 's1历史老客户名单',
        self::USER_TYPE_GLOBAL_MERCHANT => '跨品牌白名单',
    ];

    // 收款方式：有卡
    const PAYMENT_TYPE_BANKCARD = 1;
    // 收款方式：无卡
    const PAYMENT_TYPE_NON_BANKCARD = 2;
    // 收款方式：电子钱包
    const PAYMENT_TYPE_EWALLET = 3;
    // 收款方式
    const PAYMENT_TYPE = [
        self::PAYMENT_TYPE_BANKCARD => '有卡',
        self::PAYMENT_TYPE_NON_BANKCARD => '无卡',
        self::PAYMENT_TYPE_EWALLET => '电子钱包',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 白名单 & 有卡
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereWhitelistAndIsCard(Builder $query): Builder
    {
        return $query->where('user_type', self::USER_TYPE_WHITELIST)
            ->where('payment_type', self::PAYMENT_TYPE_BANKCARD);
    }

    /**
     * 白名单 & 无卡
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereWhitelistAndNonCard(Builder $query): Builder
    {
        return $query->where('user_type', self::USER_TYPE_WHITELIST)
            ->where('payment_type', self::PAYMENT_TYPE_NON_BANKCARD);
    }

    /**
     * 非白名单 & 有卡
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereNonWhitelistAndIsCard(Builder $query): Builder
    {
        return $query->where('user_type', self::USER_TYPE_NON_WHITELIST)
            ->where('payment_type', self::PAYMENT_TYPE_BANKCARD);
    }

    /**
     * 非白名单 & 无卡
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWhereNonWhitelistAndNonCard(Builder $query): Builder
    {
        return $query->where('user_type', self::USER_TYPE_NON_WHITELIST)
            ->where('payment_type', self::PAYMENT_TYPE_NON_BANKCARD);
    }

    /**
     * 判断等级是否被配置成初始化等级
     *
     * @param int $level
     *
     * @return bool
     */
    public static function existLevel(int $level): bool
    {
        return self::query()->where('clm_level', $level)->exists();
    }
}
