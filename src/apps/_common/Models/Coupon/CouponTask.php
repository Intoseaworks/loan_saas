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

class CouponTask extends Model {

    use StaticModel;

    protected $fillable = ['id','coupon_id','title','status','customer_group','clm_level','batch','is_blacklist','is_graylist','cust_status','max_overdue_day',
                            'last_login_limit','last_login_limit_scope','grant_type','grant_type_scope','get_way','notice_type','notice_type_scope','create_user',
                            'created_at','update_user','updated_at','issue_count','received_count','used_count','notice_begin_time'];

    protected $table = 'coupon_task';

    public function getBatchAttribute()
    {
        return $this->attributes['batch'] ? explode(',',$this->attributes['batch']) : $this->attributes['batch'];
    }

}
