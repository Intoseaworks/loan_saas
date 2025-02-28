<?php

namespace Common\Models\Collection;

use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Staff\Staff;
use Common\Models\User\User;
use Common\Models\User\UserInfo;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Collection\Collection
 *
 * @property int $id
 * @property int $admin_id 催收人员id
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property string $status 催收状态
 * @property string $level 催收等级(M M0 M1 M2)
 * @property string|null $assign_time 分配时间
 * @property string|null $finish_time 完结时间
 * @property string|null $bad_time 坏账时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereAssignTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereBadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $collection_time 首催时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereCollectionTime($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Collection\Collection whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 */
class Collection extends Model
{
    use StaticModel;

    const STATUS_OVERDUE_RENEWAL = 'overdue_renewal';//逾期续期
    const STATUS_REPAY_REMIND = 'repay_remind';//催收提醒
    const STATUS_WAIT_COLLECTION = 'wait_collection';//待催收
    const STATUS_COLLECTIONING = 'collectioning';//催收中
    const STATUS_COLLECTIONING_UNREPAY = 'collectioning_unrepay';//催收中未还款
    const STATUS_COLLECTIONING_PARTREPAY = 'collectioning_partrepay';//催收中部分还款
//    const STATUS_COMMITTED_REPAYMENT = 'committed_repayment';//承诺还款
    const STATUS_COMMITTED_REPAYMENT = 'commitment_to_repayment';//承诺还款
    const STATUS_COLLECTION_SUCCESS = 'collection_success';//催收成功
    const STATUS_COLLECTION_BAD = 'collection_bad';//已坏账
    
    const CALL_TEST_STATUS_WAITING = 0;
    const CALL_TEST_STATUS_ANSWER = 1;
    const CALL_TEST_STATUS_NO_ANSWER = 2;
    const CALL_TEST_STATUS_CALLING = 3;
    
    const CALL_TEST_STATUS = [
        self::CALL_TEST_STATUS_WAITING => "Waiting for autocall",
        self::CALL_TEST_STATUS_ANSWER => "Answer",
        self::CALL_TEST_STATUS_NO_ANSWER => "No answer",
        self::CALL_TEST_STATUS_CALLING => "Calling in progress",
    ];
    
    const STATUS = [
        self::STATUS_OVERDUE_RENEWAL => '逾期续期',
        self::STATUS_REPAY_REMIND => '催收提醒',
        self::STATUS_WAIT_COLLECTION => '待催收',
//        self::STATUS_COLLECTIONING => '催收中',
        self::STATUS_COLLECTIONING_UNREPAY => '催收中未还款',
        self::STATUS_COLLECTIONING_PARTREPAY => '催收中部分还款',
        self::STATUS_COMMITTED_REPAYMENT => '承诺还款',
        self::STATUS_COLLECTION_SUCCESS => '催收成功',
        self::STATUS_COLLECTION_BAD => '已坏账',
    ];

    const MY_COLLECTION_STATUS = [
        self::STATUS_WAIT_COLLECTION => '待催收',
        self::STATUS_COLLECTIONING => '催收中',
        self::STATUS_COMMITTED_REPAYMENT => '承诺还款',
    ];

    # 已完结
    const STATUS_COMPLETE = [
        self::STATUS_COLLECTION_SUCCESS,
        self::STATUS_COLLECTION_BAD,
    ];

    # 催收进行中
    const STATUS_NOT_COMPLETE = [
        self::STATUS_WAIT_COLLECTION,
        self::STATUS_COLLECTIONING,
        self::STATUS_COMMITTED_REPAYMENT,
    ];

    # 催收单未完结 (注：包括坏账)
    const STATUS_COLLECTION_UNFINISHED = [
        self::STATUS_WAIT_COLLECTION,
        self::STATUS_COLLECTIONING,
        self::STATUS_COMMITTED_REPAYMENT,
        self::STATUS_COLLECTION_BAD,
    ];

    # 不需进入催收流程状态
    const STATUS_HIDDEN = [
        self::STATUS_REPAY_REMIND,
        self::STATUS_OVERDUE_RENEWAL,
    ];

    /**
     * 联系结果
     */
//    const DIAL_NORMAL_CONTACT = 'normal_contact';//正常联系
    const DIAL_NORMAL_CONTACT = 'decommited_ustomers';//违约客户
    const DIAL_UNABLE_CONTACT = 'unanswered';//无法联系
    const DIAL_STATUS_COMMITTED_REPAYMENT = 'commitment_to_repayment';//承诺还款
//    const DIAL_SELF_INADVERTENTLY_REPAY = 'self_inadvertently_repay';//拒绝还款
    const DIAL_SELF_INADVERTENTLY_REPAY = 'refusal_to_repay';//拒绝还款
    const DIAL_PAID_BACK = 'paid_back';//已还款
    const DIAL_THIRD_PARTY_REFERRALS = 'third_party_referrals';//第三方转告
//    const DIAL_INTENTIONAL_HELP = 'intentional_help';//他人代偿
    const DIAL_INTENTIONAL_HELP = 'willing_to_repay_on_behalf_of';//愿意代还
    const DIAL_REPAYMENT_MADE = 'repayment_made';//已代还款
    const DIAL_SMS = 'SMS';//短信通告
    const DIAL_WHATSAPP = 'Whatsapp';//WhatsApp通告
    const DIAL_MESSENGER = 'Messenger';//FB通告
    # 所有
    const DIAL_ALL = [
        self::DIAL_NORMAL_CONTACT => '违约客户',
//        self::DIAL_UNABLE_CONTACT => '无法联系',
        self::DIAL_UNABLE_CONTACT => '未接听',
        self::DIAL_SMS => '短信通告',
        self::DIAL_WHATSAPP => 'WhatsApp通告',
        self::DIAL_MESSENGER => 'FB通告',
        self::DIAL_STATUS_COMMITTED_REPAYMENT => '承诺还款',
        self::DIAL_SELF_INADVERTENTLY_REPAY => '拒绝还款',
        self::DIAL_PAID_BACK => '已还款',
        self::DIAL_THIRD_PARTY_REFERRALS => '第三方转告',
        self::DIAL_INTENTIONAL_HELP => '愿意代还',
        self::DIAL_REPAYMENT_MADE => '已代还款',
    ];
    # 本人
    const DIAL_SELF = [
        self::DIAL_NORMAL_CONTACT => '违约客户',
//        self::DIAL_UNABLE_CONTACT => '无法联系',
        self::DIAL_UNABLE_CONTACT => '未接听',
        self::DIAL_SMS => '短信通告',
        self::DIAL_WHATSAPP => 'WhatsApp通告',
        self::DIAL_MESSENGER => 'FB通告',
        self::DIAL_STATUS_COMMITTED_REPAYMENT => '承诺还款',
        self::DIAL_SELF_INADVERTENTLY_REPAY => '拒绝还款',
        self::DIAL_PAID_BACK => '已还款',
        self::DIAL_THIRD_PARTY_REFERRALS => '第三方转告',
        self::DIAL_INTENTIONAL_HELP => '愿意代还',
        self::DIAL_REPAYMENT_MADE => '已代还款',
    ];
    # 非本人
    const DIAL_NOSELF = [
        self::DIAL_NORMAL_CONTACT => '违约客户',
//        self::DIAL_UNABLE_CONTACT => '无法联系',
        self::DIAL_UNABLE_CONTACT => '未接听',
        self::DIAL_SMS => '短信通告',
        self::DIAL_WHATSAPP => 'WhatsApp通告',
        self::DIAL_MESSENGER => 'FB通告',
        self::DIAL_STATUS_COMMITTED_REPAYMENT => '承诺还款',
        self::DIAL_SELF_INADVERTENTLY_REPAY => '拒绝还款',
        self::DIAL_PAID_BACK => '已还款',
        self::DIAL_THIRD_PARTY_REFERRALS => '第三方转告',
        self::DIAL_INTENTIONAL_HELP => '愿意代还',
        self::DIAL_REPAYMENT_MADE => '已代还款',
    ];

//    const PROGRESS_COMMITTED_REPAYMENT = 'committed_repayment';
    const PROGRESS_TODAY_COMMITMENT = 'today\'s_commitment';
    const PROGRESS_FRAUDULENT_LOAN_CUSTOMERS = 'fraudulent_loan_customers';
    const PROGRESS_NOT_TODAY_COMMITMENT = 'not_today_commitment';
//    const PROGRESS_SELF_INADVERTENTLY_REPAY = 'self_inadvertently_repay';
    const PROGRESS_REDUCTION_REQUIRED = 'reduction_required';
    const PROGRESS_DISAGREEMENT_ON_FEES = 'disagreement_on_fees';
//    const PROGRESS_INTENTIONAL_NOTIFICATION = 'intentional_notification';
    const PROGRESS_INTENTIONAL_NOTIFICATION = 'agree_to_pass_on';
//    const PROGRESS_INTENTIONAL_HELP = 'intentional_help';
//    const PROGRESS_INTENTIONAL_HELP_TODAY = 'intentional_help_today';
    const PROGRESS_INTENTIONAL_HELP_TODAY = 'repayment_today';
//    const PROGRESS_INTENTIONAL_HELP_NOT_TODAY = 'intentional_help_not_today';
    const PROGRESS_INTENTIONAL_HELP_NOT_TODAY = 'not_today_repayment';
    const PROGRESS_REPAYMENT_MADE_REPAID = 'repayment_made';
    const PROGRESS_FOLLOW_PTP = 'follow_ptp';
    const PROGRESS_UNINTENTIONAL = 'unintentional';
//    const PROGRESS_TOO_MANY_PROMISES = 'too_many_promises';
    const PROGRESS_TOO_MANY_PROMISES = 'multiple_decommited';
    const PROGRESS_FIRST_DECOMMITED = 'first_decommited';
    const PROGRESS_SHUT_DOWN_NO = 'shut_down_no';
//    const PROGRESS_SHUTDOWN = 'shutdown';
    const PROGRESS_SHUTDOWN = 'turn_off';
    const PROGRESS_REFUSAL_TO_ANSWER = 'refusal_to_answer';
    const PROGRESS_BUSY_LINE = 'busy_line';
    const PROGRESS_INVALID_SOUND = 'invalid_sound';
    const PROGRESS_CALL_REMINDER = 'call_reminder';
    const PROGRESS_TEMPORARY_NOBODY_CONNECTION = 'temporary_nobody_connection';
    const PROGRESS_REPEATEDLY_UNANSWERED = 'no_answer';
//    const PROGRESS_REPEATEDLY_UNANSWERED = 'repeatedly_unanswered';
    const PROGRESS_REPEATEDLY_HANG_UP = 'repeatedly_hang_up';
    const PROGRESS_DEFERRED_PAYMENT = 'deferred_payment';
//    const PROGRESS_NO_INTENTION_TO_NOTIFICATION = 'no_intention_to_notification';
    const PROGRESS_NO_INTENTION_TO_NOTIFICATION = 'refusal_of_referrals';
    const PROGRESS_ALREADY_REPAID = 'paid_back';
//    const PROGRESS_NO_DOWN = 'no_down';
    const PROGRESS_NO_DOWN = 'shutdown';
    const PROGRESS_WRONG_NUMBER = 'wrong_number';
    const PROGRESS_WRONG_PERSON_ANSWERED = 'wrong_person_answered';
    const PROGRESS_LISTENER_DENY_RELATIONSHIP_OR_NAME = 'listener_deny_relationship_or_name';
    const PROGRESS_RENEWAL_REPAYMENT = 'renewal_repayment';

    # 本人正常联系进度
    const PROGRESS_SELF_NORMAL_CONTACT = [
//        self::PROGRESS_COMMITTED_REPAYMENT => '承诺还款',
        self::PROGRESS_RENEWAL_REPAYMENT => '承诺展期',
//        self::PROGRESS_SELF_INADVERTENTLY_REPAY => '本人无意还款',
        self::PROGRESS_DEFERRED_PAYMENT => '推迟还款',
        //self::PROGRESS_TOO_MANY_PROMISES => '过多承诺跳票',
    ];
    # 非本人正常联系进度
    const PROGRESS_NOSELF_NORMAL_CONTACT = [
        self::PROGRESS_INTENTIONAL_NOTIFICATION => '同意转告',
//        self::PROGRESS_INTENTIONAL_HELP => '有意帮还',
        self::PROGRESS_NO_INTENTION_TO_NOTIFICATION => '拒绝转告',
//        self::PROGRESS_UNINTENTIONAL => '无意向',
//        self::PROGRESS_TOO_MANY_PROMISES => '过多承诺跳票',
    ];
    # 承诺还款
    const PROGRESS_COMMITTED_REPAYMENT = [
        self::PROGRESS_TODAY_COMMITMENT => '今日承诺',
        self::PROGRESS_NOT_TODAY_COMMITMENT => '非今日承诺',
        self::PROGRESS_RENEWAL_REPAYMENT => '承诺展期',
    ];
    # 拒绝还款
    const PROGRESS_SELF_INADVERTENTLY_REPAY = [
        self::PROGRESS_FRAUDULENT_LOAN_CUSTOMERS => '骗贷客户',
        self::PROGRESS_REDUCTION_REQUIRED => '需减免',
        self::PROGRESS_DISAGREEMENT_ON_FEES => '对费用有异议',
    ];
    # 已经还款
    const PROGRESS_PAID_BACK = [
        self::PROGRESS_ALREADY_REPAID => '已还款',
    ];
    # 第三方转告
    const PROGRESS_THIRD_PARTY_REFERRALS = [
        self::PROGRESS_INTENTIONAL_NOTIFICATION => '同意转告',
        self::PROGRESS_NO_INTENTION_TO_NOTIFICATION => '拒绝转告',
    ];
    # 愿意代还
    const PROGRESS_INTENTIONAL_HELP = [
        self::PROGRESS_INTENTIONAL_HELP_TODAY => '今日代还',
        self::PROGRESS_INTENTIONAL_HELP_NOT_TODAY => '非今日代还',
    ];
    # 已代还款
    const PROGRESS_REPAYMENT_MADE = [
        self::PROGRESS_REPAYMENT_MADE_REPAID => '已代还款',
    ];
    # 正常联系进度
    const PROGRESS_ALL_NORMAL_CONTACT = [
//        self::PROGRESS_COMMITTED_REPAYMENT => '承诺还款',
//        self::PROGRESS_SELF_INADVERTENTLY_REPAY => '本人无意还款',
//        self::PROGRESS_INTENTIONAL_NOTIFICATION => '有意告知',
//        self::PROGRESS_INTENTIONAL_HELP => '有意帮还',
//        self::PROGRESS_NO_INTENTION_TO_NOTIFICATION => '他人无意转告',
//        self::PROGRESS_UNINTENTIONAL => '无意向',
        //self::PROGRESS_TOO_MANY_PROMISES => '过多承诺跳票',
        self::PROGRESS_FIRST_DECOMMITED => '首次违约',
        self::PROGRESS_TOO_MANY_PROMISES => '多次违约',
    ];
    # 无法联系进度
    const PROGRESS_UNABLE_CONTACT = [
//        self::PROGRESS_SHUT_DOWN_NO => '停机空号',
        self::PROGRESS_NO_DOWN => '停机',
        self::PROGRESS_SHUTDOWN => '关机',
        self::PROGRESS_REFUSAL_TO_ANSWER => '拒绝接听',
//        self::PROGRESS_BUSY_LINE => '忙线',
//        self::PROGRESS_WRONG_NUMBER => '无效音',
//        self::PROGRESS_CALL_REMINDER => '来电提醒',
//        self::PROGRESS_TEMPORARY_NOBODY_CONNECTION => '暂时无人接通',
        self::PROGRESS_REPEATEDLY_UNANSWERED => '无人接听',
//        self::PROGRESS_REPEATEDLY_HANG_UP => '反复接听挂断',
//        self::PROGRESS_LISTENER_DENY_RELATIONSHIP_OR_NAME => '否认关系',
//        self::PROGRESS_WRONG_PERSON_ANSWERED => '错误方接听',
    ];
    const PROGRESS_ALL = [
        self::DIAL_NORMAL_CONTACT => self::PROGRESS_ALL_NORMAL_CONTACT,
        self::DIAL_UNABLE_CONTACT => self::PROGRESS_UNABLE_CONTACT,
        self::DIAL_STATUS_COMMITTED_REPAYMENT => self::PROGRESS_COMMITTED_REPAYMENT,
        self::DIAL_SELF_INADVERTENTLY_REPAY => self::PROGRESS_SELF_INADVERTENTLY_REPAY,
        self::DIAL_PAID_BACK => self::PROGRESS_PAID_BACK,
        self::DIAL_THIRD_PARTY_REFERRALS => self::PROGRESS_THIRD_PARTY_REFERRALS,
        self::DIAL_INTENTIONAL_HELP => self::PROGRESS_INTENTIONAL_HELP,
        self::DIAL_REPAYMENT_MADE => self::PROGRESS_REPAYMENT_MADE,
    ];
    const PROGRESS_SELF = [
//        self::DIAL_NORMAL_CONTACT => self::PROGRESS_SELF_NORMAL_CONTACT,
//        self::DIAL_UNABLE_CONTACT => self::PROGRESS_UNABLE_CONTACT,
        self::DIAL_NORMAL_CONTACT => self::PROGRESS_ALL_NORMAL_CONTACT,
        self::DIAL_UNABLE_CONTACT => self::PROGRESS_UNABLE_CONTACT,
        self::DIAL_STATUS_COMMITTED_REPAYMENT => self::PROGRESS_COMMITTED_REPAYMENT,
        self::DIAL_SELF_INADVERTENTLY_REPAY => self::PROGRESS_SELF_INADVERTENTLY_REPAY,
        self::DIAL_PAID_BACK => self::PROGRESS_PAID_BACK,
//        self::DIAL_THIRD_PARTY_REFERRALS => self::PROGRESS_THIRD_PARTY_REFERRALS,
//        self::DIAL_INTENTIONAL_HELP => self::PROGRESS_INTENTIONAL_HELP,
//        self::DIAL_REPAYMENT_MADE => self::PROGRESS_REPAYMENT_MADE,
    ];
    const PROGRESS_NOSELF = [
//        self::DIAL_NORMAL_CONTACT => self::PROGRESS_NOSELF_NORMAL_CONTACT,
//        self::DIAL_UNABLE_CONTACT => self::PROGRESS_UNABLE_CONTACT,
//        self::DIAL_NORMAL_CONTACT => self::PROGRESS_ALL_NORMAL_CONTACT,
        self::DIAL_UNABLE_CONTACT => self::PROGRESS_UNABLE_CONTACT,
//        self::DIAL_STATUS_COMMITTED_REPAYMENT => self::PROGRESS_COMMITTED_REPAYMENT,
//        self::DIAL_SELF_INADVERTENTLY_REPAY => self::PROGRESS_SELF_INADVERTENTLY_REPAY,
//        self::DIAL_PAID_BACK => self::PROGRESS_PAID_BACK,
        self::DIAL_THIRD_PARTY_REFERRALS => self::PROGRESS_THIRD_PARTY_REFERRALS,
        self::DIAL_INTENTIONAL_HELP => self::PROGRESS_INTENTIONAL_HELP,
        self::DIAL_REPAYMENT_MADE => self::PROGRESS_REPAYMENT_MADE,
    ];

    /**
     * @var string
     */
    protected $table = 'collection';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function sortCustom()
    {
        return [
            //应还款日期
            'promise_paid_time' => [
                'related' => 'order.lastRepaymentPlan',
                'field' => 'appointment_paid_time',
            ],
            //逾期天数
            'overdue_days' => [
                'related' => 'order.lastRepaymentPlan',
                'field' => 'overdue_days',
            ],
            //减免金额
            'reduction_fee' => [
                'related' => 'order.lastRepaymentPlan',
                'field' => 'reduction_fee',
            ],
            //催记时间
            'record_time' => [
                'related' => 'collectionDetail',
                'field' => 'record_time',
            ],
            //承诺还款时间
            'collection_promise_paid_time' => [
                'related' => 'collectionDetail',
                'field' => 'promise_paid_time',
            ],
            //催收成功时间
            'finish_time' => [
                'field' => 'finish_time',
            ],
            //坏账时间
            'bad_time' => [
                'field' => 'bad_time',
            ],
        ];
    }

    public function textRules()
    {
        return [];
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionDetail($class = CollectionDetail::class)
    {
        return $this->hasOne($class, 'collection_id', 'id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order($class = Order::class)
    {
        return $this->hasOne($class, 'id', 'order_id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function repaymentPlans($class = RepaymentPlan::class)
    {
        return $this->hasMany($class, 'order_id', 'order_id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastRepaymentPlan($class = RepaymentPlan::class)
    {
        return $this->hasOne($class, 'order_id', 'order_id')->where('installment_num', 1);
    }

    /**
     * 当前催收单包含的还款计划
     *
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function overdueRepaymentPlans($class = RepaymentPlan::class)
    {
        $hasManyQuery = $this->hasMany($class, 'order_id', 'order_id');
        # 未完结案件
        if (!$this->finish_time) {
            $hasManyQuery->where(function ($query) {
                /** @var QueryBuilder $query */
                # 应还日期小于当前时间 且未还款
                $query->where('appointment_paid_time', '<', DateHelper::date())
                    ->whereNull('repay_time');
            });
        } # 已完结案件
        else {
            $hasManyQuery->where(function ($query) {
                /** @var QueryBuilder $query */
                # 应还日期小于完结时间，且实还日期大于入催时间
                $query->where('appointment_paid_time', '<', $this->finish_time)
                    ->orWhere('repay_time', '>', $this->created_at);
            });
        }
        return $hasManyQuery->orderBy('id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff($class = Staff::class)
    {
        return $this->hasOne($class, 'id', 'admin_id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionRecord($class = CollectionRecord::class)
    {
        return $this->hasOne($class, 'collection_id', 'id')->orderBy('id', 'desc');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collectionRecords($class = CollectionRecord::class)
    {
        return $this->hasMany($class, 'collection_id', 'id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collectionContacts($class = CollectionContact::class)
    {
        return $this->hasMany($class, 'collection_id', 'id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionDeduction($class = CollectionDeduction::class)
    {
        return $this->hasOne($class, 'collection_id', 'id')->orderBy('id', 'desc');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionAssign($class = CollectionAssign::class)
    {
        return $this->hasOne($class, 'collection_id', 'id')->orderBy('id', 'desc');
    }
    
    public function collectionAssignAdmin(){
        $assign = $this->collectionAssign;
        if($assign){
            if (isset($assign->parent_id) && $assign->parent_id) {
                $assignAdmin = Staff::model()->getOne($assign->parent_id);
                return $assignAdmin->username;
            }
        }
        return "AUTO";
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userInfo($class = UserInfo::class)
    {
        return $this->hasOne($class, 'user_id', 'user_id');
    }

    /**
     * @param string $class
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function collectionBlackList($class = CollectionBlackList::class)
    {
        return $this->hasOne($class, 'user_id', 'user_id');
    }

    public function toFinsh(Collection $collection)
    {
        $data = [
            'status' => Collection::STATUS_COLLECTION_SUCCESS,
            'finish_time' => DateHelper::dateTime(),
        ];
        return $collection->setScenario($data)->save();
    }

    public function getIsCollectionProcess($orderId)
    {
        $where = [
            ['order_id', $orderId],
        ];
        return self::query()
            ->where($where)
            ->whereNotIn('status', array_merge(self::STATUS_HIDDEN, [self::STATUS_COLLECTION_SUCCESS]))
            ->orderByDesc("id")
            ->first();
    }

    public function getProgressAll()
    {
        return array_merge(
            ts(['committed_repayment'=>'承诺还款','intentional_notification'=>'同意转告'], 'collection'),
            ts(Collection::PROGRESS_ALL_NORMAL_CONTACT, 'collection'),
            ts(Collection::PROGRESS_COMMITTED_REPAYMENT, 'collection'),
            ts(Collection::PROGRESS_UNABLE_CONTACT, 'collection'));
    }
}
