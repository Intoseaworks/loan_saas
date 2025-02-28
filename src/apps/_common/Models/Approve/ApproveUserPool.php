<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-11
 * Time: 10:25
 */

namespace Common\Models\Approve;


use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ApproveUserPool
 *
 * @property int $id
 * @property int $admin_id 审批人员
 * @property int $approve_pool_id approve_pool 主键
 * @property int $order_id 订单id
 * @property string|null $approve_at 进入队列时间
 * @property int $status 是否完成 0.审批中 1.已审批 2.已过期 默认 0
 * @property int|null $start_at 审批时间开始时间
 * @property int|null $finish_at 审批结束时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Common\Models\Approve\ApprovePool $approvePool
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereApproveAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereApprovePoolId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereFinishAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Approve\ApproveResultSnapshot[] $snapshoot
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool done()
 * @property int $merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ApproveUserPool whereMerchantId($value)
 * @property-read int|null $snapshoot_count
 */
class ApproveUserPool extends Model
{
    use StaticModel;

    /**
     * 状态 审批中
     */
    const STATUS_CHECKING = 0;

    /**
     * 状态 已审批
     */
    const STATUS_DONE = 1;

    /**
     * 状态 订单绑定未审批超时
     */
    const STATUS_BIND_TIMEOUT = 2;

    /**
     * 状态 后台人工取消
     */
    const STATUS_CANCEL = 3;

    /**
     * 状态 初审通过
     */
    const STATUS_FIRST_PASS = 4;

    /**
     * 状态 初审回退
     */
    const STATUS_FIRST_RETURN = 5;

    /**
     * 状态 初审拒绝
     */
    const STATUS_FIRST_REJECT = 6;

    /**
     * 状态 电审通过
     */
    const STATUS_CALL_PASS = 7;

    /**
     * 状态 电审取消
     */
    const STATUS_CALL_RETURN = 8;

    /**
     * 状态 电审拒绝
     */
    const STATUS_CALL_REJECT = 9;

    /**
     * 状态 订单状态异常
     */
    const STATUS_EXCEPTION = 10;

    /**
     * 状态 未接听 挂起状态(处理审批挂起列表)
     */
    const STATUS_NO_ANSWER = 11;

    /**
     * 状态 订单逾期,定时任务关闭
     */
    const STATUS_CRONTAB_CANCEL = 12;

    /**
     * 状态 用户APP取消订单
     */
    const STATUS_USER_APP_CANCEL = 13;

    /**
     * 状态 停止审批流失
     */
    const STATUS_STOP_WORK = 14;

    /**
     * 状态分类 通过
     */
    const SORT_PASS = [
        self::STATUS_FIRST_PASS,
        self::STATUS_CALL_PASS,
    ];

    /**
     * 状态分类 回退
     */
    const SORT_RETURN = [
        self::STATUS_FIRST_RETURN,
        self::STATUS_NO_ANSWER,
    ];

    /**
     * 状态分类 拒绝
     */
    const SORT_REJECT = [
        self::STATUS_FIRST_REJECT,
        self::STATUS_CALL_REJECT,
    ];

    /**
     * 状态分类 流水
     */
    const SORT_MISSED = [
        self::STATUS_BIND_TIMEOUT,
        self::STATUS_STOP_WORK,
        self::STATUS_CALL_RETURN,
        self::STATUS_CANCEL,
        self::STATUS_CRONTAB_CANCEL,
        self::STATUS_USER_APP_CANCEL,
    ];

    // 质检审批的状态
    const CAN_QUALITY_STATUS = [
        self::STATUS_FIRST_PASS,
        self::STATUS_FIRST_RETURN,
        self::STATUS_FIRST_REJECT,
        self::STATUS_CALL_PASS,
        self::STATUS_CALL_RETURN,
        self::STATUS_CALL_REJECT,
        self::STATUS_NO_ANSWER,
    ];

    /**
     * @var string
     */
    protected $table = 'approve_user_pool';

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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function snapshoot()
    {
        return $this->hasMany(ApproveResultSnapshot::class, 'approve_user_pool_id', 'id');
    }

    /**
     * 获取正在审批和挂起状态的订单
     * @param null $userId
     * @return \Illuminate\Support\Collection
     */
    public function getCheckingList($userId = null, $allocateTime = false)
    {
        $query = static::whereIn('status', [static::STATUS_CHECKING, static::STATUS_NO_ANSWER])
            ->when($userId, function ($query) use ($userId) {
                $query->where('admin_id', $userId);
            });
        if($allocateTime){
            $query->where("created_at", "<", $allocateTime);
        }
        return $query->get();
    }

    /**
     * 获取工作中的审批人员
     * @param null $userId
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function getWorkingUser($userId = null, $columns = ['admin_id'])
    {
        return static::where(['status' => static::STATUS_CHECKING])
            ->select($columns)
            ->groupBy('admin_id')
            ->when($userId, function ($query) use ($userId) {
                $query->where('admin_id', $userId);
            })
            ->get();
    }

    /**
     * @param $status
     * @return bool|mixed
     */
    public function updateStatus($status)
    {
        return $this->update(['status' => $status,]);
    }

    /**
     * @param null $status
     * @return mixed|string
     */
    public function getStatusText($status = null)
    {
        if (is_null($status)) {
            $status = $this->status;
        }

        return $this->getStatusList()[$status] ?? '';
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        return [
            static::STATUS_FIRST_PASS => 'First approval pass',
            static::STATUS_FIRST_RETURN => 'First approval return',
            static::STATUS_FIRST_REJECT => 'First approval reject',
            static::STATUS_CALL_PASS => 'Call approval pass',
            static::STATUS_CALL_RETURN => 'Call approval return',
            static::STATUS_CALL_REJECT => 'Call approval reject',
            static::STATUS_NO_ANSWER => 'Call approval No answer',
        ];
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDone($query)
    {
        return $query->whereNotIn('approve_user_pool.status', [static::STATUS_CHECKING, static::STATUS_EXCEPTION]);
    }

    /**
     * @param null|integer $status
     * @return mixed|string
     */
    public function getMissReasonText($status = null)
    {
        if (is_null($status)) {
            $status = $this->status;
        }

        return $this->getMissReasonList()[$status] ?? '';
    }

    /**
     * @return array
     */
    public function getMissReasonList()
    {
        return [
            static::STATUS_BIND_TIMEOUT => t('订单绑定未审流失', 'approve'),
            static::STATUS_STOP_WORK => t('停止审批流失', 'approve'),
            static::STATUS_CALL_RETURN => t('人工取消流失', 'approve'),
            static::STATUS_CANCEL => t('人工取消流失', 'approve'),
            static::STATUS_CRONTAB_CANCEL => t('人工取消流失', 'approve'),
            static::STATUS_USER_APP_CANCEL => t('用户取消流失', 'approve'),
        ];
    }


}
