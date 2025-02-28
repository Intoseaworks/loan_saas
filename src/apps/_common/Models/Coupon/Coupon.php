<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Coupon;

use Carbon\Carbon;
use Common\Models\Crm\Customer;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {

    use StaticModel;

    protected $fillable = ['id','title','coupon_type','used_amount','usage','with_amount','overdue_use','end_time','status','create_user',
        'created_at','update_user','updated_at'];

    protected $table = 'coupon';
    protected $appends = ['effectivedays'];

//    public function getStatusAttribute()
//    {
////        return Carbon::now()->toDateTimeString() > $this->attributes['end_time'] ? 0 : $this->attributes['status'];
//    }

    public function getEffectivedaysAttribute()
    {
        $carbon = Carbon::parse ($this->attributes['end_time']); // 格式化一个时间日期字符串为 carbon 对象
        $created_at = Carbon::parse ($this->attributes['created_at']);
        $int = $created_at->diffInDays($carbon, false); // $int 为正负数
        return $int;
    }

    protected static function boot() {
        parent::boot();

        static::setAppIdOrMerchantIdBootScope();
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class,'coupon_receive','coupon_id','user_id','id','main_user_id');
    }

    public function couponReceives()
    {
        return $this->hasMany(CouponReceive::class,'coupon_id');
    }
}
