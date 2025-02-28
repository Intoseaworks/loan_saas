<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Activity;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ActivityAward extends Model {

    use StaticModel;

    protected $fillable = ['id','merchant_id','title','status',
                            'created_at','updated_at','award','award_value','award_condition','award_use_limit','award_use_value','type','upload_id'];

    protected $table = 'activities_awords';

    public function getBatchAttribute()
    {
        return $this->attributes['batch'] ? explode(',',$this->attributes['batch']) : $this->attributes['batch'];
    }

    protected static function boot() {
        parent::boot();

        static::setAppIdOrMerchantIdBootScope();
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class,'activities_awords_relation','award_id','activity_id');
    }

}
