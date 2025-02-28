<?php

namespace Common\Models\Risk;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Risk\RiskStrategyResult
 *
 * @property int $id
 * @property int|null $merchant_id 商户ID
 * @property int|null $order_id 订单ID
 * @property int|null $strategy_step 策略节点
 * @property string|null $ab_test 决策面
 * @property int|null $score 评分--评分卡
 * @property int|null $score_rank 评级--评分卡
 * @property int|null $rule_apply 准入
 * @property int|null $rule_blacklist 黑名单
 * @property int|null $rule_antifraud 反欺诈
 * @property int|null $rule_multiapply 多头申请
 * @property int|null $rule_abnormalbankcard 银行卡异常
 * @property int|null $rule_contactlist 通讯录异常
 * @property int|null $rule_photo 相册异常
 * @property int|null $rule_applist app列表异常
 * @property int|null $rule_badbankcard 银行卡命中历史逾期客户
 * @property string|null $result 审批结果
 * @property string|null $reject_code 规则编码--规则集
 * @property int|null $skip_risk_control2 是否跳过风控节点2
 * @property int|null $skip_manual_approval 是否跳过人审
 * @property string|null $external_data 对接外部数据
 * @property string|null $hit_rule 触发的规则-规则集
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereAbTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereExternalData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereHitRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRejectCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleAbnormalbankcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleAntifraud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleApplist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleApply($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleBadbankcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleBlacklist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleContactlist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRuleMultiapply($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereRulePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereScoreRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereSkipManualApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereSkipRiskControl2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyResult whereStrategyStep($value)
 * @mixin \Eloquent
 */
class RiskStrategyResult extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';

    /**
     * @var string
     */
    public $table = 'risk_strategy_result';

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
                "ab_test",
                "score",
                "score_rank",
                "rule_apply",
                "rule_blacklist",
                "rule_antifraud",
                "rule_multiapply",
                "rule_abnormalbankcard",
                "rule_contactlist",
                "rule_photo",
                "rule_applist",
                "rule_badbankcard",
                "result",
                "reject_code",
                "skip_risk_control2",
                "skip_manual_approval",
                "external_data",
                "hit_rule",
                "created_at" => $this->getDate(),
            ],
        ];
    }

    public function create($data)
    {
        return self::updateOrCreateModel(self::SCENARIO_CREATE, array_only($data, ['order_id', 'strategy_step']), $data);
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }
}
