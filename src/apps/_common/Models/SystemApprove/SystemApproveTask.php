<?php

namespace Common\Models\SystemApprove;

use Common\Models\Merchant\Merchant;
use Common\Models\Order\Order;
use Common\Services\Risk\RiskServer;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\SystemApprove\SystemApproveTask
 *
 * @property int $id
 * @property int $merchant_id 商户id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $user_type 用户类型 0:新用户  1:老用户
 * @property string|null $task_no 任务编号
 * @property string $status 状态
 * @property string $result 结果
 * @property int $hit_rule_cnt hit_rule命中数
 * @property string|null $hit_rule 命中拒绝规则code
 * @property string|null $extra_code 额外标识
 * @property string $description 注释说明
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 更新时间
 * @property-read Merchant $merchant
 * @property-read Order $order
 * @method static Builder|SystemApproveTask newModelQuery()
 * @method static Builder|SystemApproveTask newQuery()
 * @method static Builder|SystemApproveTask orderByCustom($defaultSort = null)
 * @method static Builder|SystemApproveTask query()
 * @method static Builder|SystemApproveTask whereCreatedAt($value)
 * @method static Builder|SystemApproveTask whereDescription($value)
 * @method static Builder|SystemApproveTask whereExtraCode($value)
 * @method static Builder|SystemApproveTask whereHitRule($value)
 * @method static Builder|SystemApproveTask whereHitRuleCnt($value)
 * @method static Builder|SystemApproveTask whereId($value)
 * @method static Builder|SystemApproveTask whereMerchantId($value)
 * @method static Builder|SystemApproveTask whereOrderId($value)
 * @method static Builder|SystemApproveTask whereResult($value)
 * @method static Builder|SystemApproveTask whereStatus($value)
 * @method static Builder|SystemApproveTask whereTaskNo($value)
 * @method static Builder|SystemApproveTask whereUpdatedAt($value)
 * @method static Builder|SystemApproveTask whereUserId($value)
 * @method static Builder|SystemApproveTask whereUserType($value)
 * @mixin \Eloquent
 */
class SystemApproveTask extends Model
{
    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'system_approve_task';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];

    /** 用户类型：新用户 */
    const USER_TYPE_NEW = 0;
    /** 用户类型：老用户 */
    const USER_TYPE_OLD = 1;
    /** @var array 用户类型 */
    const USER_QUALITY = [
        self::USER_TYPE_NEW => '新用户',
        self::USER_TYPE_OLD => '老用户',
    ];
    /** 用户类型关联订单表用户类型 */
    const USER_QUALITY_RELATE = [
        Order::QUALITY_NEW => self::USER_TYPE_NEW,
        Order::QUALITY_OLD => self::USER_TYPE_OLD,
    ];

    /** 状态：创建 等待数据完善 */
    const STATUS_CREATE = 'CREATE';
    /** 状态：机审中 */
    const STATUS_PROCESSING = 'PROCESSING';
    /** 状态：任务完结 */
    const STATUS_FINISH = 'FINISH';
    /** 状态：异常 */
    const STATUS_EXCEPTION = 'EXCEPTION';

    /** 结果：通过 */
    const RESULT_PASS = 'PASS';
    /** 结果：拒绝 */
    const RESULT_REJECT = 'REJECT';
    const RESULT = [
        self::RESULT_PASS => '通过',
        self::RESULT_REJECT => '拒绝',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public static function createTask($params)
    {
        $data = [
            'merchant_id' => MerchantHelper::getMerchantId(),
            'user_id' => $params['user_id'],
            'order_id' => $params['order_id'],
            'user_type' => $params['user_type'],
            'task_no' => $params['task_no'],
            'status' => SystemApproveTask::STATUS_CREATE,
        ];

        return self::create($data);
    }

    /**
     * 判断订单是否在机审中
     * @param $orderId
     * @return bool
     */
    public static function inSystemApprove($orderId)
    {
        return self::query()->where('order_id', $orderId)
            ->whereIn('status', [self::STATUS_CREATE, self::STATUS_PROCESSING])
            ->exists();
    }

    public static function getWaitSystemApprove()
    {
        return self::query()->where('status', self::STATUS_CREATE)
            ->whereHas('order', function (Builder $query) {
                $query->where('status', Order::STATUS_SYSTEM_APPROVING);
            })->get();
    }

    public function toProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        return $this->save();
    }

    public function finishTask($status, $result, $hitRuleCode = [], $taskDesc = '')
    {
        switch ($status) {
            case RiskServer::STATUS_FINISH:
                $this->status = self::STATUS_FINISH;
                break;
            case RiskServer::STATUS_EXCEPTION:
                $this->status = self::STATUS_EXCEPTION;
                break;
            default:
                throw new \Exception('完结状态错误');
        }

        if ($hitRuleCode) {
            $this->hit_rule = implode(',', $hitRuleCode);
            $this->hit_rule_cnt = count($hitRuleCode);
        }

        $this->result = $result;
        $this->description = $taskDesc;
        return $this->save();
    }

    public static function getByTaskNo($taskNo)
    {
        return self::query()->where(['task_no' => $taskNo])->first();
    }

    public static function getRejectReason($orderId)
    {
        $record = self::query()->where('order_id', $orderId)
            ->orderByDesc('id')
            ->value('hit_rule');

        $hitRuleCode = explode(',', $record);
        if (!$record || !$hitRuleCode) {
            return [];
        }

        return SystemApproveRule::showTypeClassifyReason($hitRuleCode);
    }

    /**
     * 关联商户表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
