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
 * Common\Models\Crm\CrmMarketingBatch
 *
 * @property int $id
 * @property string|null $batch_number 批次号
 * @property string|null $indate 过期时间
 * @property mixed|null $match_rule 匹配规则
 * @property int|null $total_count 导入总数
 * @property int|null $success_count 成功总数
 * @property int|null $status 是否有效(1=有效;0=失效)
 * @property int|null $admin_id 管理员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $reg_count 注册总数
 * @property int|null $finish_count 完件总数
 * @property int|null $telephone_count 电话营销总数
 * @property int|null $sms_count 短信营销总数
 * @property int|null $indate_count 有效期内数量
 * @property string|null $last_marketing_time 最后营销时间
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereIndate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereIndateCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereLastMarketingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereMatchRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereRegCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereSmsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereTelephoneCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingBatch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmMarketingBatch extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效

    protected $table = 'crm_marketing_batch';

    public function operator($class = \Common\Models\Staff\Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }

}
