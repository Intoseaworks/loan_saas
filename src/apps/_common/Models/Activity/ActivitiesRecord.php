<?php

/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/29
 * Time: 16:08
 */

namespace Common\Models\Activity;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class ActivitiesRecord extends Model {

    use StaticModel;

    protected $fillable = ['id','activity_id','user_id','record_id','status','created_at','updated_at','aword_id','award_condition'];

    protected $table = 'activities_records';

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class,'activity_id');
    }
}
