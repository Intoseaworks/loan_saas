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
 * Common\Models\Crm\CustomerEmail
 *
 * @property int $id
 * @property int|null $customer_id 客户ID
 * @property int|null $user_id 用户ID
 * @property string|null $email Email
 * @property int|null $admin_id 管理员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerEmail whereUserId($value)
 * @mixin \Eloquent
 */
class CustomerEmail extends Model {

    use StaticModel;

    protected $table = 'crm_customer_email';

}
