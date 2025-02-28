<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\MarketingPhoneReport
 *
 * @property int $id
 * @property string|null $report_date 报告日期
 * @property int|null $saler_id 销售员(staff.id)
 * @property int|null $total 总数
 * @property int|null $agree_number 同意数
 * @property int|null $call_reject_number 呼叫拒绝数
 * @property int|null $miss_call_number 未接听数
 * @property int|null $wrong_number 号码错误数
 * @property int|null $apply_number 进件数
 * @property int|null $pass_number 审核通过数
 * @property int|null $reject_number 拒绝数
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereAgreeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereApplyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereCallRejectNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereMissCallNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport wherePassNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereRejectNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereSalerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneReport whereWrongNumber($value)
 * @mixin \Eloquent
 */
class MarketingPhoneReport extends Model {

    use StaticModel;

    protected $table = 'crm_marketing_phone_report';

}
