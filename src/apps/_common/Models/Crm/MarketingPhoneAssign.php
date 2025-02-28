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
use Common\Models\Crm\Customer;
use Common\Models\Crm\CrmMarketingTask;
use Common\Models\Staff\Staff;
use Common\Models\Crm\CustomerStatus;

/**
 * Common\Models\Crm\MarketingPhoneAssign
 *
 * @property int $id
 * @property int|null $customer_id 客户IDcrm_customer.id
 * @property int|null $task_id 任务ID crm_marketing_task.id
 * @property int|null $saler_id 电话销售ID,staff.id (0未分配
 * @property string|null $assign_time 任务分配时间
 * @property string|null $suggest_time 建议致电时间
 * @property string|null $last_call_time 最后通话时间
 * @property int|null $admin_id 分配人ID(staff.id)
 * @property int|null $status 状态(1=待电销;2=完成电销)
 * @property string|null $stop_reason 停止原因
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereAssignTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereLastCallTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereSalerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereStopReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereSuggestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneAssign whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MarketingPhoneAssign extends Model {

    use StaticModel;

    const STATUS_NORMAL = 1; //正常
    const STATUS_FORGET = 0; //停止
    const STATUS_FINISH = 2; //完成

    const STATUS = [
        self::STATUS_NORMAL => "电销中",
        self::STATUS_FORGET => "已停止",
        self::STATUS_FINISH => "完成"
    ];
    
    protected $table = 'crm_marketing_phone_assign';

    public function customer($class = Customer::class) {
        return $this->hasOne($class, 'id', 'customer_id');
    }

    public function task($class = CrmMarketingTask::class) {
        return $this->hasOne($class, "id", "task_id");
    }
    
    public function customerStatus(){
        return CustomerStatus::model()->getStatus($this->customer_id);
    }

    public function log($class = MarketingPhoneLog::class) {
        $log = MarketingPhoneLog::model()->where('assign_id', $this->id)->orderBy("id", "desc")->first();
        $query = $this->hasOne($class, 'assign_id', 'id');
        if($log){
            $query->where('id', $log->id);
        }
        return $query->orderBy('id', 'desc');
    }
    
    public function saler($class = Staff::class){
        return $this->hasOne($class, "id", "saler_id");
    }
}
