<?php

namespace Common\Models\Risk;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Risk\RiskStrategyTask
 *
 * @property int $id
 * @property int|null $merchant_id 商户ID
 * @property int|null $order_id 订单ID
 * @property int|null $strategy_step 策略节点 1表示节点1,2表示节点2
 * @property int|null $status 规则平台执行状态
 * @property int|null $execute 策略应用执行状态
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read RiskStrategyResult $lastRiskStrategyResult
 * @property-read Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereExecute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereStrategyStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RiskStrategyTask extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';

    const STATUS_WAIT = 0;
    const STATUS_FINISH = 1;
    const STATUS_SKIP = 3;
    /** 策略节点1 */
    const RISK_STRATEGY_STEP_1 = 1;
    /** 策略节点2 */
    const RISK_STRATEGY_STEP_2 = 2;

    /**
     * @var string
     */
    public $table = 'risk_strategy_task';

    /**
     * @var bool
     */
    public $timestamps = false;
    protected $primaryKey = 'id';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $dates = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                "merchant_id" => MerchantHelper::getMerchantId(),
                "order_id",
                "strategy_step",
                "status" => self::STATUS_WAIT,
                "execute" => self::STATUS_WAIT,
                "created_at" => DateHelper::dateTime(),
                "updated_at" => DateHelper::dateTime(),
            ],
        ];
    }

    /**
     * 获取待处理任务 exec=0
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getWaitTask()
    {
        return self::query()->whereExecute(self::STATUS_WAIT)->get();
    }

    /**
     * 规则平台完结任务
     * @param $taskId
     * @return bool|int
     */
    public static function toFinishStatus($taskId)
    {
        return self::whereId($taskId)->update(['status' => self::STATUS_FINISH]);
    }

    /**
     * 规则平台完结Exec
     * @param $taskId
     * @return bool|int
     */
    public static function toFinishExec($taskId)
    {
        return self::whereId($taskId)->update(['execute' => self::STATUS_FINISH]);
    }

    /**
     * 完结当前任务 不执行后续操作
     * @param $taskId
     * @return bool|int
     */
    public static function toSkipTask($taskId)
    {
        return self::whereId($taskId)->update(['status' => self::STATUS_FINISH, 'execute' => self::STATUS_SKIP]);
    }

    /**
     * 创建task
     * @param $order
     * @param $step
     * @return bool|RiskStrategyTask
     */
    public function create($order, $step)
    {
        $params = [
            'order_id' => $order->id,
            'strategy_step' => $step
        ];
        return self::updateOrCreateModel(self::SCENARIO_CREATE, $params, $params);
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }

    /**
     * 获取最新的策略平台结果
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastRiskStrategyResult($class = RiskStrategyResult::class)
    {
        return $this->hasOne($class, 'order_id', 'order_id')->orderBy('id', 'desc');
    }
}
