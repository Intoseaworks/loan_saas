<?php

/**
 *
 *
 */
namespace Common\Models\Notice;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class SmsTaskUser extends Model {

    use StaticModel;

    const TYPE_RECEIVE = 1;
    const TYPE_SEND = 2;

    protected $fillable = ['id','task_id','user_id','sms_telephone','sms_centent','sms_date','type','created_at'];

    protected $table = 'sms_task_user';

    public function getTaskList($params)
    {
        $query = self::query()->select('*');
        $query = $query->with('smsTask');

        // 发送时间
        $time_start = array_get($params, 'time_start');
        $time_end = array_get($params, 'time_end');
        $task_name = array_get($params, 'task_name');
        if ($task_name) {
            $query->whereHas('smsTask', function ($query) use ($task_name) {
                $query->where('task_name', 'like', '%'.$task_name.'%');
            });
        }

        if ($time_start && $time_end) {
            $query->whereBetween('sms_task_user.sms_date', [$time_start, $time_end]);
        }

        if ($task_id = array_get($params, 'task_id')) {
            $query->where('sms_task_user.task_id', $task_id);
        }
        if ($type = array_get($params, 'type')) {
            $query->where('sms_task_user.type', $type);
        }
        // 用户名
        $keyword = array_get($params, 'sms_telephone');
        if (isset($keyword)) {
            $keyword = trim($keyword);
            $query->where(function ($query) use ($keyword) {
                $query->where('fullname', 'like', '%'.$keyword.'%');
                $query->orWhere('sms_telephone', 'like', '%'.$keyword.'%');
            });
        }
        $keyword = array_get($params, 'keyword_sms_task');
        if (isset($keyword)) {
            $keyword = trim($keyword);
            $query->where('sms_centent', 'like', '%'.$keyword.'%');
        }
        $size = array_get($params, 'size');
        return $query->orderBy('id', 'desc')->paginate($size);
    }

    public function smsTask()
    {
        return $this->belongsTo(SmsTask::class, 'task_id');
    }

}
