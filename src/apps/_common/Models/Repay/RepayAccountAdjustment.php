<?php

namespace Common\Models\Repay;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * 
 * User: zy
 * Date: 20-11-10
 * Time: 下午8:47
 *
 * @property int $id 调账表-主键自增长id
 * @property int $repay_detail_id 还款明细->id
 * @property int $trade_id 交易id
 * @property int $uid 调入的用户
 * @property int $order_id 调入的订单
 * @property int $repayment_plan_id 调入的还款计划
 * @property int $before_repayment_plan_id 原还款计划
 * @property int $type 调账类型
 * @property string $remark 调账原因
 * @property int $admin_id 操作人
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $created_at 操作时间
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereBeforeRepaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereRepayDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereRepaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereTradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RepayAccountAdjustment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RepayAccountAdjustment extends Model
{
    use StaticModel;

    protected $table = 'repay_account_adjustment';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    const SCENARIO_CREATE = 'create';

    /**
     * 定义调账类型
     */
    //调账
    const TYPE_IS_ADJUSTMENT = 1;

    //调账并结清
    const TYPE_IS_COMPLETE = 2;

    //撤销还款记录
    const TYPE_IS_REVOKE = 3;

    const TYPE = [
        self::TYPE_IS_ADJUSTMENT => '调账',
        self::TYPE_IS_COMPLETE   => '调账并结清',
        self::TYPE_IS_REVOKE     => '撤销还款记录',
    ];

    protected function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'trade_id',
                'repay_detail_id',
                'order_id',
                'uid',
                'repayment_plan_id',
                'before_repayment_plan_id',
                'type',
                'remark',
                'admin_id',
                'created_at',
            ],
        ];
    }

    public function add($data)
    {
        return self::model(self::SCENARIO_CREATE)->saveModel($data);
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'uid');
    }
}
