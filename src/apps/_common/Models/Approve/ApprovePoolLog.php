<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-10
 * Time: 14:33
 */

namespace Common\Models\Approve;


use Common\Traits\Model\GlobalScopeModel;
use Common\Utils\Code\OrderStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApprovePoolLog
 *
 * @property int $id
 * @property int $approve_pool_id
 * @property int $type 审批类型 1初审 2电审
 * @property int $grade
 * @property int|null $order_status
 * @property int|null $order_type 审批单类型
 * @property int|null $result 审批结果  0未审批
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApprovePool $approvePool
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereApprovePoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $approve_user_pool_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereApproveUserPoolId($value)
 * @property int|null $wait_time 订单审批等待时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereWaitTime($value)
 * @property int $merchant_id
 * @property int $order_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePoolLog whereOrderId($value)
 */
class ApprovePoolLog extends Model
{
    use GlobalScopeModel;

    /**
     * 未审批
     */
    const WAITING_APPROVE = 0;

    /**
     * 初审通过
     */
    const FIRST_APPROVE_RESULT_PASS = 1;

    /**
     * 初审人工拒绝
     */
    const FIRST_APPROVE_RESULT_REJECT = 2;

    /**
     * 初审待补充资料
     */
    const FIRST_APPROVE_RESULT_SUPPLEMENT = 3;

    /**
     * 电审通过
     */
    const CALL_APPROVE_RESULT_PASS = 4;

    /**
     * 电审人工拒绝
     */
    const CALL_APPROVE_RESULT_REJECT = 5;

    /**
     * 电审取消
     */
    const CALL_APPROVE_RESULT_CANCEL = 6;

    /**
     * 电审未接听
     */
    const CALL_APPROVE_RESULT_NO_ANSWER = 7;

    /**
     * 电审机审打分拒绝
     */
    const CALL_APPROVE_RESULT_RISK_REJECT = 8;

    /**
     * 初审机审打分拒绝
     */
    const FIRST_APPROVE_RESULT_RISK_REJECT = 9;

    /**
     * 电审三次取消
     */
    const CALL_APPROVE_RESULT_THREE_CANCEL = 10;

    /**
     * 电审二审一次
     */
    const CALL_APPROVE_RESULT_TWICE_ONE = 11;

    /**
     * 订单异常
     */
    const RESULT_EXCEPTION = 12;

    /**
     * 业务推送 后台人工取消
     */
    const RESULT_ORDER_CANCEL = 13;

    /**
     * 业务推送 订单逾期定时任务取消
     */
    const RESULT_CRONTAB_CANCEL = 14;

    /**
     * 业务推送 用户在APP取消订单
     */
    const RESULT_USER_APP_CANCEL = 15;

    /**
     * 电审二审二次
     */
    const CALL_APPROVE_RESULT_TWICE_TWO = 16;

    /**
     * 旧单没有记录审批结果
     */
    const RESULT_OLD = -1;

    /**
     * 取消订单合集
     */
    const CANCEL_COLLECTION = [
        self::RESULT_ORDER_CANCEL,
        self::RESULT_USER_APP_CANCEL,
    ];

    /**
     * @var string
     */
    protected $table = 'approve_pool_log';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvePool()
    {
        return $this->belongsTo(ApprovePool::class, 'approve_pool_id', 'id');
    }

    /**
     * @param $result
     * @return bool|mixed
     */
    public function updateResult($result)
    {
        return $this->update(['result' => $result,]);
    }

    /**
     * @return mixed|string
     */
    public function getOrderStatusText($status = null)
    {
        if (is_null($status)) {
            $status = $this->order_status;
        }
        return OrderStatus::getInstance()->getOrderStatusText($status);
    }
}
