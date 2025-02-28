<?php

/**
 * 
 * 
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\CrmMarketingTask
 *
 * @property int $id 任务ID标识
 * @property string|null $task_name 任务名
 * @property mixed|null $task_type 任务类型(SMS=短信;PHONE=短语）
 * @property int|null $customer_type 用户群体customer.type
 * @property string|null $customer_status 用户状态
 * @property string|null $clm_level clm等级要求
 * @property string|null $batch_id 批次号
 * @property int|null $check_blacklist 排除黑名单(0=不排除;1=排除)
 * @property int|null $check_greylist 排除灰名单(0=不排除;1=排除)
 * @property int|null $max_overdue_days 最大预期天数
 * @property int|null $telephone_status 手机状态(0:未检测;1=正常;2=无效;3=停机)
 * @property string|null $last_login 最后登陆时间（-1:未注册;已注册:{"start":"1","end":"10"}]
 * @property string|null $send_time 发送时间数组
 * @property string|null $frequency 发送频次
 * @property string|null $frequency_detail 发送频次详情
 * @property int|null $sms_template_id 短信模板
 * @property string|null $phone_time_interval 电话营销距上次时间间隔(天)
 * @property string|null $phone_stop_term 电话&短信营销停止条件
 * @property string|null $phone_stop_term_detail 停止限定 进入电销N天
 * @property int|null $phone_status_stop_days 当前状态停留时间0不计算
 * @property int|null $sms_run_times 执行次数
 * @property int|null $send_total 下发总数
 * @property int|null $success_total 成功数
 * @property int|null $apply_total 进件数
 * @property int|null $paid_total 放款数
 * @property int|null $agree_total 已同意数
 * @property int|null $status 1=正常;0=失效
 * @property int|null $admin_id 创建ID
 * @property int|null $phone_total 名单总数
 * @property int|null $phone_assign_total 分配总数
 * @property int|null $phone_finish_total 已完成总数
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereAgreeTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereApplyTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereCheckBlacklist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereCheckGreylist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereClmLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereCustomerStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereCustomerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereFrequencyDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereMaxOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePaidTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneAssignTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneFinishTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneStatusStopDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneStopTerm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneStopTermDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneTimeInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask wherePhoneTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereSendTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereSendTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereSmsRunTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereSmsTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereSuccessTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereTaskName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereTaskType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereTelephoneStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrmMarketingTask whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrmMarketingTask extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效
    const STATUS = [
        self::STATUS_FORGET => '暂停',
        self::STATUS_NORMAL => '正常',
    ];
    const TYPE_SMS = "SMS";
    const TYPE_PHONE = "PHONE";
    const STOP_TERM_STATUS_CHANGE = 1; //短信-状态变更
    const STOP_TERM_ENTER_TELEMARKETING = 2; //短信-进入电销
    const STOP_PHONE_APPLY = 1; //电销-完件
    const STOP_PHONE_STATUS_CHANGE = 2; //电销-状态改变
    const STOP_PHONE_USER_REJECT = 3; //电销-用户拒绝
    const STOP_PHONE_ENTER_TEL_DAYS = 4; //电销-进入电销天数

    protected $table = 'crm_marketing_task';

    public function admin($class = \Common\Models\Staff\Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }

}
