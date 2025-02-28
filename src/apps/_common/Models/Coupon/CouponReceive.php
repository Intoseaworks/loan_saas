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

class CouponReceive extends Model {

    use StaticModel;

    protected $fillable = ['id','coupon_id','user_id','order_id','coupon_task_id','use_time','created_at','updated_at','coupon_task_custom_id'];

    protected $table = 'coupon_receive';

    public function coupon()
    {
        return $this->belongsTo(Coupon::class,'coupon_id');
    }

}
