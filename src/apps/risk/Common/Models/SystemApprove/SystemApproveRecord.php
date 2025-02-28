<?php

namespace Risk\Common\Models\SystemApprove;

use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\SystemApprove\SystemApproveRecord
 *
 * @property int $id
 * @property int $app_id 商户id
 * @property int $task_id task id
 * @property string|null $module 所属模块 basic:基础 credit:征信
 * @property int $user_id 用户id
 * @property int $order_id 订单id
 * @property int $user_type 用户类型 0:新用户  1:老用户
 * @property int $result 结果 1:通过  2:拒绝
 * @property int $hit_rule_cnt hit_rule命中数
 * @property string|null $hit_rule 命中拒绝规则及对应命中值，json串
 * @property string|null $exe_rule 规则执行记录
 * @property string|null $extra_code 额外标识
 * @property string $description 注释说明
 * @property int|null $relate_id 关联ID
 * @property int|null $experian_relate_id experian关联ID
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereExeRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereExperianRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereExtraCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereHitRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereHitRuleCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRecord whereUserType($value)
 * @mixin \Eloquent
 */
class SystemApproveRecord extends RiskBaseModel
{
    const UPDATED_AT = null;
    /** 用户类型：新用户 */
    const USER_TYPE_NEW = 0;
    /** 用户类型：老用户 */
    const USER_TYPE_OLD = 1;
    /** @var array 用户类型 */
    const USER_QUALITY = [
        self::USER_TYPE_NEW => '新用户',
        self::USER_TYPE_OLD => '老用户',
    ];
    /** 用户类型关联订单表用户类型 */
    const USER_QUALITY_RELATE = [
        Order::QUALITY_NEW => self::USER_TYPE_NEW,
        Order::QUALITY_OLD => self::USER_TYPE_OLD,
    ];
    const RESULT_PASS = 1;
    const RESULT_REJECT = 2;
    const RESULT = [
        self::RESULT_PASS => '通过',
        self::RESULT_REJECT => '拒绝',
    ];
    /** 额外标识：未匹配命中 */
    const EXTRA_CODE_NOT_HIT = 'NOT_HIT';
    /** 额外标识：阶段2异常 */
    const EXTRA_CODE_STAGE2_EXCEPTION = 'STAGE2_EXCEPTION';
    /** 额外标识：错误 */
    const EXTRA_CODE_EXCEPTION = 'EXCEPTION';
    /** 默认 code */
    const RULE_00000 = '00000';
    /******************************************** 申请规则 *******************************************************/
    const RULE_APPLY_10001 = '10001';
    const RULE_APPLY_10002 = '10002';
    const RULE_APPLY_10003 = '10003';
    const RULE_APPLY_10004 = '10004';
    const RULE_APPLY_10005 = '10005';
    const RULE_APPLY_10006 = '10006';
    const RULE_APPLY_10007 = '10007';
    const RULE_APPLY_10008 = '10008';
    const RULE_APPLY_10009 = '10009';
    const RULE_APPLY_10010 = '10010';
    const RULE_APPLY_10011 = '10011';
    const RULE_APPLY_10012 = '10012';
    const RULE_APPLY_10013 = '10013';
    const RULE_APPLY_10014 = '10014';
    const RULE_APPLY_10015 = '10015';
    const RULE_APPLY_10016 = '10016';
    const RULE_APPLY_10017 = '10017';
    const RULE_APPLY_10018 = '10018';
    const RULE_APPLY_10019 = '10019';
    const RULE_APPLY_10020 = '10020';
    const RULE_APPLY_10021 = '10021';
    const RULE_APPLY_10022 = '10022';
    const RULE_APPLY_10023 = '10023';
    const RULE_APPLY_10024 = '10024';
    const RULE_APPLY_10025 = '10025';
    /*nio.wang 20200806*/
    const RULE_APPLY_10026 = '10026';//黑名单
    const RULE_APPLY_10027 = '10027';//用户没有what'sapp
    const RULE_APPLY_10028 = '10028';//riskcloud 黑名单命中
    const RULE_APPLY_10029 = '10029';//riskcloud 验证 人脸比对+A卡+pan卡+银行卡
    const RULE_APPLY_10030 = '10030';//是否有相关未完成订单
    /*2020027新规则*/
    const RULE_APPLY_10031 = '10031';
    const RULE_APPLY_10032 = '10032';
    const RULE_APPLY_10033 = '10033';
    const RULE_APPLY_10034 = '10034';
    /*20200930airudder*/
    const RULE_APPLY_10035 = '10035';
    /******************************************** 设备验证 *******************************************************/
    const RULE_DEVICE_20001 = '20001';
    const RULE_DEVICE_20002 = '20002';
    const RULE_DEVICE_20003 = '20003';
    const RULE_DEVICE_20004 = '20004';
    const RULE_DEVICE_20005 = '20005';
    const RULE_DEVICE_20006 = '20006';
    const RULE_DEVICE_20007 = '20007';
    const RULE_DEVICE_20008 = '20008';
    const RULE_DEVICE_20009 = '20009';
    const RULE_DEVICE_20010 = '20010';
    const RULE_DEVICE_20011 = '20011';
    /******************************************** 身份验证 *******************************************************/
    const RULE_AUTH_30001 = '30001';
    const RULE_AUTH_30002 = '30002';
    const RULE_AUTH_30003 = '30003';
    const RULE_AUTH_30004 = '30004';
    const RULE_AUTH_30005 = '30005';
    const RULE_AUTH_30006 = '30006';
    const RULE_AUTH_30007 = '30007';
    const RULE_AUTH_30008 = '30008';
    const RULE_AUTH_30009 = '30009';
    const RULE_AUTH_30010 = '30010';
    const RULE_AUTH_30011 = '30011';
    const RULE_AUTH_30012 = '30012';
    const RULE_AUTH_30013 = '30013';
    const RULE_AUTH_30014 = '30014';
    const RULE_AUTH_30015 = '30015';
    const RULE_AUTH_30016 = '30016';
    /******************************************** 通讯录验证 *******************************************************/
    const RULE_CONTACTS_40001 = '40001';
    const RULE_CONTACTS_40002 = '40002';
    const RULE_CONTACTS_40003 = '40003';
    const RULE_CONTACTS_40004 = '40004';
    const RULE_CONTACTS_40005 = '40005';
    const RULE_CONTACTS_40006 = '40006';
    const RULE_CONTACTS_40007 = '40007';
    const RULE_CONTACTS_40008 = '40008';
    /******************************************** 借款行为 *******************************************************/
    const RULE_BEHAVIOR_50001 = '50001';
    const RULE_BEHAVIOR_50002 = '50002';
    const RULE_BEHAVIOR_50003 = '50003';
    const RULE_BEHAVIOR_50004 = '50004';
    const RULE_BEHAVIOR_50005 = '50005';
    const RULE_BEHAVIOR_50006 = '50006';
    /******************************************** 其他规则 *******************************************************/
    const RULE_OTHER_60001 = '60001';
    const RULE_OTHER_60002 = '60002';
    const RULE_OTHER_60003 = '60003';
    const RULE_OTHER_60004 = '60004';
    const RULE_OTHER_60005 = '60005';
    const RULE_OTHER_60006 = '60006';
    const RULE_OTHER_60007 = '60007';
    /*************************************************************************************************************
     * Rule end
     ************************************************************************************************************/

