<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 10:24
 */

namespace Common\Models\Approve;


use Admin\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Utils\Code\OrderStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApprovePool
 *
 * @property int $id
 * @property int $order_id
 * @property int $type 审批类型 1初审 2电审
 * @property int $grade 订单等级(审批顺序)
 * @property string $order_no 订单编号
 * @property string|null $telephone 用户手机号码
 * @property string|null $order_type 订单类型
 * @property string|null $order_status 订单状态
 * @property string|null $pass_time 机审/初审 通过时间
 * @property int $wait_time 订单需要电审的时间,计算审批单等待时间
 * @property int $status 审批中状态 0.未审批 1.审批中 2.审批完成 默认0
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApproveUserPool $approveUserPool
 * @property-read Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereGrade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool wherePassTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereWaitTime($value)
 * @mixin \Eloquent
 * @property string|null $username 用户名称
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereUsername($value)
 * @property string|null $order_created_time 订单生成时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereOrderCreatedTime($value)
 * @property int|null $approve_wait_time 审批等待时间,进入审批人员审批池的等待时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereApproveWaitTime($value)
 * @property int|null $approve_at 放入用户审批池时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereApproveAt($value)
 * @property int|null $user_id 用户id
 * @property int|null $manual_time 人工审批时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereManualTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereUserId($value)
 * @property int|null $sort
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereSort($value)
 * @property int|null $risk_pass_time 机审通过时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereRiskPassTime($value)
 * @property-read \Common\Models\Approve\ApprovePoolLog $approvePoolLog
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool show()
 * @property int|null $merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApprovePool whereMerchantId($value)
 */
class ApprovePool extends Model
{
    use StaticModel;

    /**
     * 电审超时时间
     */
    const APPROVE_IS_ELECTRIC = 86400;

    /**
     * 非电审超时时间
     */
    const APPROVE_NOT_ELECTRIC = 86400;

    /**
     * 审批类型 初审
     */
    const ORDER_FIRST_GROUP = 1;

    /**
     * 审批类型 电审
     */
    const ORDER_CALL_GROUP = 2;

    /**
     * 初审订单等级 待人审
     */
    const GRADE_FIRST_APPROVE = 1;

    /**
     * 初审订单等级 补充资料
     */
    const GRADE_SUPPLEMENT_APPROVE = 2;

    /**
     * 电审审订单等级 待电审
     */
    const GRADE_CALL_APPROVE = 3;

    /**
     * 电审审订单等级 电二审一次
     */
    const GRADE_SECOND_CALL_APPROVE = 4;

    /**
     * 电审审订单等级 电二审二次
     */
    const GRADE_SECOND_CALL_TWICE_APPROVE = 5;

    /**
     * 最大分单数量
     */
    const APPROVE_POLICY_NUM = 5;

    /**
     * 最大电审次数
     */
    const MAX_CALL_TIME = 3;

    /**
     * 状态 未审批
     */
    const STATUS_WAITING = 0;

    /**
     * 状态 审批中
     */
    const STATUS_CHECKING = 1;

    /**
     * 状态 通过
     */
    const STATUS_PASS = 2;

    /**
     * 状态 审批取消
     */
    const STATUS_CANCEL = 3;

    /**
     * 状态 未达到审批条件
     */
    const STATUS_NOT_CONDITION = 4;

    /**
     * 状态 已回退
     */
    const STATUS_RETURN = 5;

    /**
     * 状态 已拒绝
     */
    const STATUS_REJECT = 6;

    /**
     * 状态 已关闭
     */
    const STATUS_CLOSED = 7;

    /**
     * 状态 订单状态异常
     */
    const STATUS_EXCEPTION = 8;

    /**
     * 状态 等待推送
     */
    const STATUS_WAITING_PUSH = 9;

    /**
     * 状态 电审未接听
     */
    const STATUS_NO_ANSWER = 10;

    /**
     * 状态 初审(初审-已补充资料)已通过
     */
    const STATUS_FIRST_APPROVE_PASS = 11;

    /**
     * 旧单没有记录审批结果
     */
    const STATUS_OLD = -1;

    /**
     * 不在审批查看类表展示
     */
    const NOT_SHOW_IN_CHECK_LIST = [
        self::STATUS_EXCEPTION,
        self::STATUS_OLD,
        self::STATUS_WAITING_PUSH,
    ];
    
