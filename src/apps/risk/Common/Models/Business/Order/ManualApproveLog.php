<?php

namespace Risk\Common\Models\Business\Order;

use Risk\Common\Models\Business\BusinessBaseModel;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserAuth;

/**
 * Risk\Common\Models\Business\Order\ManualApproveLog
 *
 * @property int $id
 * @property int $app_id
 * @property int|null $admin_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int|null $approve_type 审批类型判断1初审2电审
 * @property string|null $name
 * @property string|null $from_order_status
 * @property string|null $to_order_status
 * @property string|null $result
 * @property string|null $remark 备注
 * @property string|null $created_at
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereApproveType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereFromOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereToOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManualApproveLog whereUserId($value)
 * @mixin \Eloquent
 */
class ManualApproveLog extends BusinessBaseModel
{
    /** 初审 */
    const APPROVE_TYPE_MANUAL = 1;
    /** 电审 */
    const APPROVE_TYPE_CALL = 2;
    const SCENARIO_CREATE = 'create';
    const NAME_MANUAL_PASS = 'manual_pass';
    const NAME_MANUAL_REPLENISH = 'manual_replenish';
    const NAME_MANUAL_REJECT = 'manual_reject'; // 初审通过
    const NAME_CALL_PASS = 'call_pass'; // 待补充资料;
    const NAME_CALL_USER_GIVE_UP = 'call_user_give_up'; // 初审拒绝;
    const NAME_CALL_REJECT = 'call_reject'; // 电审通过
    const NAME_CALL_SECOND = 'call_second'; // 电审用户取消
    const NAME_CALL_MAX_CALL_CANCEL = 'call_max_call_cancel'; // 电审拒绝
    const NAME_CALL_NO_ANSWER = 'call_second'; // 需要二审
    const NAME_PASS = 'pass'; // 电审3次无人审结果
    const NAME_MANUAL_SYSTEM_REJECT = 'manual_system_reject'; // 无响应
    const NAME_CALL_SYSTEM_REJECT = 'call_system_reject'; // 审批通过
    /** @var array 审批拒绝状态 */
    const NAME_REJECT = [
        self::NAME_MANUAL_REJECT,
        self::NAME_CALL_REJECT,
    ]; // 初审审批通过机审打分拒绝
    const RESULT_PAN_CARD_NOT_CLEAR = 'PAN_CARD_NOT_CLEAR'; // 电审审批通过机审打分拒绝
    const RESULT_FRONT_VOTER_ID_NOT_CLEAR = 'FRONT_VOTER_ID_NOT_CLEAR';

    // pan card
    const RESULT_BACK_VOTER_ID_NOT_CLEAR = 'BACK_VOTER_ID_NOT_CLEAR';
    // voterId
    const RESULT_FRONT_DL_NOT_CLEAR = 'FRONT_DL_NOT_CLEAR';
    // voterId
    const RESULT_BACK_DL_NOT_CLEAR = 'BACK_DL_NOT_CLEAR';
    // driving licence
    const RESULT_DL_EXPIRED = 'DL_EXPIRED';
    // driving licence
    const RESULT_FRONT_AADHAAR_CARD_NOT_CLEAR = 'FRONT_AADHAAR_CARD_NOT_CLEAR';
    // driving licnce
    const RESULT_BACK_AADHAAR_CARD_NOT_CLEAR = 'BACK_AADHAAR_CARD_NOT_CLEAR';
    // aadhaar card
    const RESULT_FRONT_PASSPORT_NOT_CLEAR = 'FRONT_PASSPORT_NOT_CLEAR';
    // aadharr card
    const RESULT_BACK_PASSPORT_NOT_CLEAR = 'BACK_PASSPORT_NOT_CLEAR';
    // passport
    const RESULT_PASSPORT_EXPIRED = 'PASSPORT_EXPIRED';
    // passport
    const RESULT_LIVING_BLURRY = 'LIVING_BLURRY';
    // passport
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
    // 人脸识别
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
    public $timestamps = false;
    protected $table = 'data_manual_approve_log';
    protected $fillable = [
        'id',
        'app_id',
        'admin_id',
        'user_id',
        'order_id',
        'approve_type',
        'name',
        'from_order_status',
        'to_order_status',
        'result',
        'remark',
        'created_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
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

//    public function user($class = Staff::class)
//    {
//        return $this->belongsTo($class, 'admin_id', 'id');
//    }
}
