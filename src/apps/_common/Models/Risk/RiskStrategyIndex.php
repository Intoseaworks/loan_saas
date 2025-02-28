<?php

namespace Common\Models\Risk;

use Common\Models\Order\Order;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Risk\RiskStrategyIndex
 *
 * @property int $id
 * @property int|null $merchant_id 商户ID
 * @property int|null $order_id 订单ID
 * @property int|null $strategy_step 策略节点
 * @property string|null $var 指标名称
 * @property string|null $value 指标值
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereStrategyStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskStrategyIndex whereVar($value)
 * @mixin \Eloquent
 */
class RiskStrategyIndex extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';

    /**
     * @var string
     */
    public $table = 'risk_strategy_index';

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
                "var",
                "value",
                "created_at" => $this->getDate(),
            ],
        ];
    }

    public function create($data)
    {
        return self::updateOrCreateModel(self::SCENARIO_CREATE, array_only($data, ['order_id', 'strategy_step', 'var']), $data);
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
