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
use Common\Models\Crm\MarketingPhoneAssign;

/**
 * Common\Models\Crm\MarketingPhoneLog
 *
 * @property int $id
 * @property int|null $task_id 营销任务ID(crm_marketing_task.id)
 * @property int|null $customer_id 客户ID(crm_customer.id
 * @property int|null $assign_id 分配ID(crm_marketing_telephone.id)
 * @property int|null $operator_id 操作员(staff.id)
 * @property int|null $call_status 呼叫情况
 * 1.NO ANSWER
 * 2.NO SERVICE
 * 3.INVALID NUMBER
 * 4.WRONG NUMBER
 * 5.ANSWER
 * @property int|null $call_result 跟进结果
 * 1.QUIT MARKETING
 * 2.HANG UP
 * 3.THINK ABOUT IT
 * 4.WILL APPLY
 * 5.NO INTENTION TO APPLY
 * 6.OTHER PERSON USE PHONE AND HAVE NO INTENTION TO APPLY
 * 7.OTHER PERSON USE PHONE AND WILL APPLY
 * 8.CALL LATER
 * @property int|null $call_refusal 拒绝原因
 * 1.DON'T NEED
 * 2.DOESN'T SAY ANYTHING
 * 3.HAVE NO WORK
 * 4.HIGH INTEREST
 * 5.LOST ID
 * 6.NO BANK CARD
 * 7.SHORT PERIOD
 * 8.SLOW DISBURSEMENT
 * @property string|null $remark 备注
 * @property int|null $customer_status 当前用户状态
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereAssignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCallRefusal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCallResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCallStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereCustomerStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MarketingPhoneLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MarketingPhoneLog extends Model {

    const CALL_STATUS_NO_ANSWER = 1;
    const CALL_STATUS_NO_SERVICE = 2;
    const CALL_STATUS_INVALID_NUMBER = 3;
    const CALL_STATUS_WRONG_NUMBER = 4;
    const CALL_STATUS_ANSWER = 5;
    const CALL_STATUS = [
        self::CALL_STATUS_NO_ANSWER => 'NO ANSWER',
        self::CALL_STATUS_NO_SERVICE => 'NO SERVICE',
        self::CALL_STATUS_INVALID_NUMBER => 'INVALID NUMBER',
        self::CALL_STATUS_WRONG_NUMBER => 'WRONG NUMBER',
        self::CALL_STATUS_ANSWER => 'ANSWER',
    ];
    const CALL_RESULT_QUIT_MARKETING = 1;
    const CALL_RESULT_HANG_UP = 2;
    const CALL_RESULT_THINK_ABOUT_IT = 3;
    const CALL_RESULT_WILL_APPLY = 4;
    const CALL_RESULT_NO_INTENTION_TO_APPLY = 5;
    const CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_HAVE_NO_INTENTION_TO_APPLY = 6;
    const CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_WILL_APPLY = 7;
    const CALL_RESULT_CALL_LATER = 8;
    const CALL_RESULT_NO_ANSWER = 9;
    const CALL_RESULT_NO_SERVICE = 10;
    const CALL_RESULT_INVALID_NUMBER = 11;
    const CALL_RESULT_WRONG_NUMBER = 12;
    const CALL_RESULT = [
        self::CALL_RESULT_QUIT_MARKETING => 'QUIT MARKETING',
        self::CALL_RESULT_HANG_UP => 'HANG UP',
        self::CALL_RESULT_THINK_ABOUT_IT => 'THINK ABOUT IT',
        self::CALL_RESULT_WILL_APPLY => 'WILL APPLY',
        self::CALL_RESULT_NO_INTENTION_TO_APPLY => 'NO INTENTION TO APPLY',
        self::CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_HAVE_NO_INTENTION_TO_APPLY => 'OTHER PERSON USE PHONE AND HAVE NO INTENTION TO APPLY',
        self::CALL_RESULT_OTHER_PERSON_USE_PHONE_AND_WILL_APPLY => 'OTHER PERSON USE PHONE AND WILL APPLY',
//        self::CALL_RESULT_CALL_LATER => 'CALL LATER',
        self::CALL_RESULT_NO_ANSWER => 'NO ANSWER',
        self::CALL_RESULT_NO_SERVICE => 'NO SERVICE',
        self::CALL_RESULT_INVALID_NUMBER => 'INVALID NUMBER',
        self::CALL_RESULT_WRONG_NUMBER => 'WRONG NUMBER',
    ];
    const CALL_REFUSAL_DONT_NEED = 1;
    const CALL_REFUSAL_DOESNT_SAY_ANYTHING = 2;
    const CALL_REFUSAL_HAVE_NO_WORK = 3;
    const CALL_REFUSAL_HIGH_INTEREST = 4;
    const CALL_REFUSAL_LOST_ID = 5;
    const CALL_REFUSAL_NO_BANK_CARD = 6;
    const CALL_REFUSAL_SHORT_PERIOD = 7;
    const CALL_REFUSAL_SLOW_DISBURSEMENT = 8;
    const CALL_REFUSAL = [
        self::CALL_REFUSAL_DONT_NEED => "DON'T NEED",
        self::CALL_REFUSAL_DOESNT_SAY_ANYTHING => "DOESN'T SAY ANYTHING",
        self::CALL_REFUSAL_HAVE_NO_WORK => "HAVE NO WORK",
        self::CALL_REFUSAL_HIGH_INTEREST => "HIGH INTEREST",
        self::CALL_REFUSAL_LOST_ID => "LOST ID",
        self::CALL_REFUSAL_NO_BANK_CARD => "NO BANK CARD",
        self::CALL_REFUSAL_SHORT_PERIOD => "SHORT PERIOD",
        self::CALL_REFUSAL_SLOW_DISBURSEMENT => "SLOW DISBURSEMENT",
    ];

    use StaticModel;

    protected $table = 'crm_marketing_phone_log';

    public function customer($class = Customer::class) {
        return $this->hasOne($class, 'id', 'customer_id');
    }

    public function assign($class = MarketingPhoneAssign::class) {
        return $this->hasOne($class, 'id', 'assign_id');
    }

    public function admin($class = \Common\Models\Staff\Staff::class) {
        return $this->hasOne($class, 'id', 'operator_id');
    }

}
