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
 * Common\Models\Crm\CustomerFb
 *
 * @property int $id
 * @property int|null $customer_id 客户ID
 * @property string|null $reg_telephone fb注册手机号
 * @property string|null $fb_id fbID
 * @property int|null $admin_id 管理员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereFbId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereRegTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerFb whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerFb extends Model {

    use StaticModel;

    protected $table = 'crm_customer_fb';

}
