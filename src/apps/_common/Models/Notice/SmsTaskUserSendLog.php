<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Notice;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\MarketingSmsLog
 *
 * @property int $id
 * @property int|null $task_id 任务ID(crm_marketing_task.id
 * @property int|null $customer_id 客户ID(crm_customer.id
 * @property int|null $sms_template_id 短信模板ID(crm_sms_template.id)
 * @property string|null $telephone 电话
 * @property string|null $content 短信内容
 * @property int|null $status 0=待发送1=已发;2=发送成功
 * @property int|null $customer_status 当前用户状态
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereCustomerStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereSmsTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingSmsLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SmsTaskUserSendLog extends Model {

    use StaticModel;

    protected $table = 'sms_task_user_send_log';

}
