<?php

/**
 *
 *
 */
namespace Common\Models\Notice;

use Admin\Services\Crm\SmsTemplateServer;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

class SmsTask extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //失效

    const TYPE_SMS = "SMS";
    const TYPE_PHONE = "PHONE";

    protected $fillable = ['merchant_id','upload_id','task_name','task_type','customer_type','clm_lever','batch_id','check_blacklist','check_greylist','max_overdue_days','telephone_status',
                            'last_login','send_time','frequency','sms_template_id','phone_time_interval','phone_stop_term','sms_run_times','status','created_at','updated_at'];

    protected $table = 'sms_task';

    protected $appends = ['sms_template_name'];

    public function getTaskList($params)
    {
        $query = self::query()->select('*');

        // 发送时间
        $send_time = array_get($params, 'send_time');

        if ($send_time) {
            $query->where('send_time', $send_time);
        }

        if ($sms_template_id = array_get($params, 'sms_template_id')) {
            $query->where('sms_template_id', $sms_template_id);
        }
        // 推送关键词
        $keyword = array_get($params, 'keyword_sms_task');
        if (isset($keyword)) {
                $keyword = '%' . trim($keyword) . '%';
                $query->where('task_name', 'like', $keyword);
        }
        $size = array_get($params, 'size');
        return $query->withCount('smsTaskUsers')->orderBy('id', 'desc')->paginate($size);
    }

    public function getSmsTemplateNameAttribute()
    {
        if ($this->attributes['sms_template_id']) {
            $template = SmsTemplateServer::server()->getOne($this->attributes['sms_template_id']);
            if ($template){
                return $template['tpl_name'];
            }else{
                return null;
            }
        }else {
            return null;
        }
    }

    public function smsTaskUsers()
    {
        return $this->hasMany(SmsTaskUser::class, 'task_id');
    }

}
