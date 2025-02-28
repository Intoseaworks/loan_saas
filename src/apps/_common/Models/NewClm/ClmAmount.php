<?php

namespace Common\Models\NewClm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\NewClm\ClmAmount
 *
 * @property int $id id
 * @property int $clm_level 等级
 * @property string $clm_amount 等级对应额度
 * @property string $clm_interest_discount 等级对应费率优惠(百分比)
 * @property string $alias 别名
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereClmAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereClmInterestDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereClmLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmAmount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmAmount extends Model
{
    use StaticModel;

    protected $table = 'new_clm_amount';

    protected $guarded = [];

    // 最小可贷额度
    const MIN_CLM_AMOUNT = 100;

    /**
     * 获取系统最大clm等级
     *
     * @return mixed
     */
    public static function getMaxLevel()
    {
        return self::query()->max('clm_level');
    }

    /**
     * 获取系统最大clm等级amount
     *
     * @return mixed
     */
    public static function getMaxAmount()
    {
        return self::query()->max('clm_amount');
    }

    /**
     * 获取系统最小clm等级
     *
     * @return mixed
     */
    public static function getMinLevel()
    {
        return self::query()->min('clm_level');
    }

    /**
     * 处理等级
     *
     * @param $level
     *
     * @return mixed
     */
    public static function disposeLevel($level)
    {
        $systemMaxLevel = ClmAmount::getMaxLevel();
        $systemMinLevel = ClmAmount::getMinLevel();

        return min($systemMaxLevel, max($systemMinLevel, $level));
    }

    /**
     * 根据等级获取记录
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLevelAndAmount()
    {
        return self::query()->select(['clm_level', 'clm_amount'])->orderBy('clm_level')->get();
    }

    /**
     * 根据等级获取对应额度
     *
     * @param $level
     *
     * @return mixed
     */
    public static function getAmountByLevel($level)
    {
        return self::query()->where('clm_level', $level)->value('clm_amount');
    }

    /**
     * 根据等级获取金额配置
     * @param $level
     *
     * @return static|null
     */
    public static function getByLevel($level): ?self
    {
        return self::query()->where('clm_level', $level)->first();
    }

    /**
     * 根据等级获取金额配置(自适应)
     *
     * @param $level
     *
     * @return static|null
     */
    public static function getAutoByLevel($level): ?self
    {
        $clmAmount = self::getByLevel($level);

        if (!is_null($clmAmount)) {
            return $clmAmount;
        }

        // 如果不存在等级对应金额，自适应取最高或最低。如：对应等级在金额表已经移除等情况
        $systemMaxLevel = ClmAmount::getMaxLevel();
        if ($level > $systemMaxLevel) {
            return self::getByLevel($systemMaxLevel);
        }

        $systemMinLevel = ClmAmount::getMinLevel();
        return self::getByLevel($systemMinLevel);
    }

    public static function getMaxLevelByAmount($amount){
        return self::query()->where("clm_amount", "<=", $amount)->orderByDesc('clm_amount')->first();
    }
}
