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
 * Common\Models\Crm\CrmWhiteBatch
 *
 * @property int $id
 * @property string|null $batch_number 批次号
 * @property string|null $indate 过期时间
 * @property mixed|null $match_rule 匹配规则
 * @property int|null $match_blacklist 是否匹配黑名单
 * @property int|null $match_greylist_type 命中灰名单
 * @property int|null $total_count 导入总数
 * @property int|null $reg_count 注册总数
 * @property int|null $finish_count 完件总数
 * @property int|null $success_count 成功总数
 * @property int|null $telephone_count 电话营销总数
 * @property int|null $sms_count 短信营销总数
 * @property int|null $indate_count 有效期内数量
 * @property int|null $status 是否有效(1=有效;0=失效)
 * @property int|null $admin_id 管理员
 * @property string|null $last_marketing_time 最后营销时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereBatchNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereFinishCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereIndate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereIndateCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereLastMarketingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereMatchBlacklist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereMatchGreylistType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereMatchRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereRegCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereSmsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereSuccessCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereTelephoneCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereTotalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmWhiteBatch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmWhiteBatch extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_FORGET => '失效'
    ];

    protected $table = 'crm_white_batch';
    
    public function operator($class = \Common\Models\Staff\Staff::class){
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }
}
