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
 * Common\Models\Crm\CustomerTelephone
 *
 * @property int $id
 * @property int|null $customer_id crm客户ID
 * @property string|null $telephone 手机号
 * @property int|null $telephone_status 手机状态(-1:未检测;1=正常;2=无效;3=停机)
 * @property string|null $telephone_test_time 手机号检测时间
 * @property int|null $user_id 用户ID
 * @property int|null $status 是否有效(1=有效;0=失效)
 * @property int|null $admin_id 管理员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereTelephoneStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereTelephoneTestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerTelephone whereUserId($value)
 * @mixin \Eloquent
 */
class CustomerTelephone extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效
    const TELEPHONE_STATUS_NORMAL = 1; //正常
    const TELEPHONE_STATUS_UNDETECTED = 0; //未检测
    const TELEPHONE_STATUS_STOP = 3; //停机

    protected $table = 'crm_customer_telephone';

}