    const RESULT_CODE = [
        // 申请规则
        SystemApproveRule::RULE_APPLY_AGE_UNQUALIFIED => self::RULE_APPLY_10001, // age criteria failed  年龄不符合
        SystemApproveRule::RULE_APPLY_HAS_REJECTED_ORDERS => self::RULE_APPLY_10002, // customer has been rejected lately 近期有被拒订单
        SystemApproveRule::RULE_APPLY_EDUCATION_UNQUALIFIED => self::RULE_APPLY_10004, // education criteria failed  学历不符合
        SystemApproveRule::RULE_APPLY_CAREER_TYPE_UNQUALIFIED => self::RULE_APPLY_10005,  // occupation criteria failed  职业不符合
        SystemApproveRule::RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME => self::RULE_APPLY_10006, // apply process criteria failed 申请行为异常
        SystemApproveRule::RULE_APPLY_CREATE_TIME => self::RULE_APPLY_10007, // apply process criteria failed 申请行为异常
        SystemApproveRule::RULE_APPLY_ADDRESS_UNQUALIFIED => self::RULE_APPLY_10008, // region criteria failed  地址不符合
        SystemApproveRule::RULE_APPLY_START_VERIFY_TIME => self::RULE_APPLY_10012, // apply process criteria failed 申请行为异常
        SystemApproveRule::RULE_APPLY_IP_NOT_IN_INDIA => self::RULE_APPLY_10013, // region criteria failed 地址不符合
        SystemApproveRule::RULE_APPLY_GPS_NOT_IN_INDIA => self::RULE_APPLY_10024, // region criteria failed 地址不符合
        SystemApproveRule::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE => self::RULE_APPLY_10018, // customer's personal infomation verification failed 个人信息验证失败
        SystemApproveRule::RULE_APPLY_CHANNEL_ID_IN_LIST => self::RULE_APPLY_10019, // channel criteria failed  渠道拒绝
        SystemApproveRule::RULE_APPLY_BANK_IN_REJECTED_LIST => self::RULE_APPLY_10023, // customer's personal infomation verification failed 个人信息验证失败
        SystemApproveRule::RULE_APPLY_DISTINCT_DEVICE_COUNT => self::RULE_APPLY_10025, // customer's personal infomation verification failed 个人信息验证失败
        SystemApproveRule::RULE_KUDOS_COVER_PAN => self::RULE_AUTH_30015,
        SystemApproveRule::RULE_KREDITONE_SCORE => self::RULE_AUTH_30016,
        SystemApproveRule::RULE_CHECK_OPEN_ORDER => self::RULE_APPLY_10030,
        //20200806 黑名单规则
        SystemApproveRule::RULE_APPLY_IN_BLACKLIST => self::RULE_APPLY_10026, // user in blacklist 用户在黑名单内
        //20200927 kudos&积分相关规则
        SystemApproveRule::RULE_KUDOS_USER_SCORE_LOW => self::RULE_APPLY_10031,
        SystemApproveRule::RULE_KUDOS_USER_SCORE_KREDITONE_LOW => self::RULE_APPLY_10032,
        SystemApproveRule::RULE_KUDOS_NULL => self::RULE_APPLY_10033,
        SystemApproveRule::RULE_NO_WHATSAPP_USER_SCORE_LOW => self::RULE_APPLY_10034,
        // 设备验证
        SystemApproveRule::RULE_DEVICE_SAME_APPLY_COUNT => self::RULE_DEVICE_20001, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT => self::RULE_DEVICE_20002, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_SAME_IN_APPROVE_COUNT => self::RULE_DEVICE_20003, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_SAME_OVERDUE_COUNT => self::RULE_DEVICE_20004, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_SAME_MATCHING_IDENTITY => self::RULE_DEVICE_20005, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_SAME_MATCHING_TELEPHONE => self::RULE_DEVICE_20006, // customer's device in associate with other account 设备存在关联用户
        SystemApproveRule::RULE_DEVICE_TYPE => self::RULE_DEVICE_20008, // device criteria failed 设备不符合
        SystemApproveRule::RULE_DEVICE_PHONE_MEMORY => self::RULE_DEVICE_20009, // device criteria failed 设备不符合
        SystemApproveRule::RULE_DEVICE_PHONE_FREE_MEMORY => self::RULE_DEVICE_20010, // device criteria failed 设备不符合
        SystemApproveRule::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => self::RULE_DEVICE_20011, // device criteria failed 设备不符合
        // 身份验证
        SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => self::RULE_AUTH_30001, // customer's bank card in associate with other account 银行卡未通过
        SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => self::RULE_AUTH_30002, // customer's bank card in associate with other account 银行卡未通过
        SystemApproveRule::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => self::RULE_AUTH_30003, // customer's bank card in associate with other account 银行卡未通过
        SystemApproveRule::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => self::RULE_AUTH_30004, // customer's identity in associate with other account 关联账户不符合
        SystemApproveRule::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => self::RULE_AUTH_30005, // customer's identity in associate with other account 关联账户不符合
        SystemApproveRule::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => self::RULE_AUTH_30006, // customer's identity in associate with other account 关联账户不符合
        SystemApproveRule::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => self::RULE_AUTH_30007, // customer's identity in associate with other account 关联账户不符合
        SystemApproveRule::RULE_AUTH_FACE_MATCH => self::RULE_AUTH_30008, // face recognition failed 人脸不合格
        SystemApproveRule::RULE_AUTH_OCR_NAME_COMPARISON => self::RULE_AUTH_30010, // customer's identity verification failed 身份认证未通过
        // 通讯录验证
        SystemApproveRule::RULE_CONTACTS_IN_OVERDUE_COUNT => self::RULE_CONTACTS_40002, // contacts criteria failed 通讯录不合格
        SystemApproveRule::RULE_CONTACTS_COUNT => self::RULE_CONTACTS_40003, // contacts criteria failed 通讯录不合格
        SystemApproveRule::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => self::RULE_CONTACTS_40004, // contacts criteria failed 通讯录不合格
        SystemApproveRule::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => self::RULE_CONTACTS_40005, // contacts criteria failed 通讯录不合格
        SystemApproveRule::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT => self::RULE_CONTACTS_40007, // contacts criteria failed 通讯录不合格
        // 借款行为
        SystemApproveRule::RULE_BEHAVIOR_REJECT_COUNT => self::RULE_BEHAVIOR_50001, // customer has been rejected too many times 历史被拒次数过多
        SystemApproveRule::RULE_BEHAVIOR_OVERDUE_COUNT => self::RULE_BEHAVIOR_50002, // previous loan has bad behavior 历史表现不良
        SystemApproveRule::RULE_BEHAVIOR_MAX_OVERDUE_DAYS => self::RULE_BEHAVIOR_50003, // previous loan has bad behavior 历史表现不良
        SystemApproveRule::RULE_BEHAVIOR_LATELY_OVERDUE_DAYS => self::RULE_BEHAVIOR_50004, // previous loan has bad behavior 历史表现不良
        SystemApproveRule::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT => self::RULE_BEHAVIOR_50005, // previous loan has bad behavior 历史表现不良
        // 其他规则
        SystemApproveRule::RULE_OTHER_HAS_LOAN_APP_COUNT => self::RULE_OTHER_60001, // applists criteria failed  应用列表不合格
        SystemApproveRule::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => self::RULE_OTHER_60002, // applists criteria failed 应用列表不合格
        SystemApproveRule::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT => self::RULE_OTHER_60005, // customer's identity in associate with overdue account 关联账号申请条件不符
        SystemApproveRule::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT => self::RULE_OTHER_60006, // applists criteria failed  应用列表不合格
        
        //自建应用列表nio.wang
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0001 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0002 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0003 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0004 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0005 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0006 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0007 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0008 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0009 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0010 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0011 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0012 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0013 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0014 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0015 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_APP_ANTIFRAUD_RULE0016 => self::RULE_OTHER_60001,
        SystemApproveRule::RULE_THIRD_DATA_WHATSAPP => self::RULE_APPLY_10027,
        SystemApproveRule::RULE_THIRD_DATA_AIRUDDER => self::RULE_APPLY_10035,
        SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_BLACK => self::RULE_APPLY_10028,
        
        SystemApproveRule::RULE_THIRD_DATA_RICHCLOUD_VERIFY => self::RULE_APPLY_10029,
    ];
    /**
     * @var string
     */
    protected $table = 'system_approve_record';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public static function addRecord($params)
    {
        $data = [
            'app_id' => $params['app_id'],
            'task_id' => $params['task_id'],
            'module' => $params['module'],
            'user_id' => $params['user_id'],
            'order_id' => $params['order_id'],
            'user_type' => $params['user_type'],
            'result' => $params['result'],
            'hit_rule_cnt' => $params['hit_rule_cnt'],
            'hit_rule' => $params['hit_rule'],
            'exe_rule' => $params['exe_rule'],
            'description' => $params['description'],
            'relate_id' => $params['relate_id'] ?? null,
            'experian_relate_id' => $params['experian_relate_id'] ?? null,
            'extra_code' => $params['extra_code'] ?? null,
        ];

        return self::create($data);
    }

    public static function getRejectReason($orderId)
    {
        $record = self::query()->where('order_id', $orderId)
            ->orderByDesc('id')
            ->value('hit_rule');

        $hitRuleRecord = json_decode($record, true);

        if (!$hitRuleRecord) {
            return [];
        }

        $hitRule = array_keys($hitRuleRecord);

        if (is_numeric($hitRule[0])) {
            $hitRule = array_column($hitRuleRecord, 'rule');
        }

        return SystemApproveRule::showTypeClassifyReason($hitRule);
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 获取命中规则对应code列表
     * @return array
     */
    public function getHitRuleCode()
    {
        $record = $this->hit_rule;
        $hitRuleRecord = json_decode($record, true);

        if (!$hitRuleRecord) {
            return [];
        }

        $hitRuleCode = [];
        foreach (array_keys($hitRuleRecord) as $hitRule) {
            $hitRuleCode[] = array_get(self::RESULT_CODE, $hitRule, self::RULE_00000);
        }

        return $hitRuleCode;
    }

//    /**
//     * 关联商户表
//     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
//     */
//    public function merchant()
//    {
//        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
//    }
}
