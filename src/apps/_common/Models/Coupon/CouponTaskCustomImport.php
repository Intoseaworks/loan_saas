<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Coupon;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class CouponTaskCustomImport extends Model {

    use StaticModel;

    protected $fillable = ['id','coupon_id','coupon_title','status','telephone',
                            'create_user','merchant_id',
                            'created_at','update_user','updated_at','send_time'];

    protected $table = 'coupon_task_custom_import';

}
