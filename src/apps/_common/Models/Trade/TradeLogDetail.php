<?php

namespace Common\Models\Trade;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Trade\TradeLogDetail
 *
 * @property int $id 交易详情记录id
 * @property int $trade_log_id 关联主交易记录id
 * @property int $business_id 业务id(还款计划id,续期id)
 * @property string $business_no 业务编号(还款计划编号,,,)
 * @property float $amount 金额
 * @property string $created_time 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereBusinessNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereTradeLogId($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Trade\TradeLogDetail whereCreatedAt($value)
 * @property string|null $flag trade log detail 标记字段
 * @method static \Illuminate\Database\Eloquent\Builder|TradeLogDetail whereFlag($value)
 */
class TradeLogDetail extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';
    /**
     * @var string
     */
    protected $table = 'trade_log_detail';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    const UPDATED_AT = null;

    const FLAG_IS_FINAL_REPAY = 'IS_FINAL_REPAY';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'trade_log_id',
                'business_id',
                'business_no',
                'amount',
                'flag',
            ],
        ];
    }

    public function textRules()
    {
        return [
            'array' => [
            ],
        ];
    }

    public function sortCustom()
    {
        return [
        ];
    }

    public function addDetail($attributes)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($attributes);
    }

    public function tradeLog($class = TradeLog::class)
    {
        return $this->belongsTo($class, 'trade_log_id', 'id');
    }
}
