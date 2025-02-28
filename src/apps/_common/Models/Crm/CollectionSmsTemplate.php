<?php

/**
 *
 *
 */

namespace Common\Models\Crm;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Crm\SmsTemplate
 *
 * @property int $id
 * @property string|null $tpl_name 模板名称
 * @property string|null $tpl_content 短信内容
 * @property int|null $status 模板状态(1=正常;0=停用)
 * @property int|null $admin_id
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereTplContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereTplName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CollectionSmsTemplate extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效

    protected $table = 'collection_contact_sms_template';

    public function admin($class = \Common\Models\Staff\Staff::class) {
        return $this->hasOne($class, 'id', 'admin_id')->orderBy('id', 'desc');
    }

}
