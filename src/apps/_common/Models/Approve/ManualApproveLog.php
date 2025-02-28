<?php


namespace Common\Models\Approve;


use Api\Models\User\UserAuth;
use Common\Models\Order\Order;
use Common\Models\Staff\Staff;
use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\ManualApproveLog
 *
 * @property int $id
 * @property int $admin_id 管理员id (默认0 标识为系统操作)
 * @property int $user_id 用户id
 * @property int|null $order_id 订单id
 * @property int|null $approve_type 审批类型判断1初审2电审
 * @property string $name 日志操作名
 * @property int|null $from_order_status 订单改变前状态
 * @property int|null $to_order_status 订单改变后状态
 * @property string|null $result 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \Common\Models\Approve\Staff $user
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereApproveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereFromOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereToOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereUserId($value)
 * @mixin \Eloquent
 * @property int $merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\ManualApproveLog whereMerchantId($value)
 * @property string|null $remark 备注
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereRemark($value)
 */
class ManualApproveLog extends Model
{
    use StaticModel;

    protected $table = 'manual_approve_log';
    protected $guarded = ['id'];

    /** 初审 */
    const APPROVE_TYPE_MANUAL = ApprovePool::ORDER_FIRST_GROUP;
    /** 电审 */
    const APPROVE_TYPE_CALL = ApprovePool::ORDER_CALL_GROUP;

    /**
     * @var bool
     */
    public $timestamps = false;

    //const CREATED_AT = null;
    //const UPDATED_AT = null;
    const SCENARIO_CREATE = 'create';

    const NAME_MANUAL_PASS = 'manual_pass'; // 初审通过
    const NAME_MANUAL_REPLENISH = 'manual_replenish'; // 待补充资料;
    const NAME_MANUAL_REJECT = 'manual_reject'; // 初审拒绝;
    const NAME_CALL_PASS = 'call_pass'; // 电审通过
    const NAME_CALL_USER_GIVE_UP = 'call_user_give_up'; // 电审用户取消
    const NAME_CALL_REJECT = 'call_reject'; // 电审拒绝
    const NAME_CALL_SECOND = 'call_second'; // 需要二审
    const NAME_CALL_MAX_CALL_CANCEL = 'call_max_call_cancel'; // 电审3次无人审结果
    const NAME_CALL_NO_ANSWER = 'call_second'; // 无响应
    const NAME_PASS = 'pass'; // 审批通过
    const NAME_MANUAL_SYSTEM_REJECT = 'manual_system_reject'; // 初审审批通过机审打分拒绝
    const NAME_CALL_SYSTEM_REJECT = 'call_system_reject'; // 电审审批通过机审打分拒绝

    /** @var array 审批拒绝状态 */
    const NAME_REJECT = [
        self::NAME_MANUAL_REJECT,
        self::NAME_CALL_REJECT,
    ];

    // pan card
    const RESULT_PAN_CARD_NOT_CLEAR = 'PAN_CARD_NOT_CLEAR';
    // voterId
    const RESULT_FRONT_VOTER_ID_NOT_CLEAR = 'FRONT_VOTER_ID_NOT_CLEAR';
    // voterId
    const RESULT_BACK_VOTER_ID_NOT_CLEAR = 'BACK_VOTER_ID_NOT_CLEAR';
    // driving licence
    const RESULT_FRONT_DL_NOT_CLEAR = 'FRONT_DL_NOT_CLEAR';
    // driving licence
    const RESULT_BACK_DL_NOT_CLEAR = 'BACK_DL_NOT_CLEAR';
    // driving licnce
    const RESULT_DL_EXPIRED = 'DL_EXPIRED';
    // aadhaar card
    const RESULT_FRONT_AADHAAR_CARD_NOT_CLEAR = 'FRONT_AADHAAR_CARD_NOT_CLEAR';
    // aadharr card
    const RESULT_BACK_AADHAAR_CARD_NOT_CLEAR = 'BACK_AADHAAR_CARD_NOT_CLEAR';
    // passport
    const RESULT_FRONT_PASSPORT_NOT_CLEAR = 'FRONT_PASSPORT_NOT_CLEAR';
    // passport
    const RESULT_BACK_PASSPORT_NOT_CLEAR = 'BACK_PASSPORT_NOT_CLEAR';
    // passport
    const RESULT_PASSPORT_EXPIRED = 'PASSPORT_EXPIRED';
    // 人脸识别
    const RESULT_LIVING_BLURRY = 'LIVING_BLURRY';

