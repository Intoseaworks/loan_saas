<?php

namespace Risk\Common\Models\Business\RiskData;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\RiskData\RiskDataIndex
 *
 * @property int $id
 * @property int|null $user_id 用户ID
 * @property int|null $order_id 订单ID
 * @property string|null $index_name 指标名称
 * @property string|null $index_value 指标值
 * @property string|null $comment 备注
 * @property string|null $update_at
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereIndexName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereIndexValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereUpdateAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskDataIndex whereUserId($value)
 * @mixin \Eloquent
 */
class RiskDataIndex extends BusinessBaseModel
{
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'risk_data_index';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
}
