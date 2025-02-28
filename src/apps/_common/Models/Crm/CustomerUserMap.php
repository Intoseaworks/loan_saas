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
 * Common\Models\Crm\CustomerUserMap
 *
 * @property int $id
 * @property int|null $customer_id crm_customer.id
 * @property int|null $user_id user.id
 * @property int|null $similarity 相似度
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereSimilarity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerUserMap whereUserId($value)
 * @mixin \Eloquent
 */
class CustomerUserMap extends Model {

    use StaticModel;

    protected $table = 'crm_customer_user_map';

}
