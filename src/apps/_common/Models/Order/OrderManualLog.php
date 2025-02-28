<?php

namespace Common\Models\Order;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderManualLog
 *
 * @package Common\Models\Order
 * @method static \Illuminate\Database\Eloquent\Builder|OrderManualLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderManualLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderManualLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderManualLog query()
 * @mixin \Eloquent
 */
class OrderManualLog extends Model
{

    use StaticModel;

    /**
     * 放款操作
     */
    const TYPE_LENDING = 1;
    /**
     * 还款操作
     */
    const TYPE_REPAYMENT = 2;

    /**
     * @var string
     */
    protected $table = 'order_manual_log';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @var bool
     */
    //public $timestamps = false;

    public function textRules()
    {
        return [];
    }

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id',
                'order_id',
                'opt_man',
                'remark',
                'order_status_old',
                'order_status',
            ]
        ];
    }

}
