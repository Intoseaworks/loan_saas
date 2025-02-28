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
use Common\Models\Crm\CrmMarketingTask;

/**
 * Common\Models\Crm\MarketingPhoneJob
 *
 * @property int $id
 * @property int|null $task_id 任务Id(crm_marketing_task.id)
 * @property int|null $customer_type 客户状态
 * @property int|null $total 待分配总量
 * @property int|null $per_capita_allocation 人均分配量
 * @property string|null $saler_ids 分配账号例1,2,3,4
 * @property int|null $admin_id 操作员(staff.id)
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereCustomerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob wherePerCapitaAllocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereSalerIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneJob whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MarketingPhoneJob extends Model {

    use StaticModel;
    const STATUS_WAIT = 0;
    const STATUS_RUNNING = 1;
    const StATUS_OVER = 2;

    protected $table = 'crm_marketing_phone_job';

    public function task($class = CrmMarketingTask::class) {
        return $this->hasOne($class, 'id', 'task_id');
    }

}
