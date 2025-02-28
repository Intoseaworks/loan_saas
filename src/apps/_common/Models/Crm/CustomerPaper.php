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
 * Common\Models\Crm\CustomerPaper
 *
 * @property int $id
 * @property int|null $customer_id 客户ID
 * @property string|null $id_type 证件类型
 * @property string|null $id_number 证件编号
 * @property int|null $user_id
 * @property int|null $admin_id 管理员
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereIdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerPaper whereUserId($value)
 * @mixin \Eloquent
 */
class CustomerPaper extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效

    protected $table = 'crm_customer_paper';

}