    const TAGS_FIRST_TWICE = "FIRST_TWICE";
    const TAGS_SECOND_TWICE = "SECOND_TWICE";
    const AUTO_CALL_STATUS_FIRST_WAIT = "FIRST_WAIT";
    const AUTO_CALL_STATUS_FIRST_PASS = "FIRST_PASS";
    const AUTO_CALL_STATUS_FIRST_CALLING = "FIRST_CALLING";
    const AUTO_CALL_STATUS_TWICE_WAIT = "TWICE_WAIT";
    const AUTO_CALL_STATUS_TWICE_PASS = "TWICE_PASS";
    const AUTO_CALL_STATUS_TWICE_CALLING = "TWICE_CALLING";
    const AUTO_CALL_STATUS_TWICE_FAIL = "TWICE_FAIL";
    const AUTO_CALL_STATUS_PASS = [
        self::AUTO_CALL_STATUS_FIRST_PASS,
        self::AUTO_CALL_STATUS_TWICE_PASS,
    ];
    
    const ORDER_TYPE = [
        "CCO" => "CCO",
        "C2T" => "C2T",
    ];

    /**
     * @var string
     */
    protected $table = 'approve_pool';

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
     * @return array
     */
    public function textRules()
    {
        return [];
    }

    /**
     * 获取未审批的订单
     * @param $limit
     * @param int $offset
     * @param null|string $type
     * @return \Illuminate\Support\Collection
     */
    public function getWaitingCheckList($limit, $offset = 0, $type = null)
    {
        return static::model()
            ->select("approve_pool.*")
            ->join("order", "order.id", "approve_pool.order_id")
            ->where('approve_pool.status', ApprovePool::STATUS_WAITING)
            ->when($type, function ($query) use ($type) {
                $query->where('approve_pool.type', $type);
            })
            ->whereNotIn("approve_pool.id", ApproveUserPool::model()->whereIn("status", [0,11])->pluck('approve_pool_id')->toArray())
            ->offset($offset * $limit)
            ->orderBy('approve_pool.sort', 'DESC')
            ->orderBy("order.signed_time")
            ->limit($limit)
            ->get();
    }

    /**
     *
     * 这种方法关联 approveUserPool可能会取错数据
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @deprecated
     */
    public function approveUserPool()
    {
        return $this->hasOne(ApproveUserPool::class, 'approve_pool_id', 'id')
            ->orderBy('id', 'DESC')
            ->withDefault();
    }

    /**
     * 这种方法关联 approvePoolLog可能会取错数据
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @deprecated
     */
    public function approvePoolLog()
    {
        return $this->hasOne(ApprovePoolLog::class, 'approve_pool_id', 'id')
            ->orderBy('id', 'DESC')
            ->withDefault();
    }

    /**
     * @param $status
     * @return bool|mixed
     */
    public function updateStatus($status)
    {
        return $this->update(['status' => $status, 'sort' => 0]);
    }

    /**
     * @param null|integer $type
     * @return mixed|string
     */
    public function getApproveType($type = null)
    {
        if (is_null($type)) {
            $type = $this->type;
        }
        return static::approveTypeList()[$type] ?? '';
    }

    /**
     * @return array
     */
    public static function approveTypeList()
    {
        return [
            static::ORDER_FIRST_GROUP => t('初审', 'approve'),
            static::ORDER_CALL_GROUP => t('电审', 'approve'),
        ];
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

    /**
     * @return array
     */
    public static function approveStatusList()
    {
        return [
            static::STATUS_WAITING => t('待审批', 'approve'),
            static::STATUS_CHECKING => t('审批中', 'approve'),
            static::STATUS_RETURN => t('已回退', 'approve'),
            static::STATUS_REJECT => t('已拒绝', 'approve'),
            static::STATUS_CLOSED => t('已关闭', 'approve'),
            static::STATUS_CANCEL => t('已取消', 'approve'),
            static::STATUS_PASS => t('已通过', 'approve'),
            static::STATUS_NOT_CONDITION => t('待审批', 'approve'),
            static::STATUS_NO_ANSWER => t('未接听', 'approve'),
            static::STATUS_FIRST_APPROVE_PASS => t('已通过', 'approve'),
        ];
    }

    /**
     * @param null $status
     * @return mixed
     */
    public function getApproveStatusText($status = null)
    {
        if (is_null($status)) {
            $status = $this->status;
        }
        return static::approveStatusList()[$status] ?? '';
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShow($query)
    {
        return $query->whereNotIn('approve_pool.status', [static::STATUS_EXCEPTION, static::STATUS_OLD]);
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order($class = Order::class)
    {
        return $this->belongsTo($class, 'order_id', 'id');
    }
}
