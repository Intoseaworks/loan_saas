<?php

namespace Common\Models\Clm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Clm\ClmLevel
 *
 * @property int $id
 * @property int|null $clm_level 等级值
 * @property int|null $loan_term_days_max 最大贷款天数
 * @property int|null $loan_term_days_min 最小贷款天数
 * @property string|null $loan_amount_max 最大贷款金额
 * @property string|null $loan_amount_min 最小到款金额
 * @property int|null $loan_term_day_step 贷款天数步长
 * @property string|null $loan_amount_step 贷款金额步长
 * @property string|null $service_fee_dis_rate 服务费优惠比率
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereClmLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanAmountMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanAmountMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanAmountStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanTermDayStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanTermDaysMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereLoanTermDaysMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereServiceFeeDisRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClmLevel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClmLevel extends Model {

    use StaticModel;

    /**
     * @var string
     */
    protected $table = 'clm_level';

    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $hidden = [];

}
