<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Activity;

use Carbon\Carbon;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model {

    use StaticModel;

    protected $fillable = ['id','target_group','clm_level','gender','order_count',
        'type','created_at','updated_at','merchant_id','title','status','start_time','end_time'];

    protected $table = 'activities';
    protected $appends = ['effectivedays'];

    const INVITE_ACTIVIE = 1;
    const LOTTERY_ACTIVIE = 2;

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

    public function awards()
    {
        return $this->belongsToMany(ActivityAward::class,'activities_awords_relation','activity_id','award_id');
    }

    public function activitiesRecords()
    {
        return $this->hasMany(ActivitiesRecord::class,'activity_id');
    }
}