    /**
     * 初审回退项 具体查看 Params::getFirstApprove('base_detail_failed_list')
     */
    const REMARK_KEY_MAP = [
        1 => self::RESULT_PAN_CARD_NOT_CLEAR,
        2 => self::RESULT_FRONT_VOTER_ID_NOT_CLEAR,
        3 => self::RESULT_BACK_VOTER_ID_NOT_CLEAR,
        4 => self::RESULT_FRONT_DL_NOT_CLEAR,
        5 => self::RESULT_BACK_DL_NOT_CLEAR,
        6 => self::RESULT_FRONT_AADHAAR_CARD_NOT_CLEAR,
        7 => self::RESULT_BACK_AADHAAR_CARD_NOT_CLEAR,
        8 => self::RESULT_FRONT_PASSPORT_NOT_CLEAR,
        9 => self::RESULT_BACK_PASSPORT_NOT_CLEAR,
        10 => self::RESULT_PASSPORT_EXPIRED,
        11 => self::RESULT_LIVING_BLURRY,
        12 => self::RESULT_DL_EXPIRED,
    ];

    const SUPPLEMENT_AUTHS = [
        self::RESULT_PAN_CARD_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_FRONT_VOTER_ID_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_BACK_VOTER_ID_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_FRONT_DL_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_BACK_DL_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_DL_EXPIRED => UserAuth::AUTH_IDENTITY,
        self::RESULT_FRONT_AADHAAR_CARD_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_BACK_AADHAAR_CARD_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_FRONT_PASSPORT_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_BACK_PASSPORT_NOT_CLEAR => UserAuth::AUTH_IDENTITY,
        self::RESULT_PASSPORT_EXPIRED => UserAuth::AUTH_IDENTITY,
        self::RESULT_LIVING_BLURRY => UserAuth::AUTH_IDENTITY,
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            static::SCENARIO_CREATE => [
                'admin_id',
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'order_id',
                'approve_type',
                'name',
                'from_order_status',
                'to_order_status',
                'result',
                'remark',
                'approve_principal',
                'apply_principal',
                'created_at' => DateHelper::dateTime(),
            ],
        ];
    }

    /**
     * @return string|null
     */
    public function resultToText()
    {
        if ($this->result == '') {
            return '';
        }
        if (!$resultData = json_decode($this->result, true)) {
            return $this->result;
        }

        if (is_array($resultData)) {
            return implode(',', array_flatten($resultData));
        }

        return $resultData;
    }

    /**
     * 待补充项
     *
     * @return array
     */
    public function waitAuths(Order $order)
    {
        $approveManualLog = ManualApproveLog::model()
            ->where('order_id', $order->id)
            ->where('to_order_status', Order::STATUS_REPLENISH)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$approveManualLog || $approveManualLog['result'] == '') {
            return [];
        }
        if (!$remarkData = json_decode($approveManualLog['result'], true)) {
            return [];
        }
        return array_intersect(array_keys($remarkData), ManualApproveLog::REMARK_KEY_MAP);
    }

    /**
     * 待补充资料原因
     *
     * @param User $user
     * @return array|mixed
     */
    public function supplementTips(User $user)
    {
        if (!$user->order || $user->order->status != Order::STATUS_REPLENISH) {
            return [];
        }
        $approveManualLog = ManualApproveLog::model()
            ->where('order_id', $user->order->id)
            ->where('to_order_status', Order::STATUS_REPLENISH)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$approveManualLog || $approveManualLog['result'] == '') {
            return [];
        }
        if (!$remarkData = json_decode($approveManualLog['result'], true)) {
            return [];
        }
        return $remarkData;
    }

    public function user($class = Staff::class)
    {
        return $this->belongsTo($class, 'admin_id', 'id');
    }

}
