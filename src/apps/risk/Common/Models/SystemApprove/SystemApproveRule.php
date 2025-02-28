<?php

namespace Risk\Common\Models\SystemApprove;

use Common\Utils\Data\ArrayHelper;
use Risk\Common\Helper\CityHelper;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\Business\User\UserWork;
use Risk\Common\Models\RiskBaseModel;
use Risk\Common\Services\Risk\RiskAppHitServer;

/**
 * Risk\Common\Models\SystemApprove\SystemApproveRule
 *
 * @property int $id id
 * @property int $app_id 商户id
 * @property string|null $module 所属模块 basic:基础 credit:征信
 * @property int $user_quality 用户类型 0:新用户 1:老用户
 * @property string $rule 规则名
 * @property int $type 类型 1:字符串 2:范围 3:选项 4:多属性
 * @property string|null $value 规则值
 * @property int $status 状态 -1:删除 1:正常 2:关闭-执行规则保存规则执行记录，但结果不影响机审结果  3:彻底关闭-彻底不执行规则(主要为防止某些规则执行占用时间)
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @property int $node 審批節點
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereNode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereUserQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemApproveRule whereValue($value)
 * @mixin \Eloquent
 */
class SystemApproveRule extends RiskBaseModel
{
    /** 规则：年龄不符合要求[min,max]，包含边界值 */
    const RULE_APPLY_AGE_UNQUALIFIED = 'APPLY_AGE_UNQUALIFIED';
    /** 规则：有小于[day]天内被审批拒绝订单 */
    const RULE_APPLY_HAS_REJECTED_ORDERS = 'APPLY_HAS_REJECTED_ORDERS';
    /** 规则：命中黑名单（开关） */
    const RULE_APPLY_ON_BLACK_LIST = 'APPLY_ON_BLACK_LIST';
    /** 规则：学历在被拒列表[option1,option2,option3] */
    const RULE_APPLY_EDUCATION_UNQUALIFIED = 'APPLY_EDUCATION_UNQUALIFIED';

    /*************************************************************************************************************
     * Rule start 此处规则全为拒绝规则，命中则拒绝
     ************************************************************************************************************/
    /******************************************** 申请规则 *******************************************************/
    /** 规则：职业类型在被拒列表[option1,option2,option3] */
    const RULE_APPLY_CAREER_TYPE_UNQUALIFIED = 'APPLY_CAREER_TYPE_UNQUALIFIED';
    /** 规则：首次申请贷款资料填写时间小于[minute]分钟 */
    const RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME = 'APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME';
    /** 规则：申请时间在范围[H:i-H:i]，且当日已创建大于等于[n]笔订单 */
    const RULE_APPLY_CREATE_TIME = 'APPLY_CREATE_TIME';
    /** 规则：相关住址在被拒列表[option1,option2,option3]. 地址指GPS地址或永久住址. @see CityHelper 区域划分 */
    const RULE_APPLY_ADDRESS_UNQUALIFIED = 'APPLY_ADDRESS_UNQUALIFIED';
    /** 规则：多头命中数(此为内部商户多头) 大于等于[count] */
    const RULE_APPLY_MULTIPOINT_HIT_COUNT = 'APPLY_MULTIPOINT_HIT_COUNT';
    /** 规则：命中Krazybee且年龄大于等于[age] */
    const RULE_APPLY_HIT_KB_AGE_OUT = 'APPLY_HIT_KB_AGE_OUT';
    /** 规则：小于等于[num]天内，半径[num]内有大于等于[num]笔申请订单 */
    const RULE_APPLY_GPS_RADIUS_LIMIT = 'APPLY_GPS_RADIUS_LIMIT';
    /** 规则：开始验证流程的时间在[min,max]内(取基础信息认证完成时间) */
    const RULE_APPLY_START_VERIFY_TIME = 'APPLY_START_VERIFY_TIME';
    /** 规则：提交订单时的ip地址不在印度 */
    const RULE_APPLY_IP_NOT_IN_INDIA = 'APPLY_IP_NOT_IN_INDIA';
    /** 规则：提交订单时的GPS地址不在印度 */
    const RULE_APPLY_GPS_NOT_IN_INDIA = 'APPLY_GPS_NOT_IN_INDIA';
    /** 规则：用户注册手机号首位在被拒列表[n1,n2]，或用户注册手机号首位在列表[m1,m2]且紧急联系人手机号首位在被拒列表. */
    const RULE_APPLY_TELEPHONE_INITIAL_IN_LIST = 'APPLY_TELEPHONE_INITIAL_IN_LIST';
    /** 规则：紧急联系人手机号首位都在被拒列表[list] */
    const RULE_APPLY_EMERGENCY_CONTACTS_INITIAL_ALL_IN_LIST = 'APPLY_EMERGENCY_CONTACTS_INITIAL_ALL_IN_LIST';
    /** 规则：用户本次申请之前app列表中包含小于[n]个[list]列表app */
    const RULE_APPLY_ALL_APPLICATION_IN_TOP_APP_COUNT = 'APPLY_ALL_APPLICATION_IN_TOP_APP_COUNT';
    /** 规则：用户年龄小于等于[age]且学历在[list] */
    const RULE_APPLY_AGE_RESTRICT_EDUCATION_UNQUALIFIED = 'APPLY_AGE_RESTRICT_EDUCATION_UNQUALIFIED';
    /** 规则：用户在[list]时间段提交订单且婚姻状况为[list]且学历为[list]且性别为[list] */
    const RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE = 'APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE';
    /** 规则：注册渠道ID在列表[list] */
    const RULE_APPLY_CHANNEL_ID_IN_LIST = 'APPLY_CHANNEL_ID_IN_LIST';
    /** 规则：紧急联系人号码与sim卡抓取的手机号一致 */
    const RULE_APPLY_EMERGENCY_CONTACTS_EQUAL_SIM_TEL = 'APPLY_EMERGENCY_CONTACTS_EQUAL_SIM_TEL';
    /** 规则：用户的employment_type 在[]列表且workplace_pincode返回的province与常驻province不相符 */
    const RULE_APPLY_EMPLOYMENT_TYPE_IN_LIST_AND_WORKPLACE_PINCODE_DIFF = 'APPLY_EMPLOYMENT_TYPE_IN_LIST_AND_WORKPLACE_PINCODE_DIFF';
    /** 规则：宗教在被拒列表[option1],且常驻省份在列表[list] */
    const RULE_APPLY_RELIGION_IN_LIST_AND_STATE_UNQUALIFIED = 'APPLY_RELIGION_IN_LIST_AND_STATE_UNQUALIFIED';
    /** 规则：用户绑定放款银行卡在被拒列表[list]【拒绝】 */
    const RULE_APPLY_BANK_IN_REJECTED_LIST = 'APPLY_BANK_IN_REJECTED_LIST';
    /** 规则：本次申请前采集（包括本次）的剔重设备数  */
    const RULE_APPLY_DISTINCT_DEVICE_COUNT = 'APPLY_DISTINCT_DEVICE_COUNT';
    /** 规则：同一设备申请次数大于[count] */
    const RULE_DEVICE_SAME_APPLY_COUNT = 'DEVICE_SAME_APPLY_COUNT';
    /** 规则：同一设备指定天数小于等于[days]内申请次数大于[count] */
    const RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT = 'DEVICE_SAME_TIME_RANGE_APPLY_COUNT';
    /** 规则：同一设备当前正在申请(处于审批阶段)的笔数大于等于[count] */
    const RULE_DEVICE_SAME_IN_APPROVE_COUNT = 'DEVICE_SAME_IN_APPROVE_COUNT';
    /** 规则：同一设备逾期大于等于[m]天的次数大于等于[n] */
    const RULE_DEVICE_SAME_OVERDUE_COUNT = 'DEVICE_SAME_OVERDUE_COUNT';
    /** 规则：命中黑名单20200826 nio.wang */
    const RULE_APPLY_IN_BLACKLIST = 'APPLY_IN_BLACKLIST';
    /******************************************** 设备验证 *******************************************************/
    /** 规则：同一设备匹配 身份标识 个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD */
    const RULE_DEVICE_SAME_MATCHING_IDENTITY = 'DEVICE_SAME_MATCHING_IDENTITY';
    /** 规则：同一设备匹配手机号个数大于等于[count] */
    const RULE_DEVICE_SAME_MATCHING_TELEPHONE = 'DEVICE_SAME_MATCHING_TELEPHONE';
    /** 规则：同一设备指定时间段[2.00-3.00](包含边界值)，申请次数大于等于[count] */
    const RULE_DEVICE_SAME_TIME_PERIOD_APPLY_COUNT = 'DEVICE_SAME_TIME_PERIOD_APPLY_COUNT';
    /** 规则：设备类型验证（开关） */
    const RULE_DEVICE_TYPE = 'DEVICE_TYPE';
    /** 规则：手机内存小于[count]M */
    const RULE_DEVICE_PHONE_MEMORY = 'DEVICE_PHONE_MEMORY';
    /** 规则：手机剩余内存小于等于[count]M */
    const RULE_DEVICE_PHONE_FREE_MEMORY = 'DEVICE_PHONE_FREE_MEMORY';
    /** 规则：申请设备号变更(本次申请设备号与上次申请设备号不相同) */
    const RULE_DEVICE_UNIQUE_NUMBER_CHANGE = 'DEVICE_UNIQUE_NUMBER_CHANGE';
    /** 规则：同一银行卡匹配 身份标识 个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD */
    const RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY = 'AUTH_SAME_BANK_CARD_MATCHING_IDENTITY';
    /** 规则：同一银行卡匹配手机号个数大于等于[count] */
    const RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE = 'AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE';
    /** 规则：同一银行卡[m]天内在其他商户有过超过[n]次成功放款记录 */
    const RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER = 'AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER';
    /** 规则：同一 身份标识 匹配手机号个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD */
    const RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE = 'AUTH_SAME_IDENTITY_MATCHING_TELEPHONE';
    /******************************************** 身份验证 *******************************************************/
    /** 规则：同一 身份标识 匹配设备号个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD */
    const RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE = 'AUTH_SAME_IDENTITY_MATCHING_DEVICE';
    /** 规则：关联账户有小于[days]天内被审批拒绝的订单（开关） */
    const RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS = 'AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS';
    /** 规则：关联账户有进行中(未完结&审批中)的订单（开关） */
    const RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER = 'AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER';
    /** 规则：人脸匹配低于[count]%拒绝  */
    const RULE_AUTH_FACE_MATCH = 'AUTH_FACE_MATCH';
    /** 规则：上架小于等于[num]天内申请的订单，匹配到的关联账户订单数大于等于[num] (跨商户关联,关联条件不包括紧急联系人) */
    const RULE_AUTH_RELATE_ACCOUNT_PUTAWAY_APPLY_COUNT = 'AUTH_RELATE_ACCOUNT_PUTAWAY_APPLY_COUNT';
    /** 规则：OCR读取姓名比对 */
    const RULE_AUTH_OCR_NAME_COMPARISON = 'AUTH_OCR_NAME_COMPARISON';
    /** 规则：aadhaar card验证返回手机号不为空情况下，与注册手机号不匹配 */
    const RULE_AUTH_AADHAAR_PHONE_NUM_NO_MATCH_TELEPHONE = 'AUTH_AADHAAR_PHONE_NUM_NO_MATCH_TELEPHONE';
    /** 规则：用户计算得出的年龄不在 aadhaar返回的年龄区间内 */
    const RULE_AUTH_AADHAAR_AGE_BAND_NO_MATCH_AGE = 'AUTH_AADHAAR_AGE_BAND_NO_MATCH_AGE';
    /** 规则：fullname和银行卡返回benename比对不匹配 */
    const RULE_AUTH_BANK_BENENAME_NO_MATCH_FULLNAME = 'AUTH_BANK_BENENAME_NO_MATCH_FULLNAME';
    /** 规则：姓名验证(aadhaar_verify流程下，voterNameCheck或passportNameCheck验证不通过) */
    const RULE_AUTH_PAPERS_NAME_CHECK = 'AUTH_PAPERS_NAME_CHECK';
    /** 规则：通讯录内正在申请(处于审批阶段)贷款的人数大于[count] */
    const RULE_CONTACTS_IN_APPROVE_COUNT = 'CONTACTS_IN_APPROVE_COUNT';
    /** 规则：通讯录内当前处于逾期且天数大于等于[m]天贷款人数超过[n] */
    const RULE_CONTACTS_IN_OVERDUE_COUNT = 'CONTACTS_IN_OVERDUE_COUNT';
    /** 规则：通讯录人数小于[count] */
    const RULE_CONTACTS_COUNT = 'CONTACTS_COUNT';
    /** 规则：用户通讯录中有大于等于[n]个号码发生过注册行为（跨商户）*/
    const RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT = 'CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT';
    /******************************************** 通讯录验证 *******************************************************/
    /** 规则：用户通讯录中有大于等于[n]个号码发生过申请行为（跨商户）*/
    const RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT = 'CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT';
    /** 规则：用户通讯录姓名中包含列表app名的号码数大于等于[n]个【拒绝】比对规则，去空格去大小写 */
    const RULE_CONTACTS_CONTACT_NAME_IN_APP_LIST_CNT = 'CONTACTS_CONTACT_NAME_IN_APP_LIST_CNT';
    /** 规则：通讯录击中公共类号码的数量 */
    const RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT = 'CONTACTS_HIT_COMMON_TELEPHONE_CNT';
    /** 规则：通讯录击中催收distinct(APP数)大于等于[n]个且用户通讯录中有大于等于[m]个号码发生过申请行为【拒绝】 */
    const RULE_CONTACTS_HIT_COLLECTION_PHONE_CNT_AND_APPLY_CNT = 'CONTACTS_HIT_COLLECTION_PHONE_CNT_AND_APPLY_CNT';
    /** 规则：历史被拒次数大于等于[count] */
    const RULE_BEHAVIOR_REJECT_COUNT = 'BEHAVIOR_REJECT_COUNT';
    /** 规则：历史逾期天数大于等于[m]天的次数大于等于[n] */
    const RULE_BEHAVIOR_OVERDUE_COUNT = 'BEHAVIOR_OVERDUE_COUNT';
    /** 规则：历史最大逾期天数大于等于[count] */
    const RULE_BEHAVIOR_MAX_OVERDUE_DAYS = 'BEHAVIOR_MAX_OVERDUE_DAYS';
    /** 规则：上一笔订单逾期天数大于等于[count] */
    const RULE_BEHAVIOR_LATELY_OVERDUE_DAYS = 'BEHAVIOR_LATELY_OVERDUE_DAYS';
    /******************************************** 借款行为 *******************************************************/
    /** 规则：上一笔订单催收电话次数大于等于[count]. (只计已逾期订单的本人和紧急联系人的催记) */
    const RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT = 'BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT';
    /** 规则：已结清[a]次的老用户上次贷款在[n]天内结清且在结清后[m]小时申请下一笔贷款 */
    const RULE_BEHAVIOR_QUICK_FINISH_APPLY_NEXT_ORDER = 'BEHAVIOR_QUICK_FINISH_APPLY_NEXT_ORDER';
    /** 规则：APP列表中贷款产品数大于[count] @see RiskAppHitServer */
    const RULE_OTHER_HAS_LOAN_APP_COUNT = 'OTHER_HAS_LOAN_APP_COUNT';
    /** 规则：安装时间距离申请时间[day]天内贷款产品APP数大于[count] */
    const RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT = 'OTHER_RECENT_INSTALL_LOAN_APP_COUNT';
    /** 规则：关联账号(手机号,imei,aadhaar,pan)近[n]天内在其他商户未完成且审批通过的订单数大于等于[m]笔（审批通过，已签约，已放款未结清，出款失败） */
    const RULE_OTHER_RELATE_MERCHANT_HAS_NOT_COMPLETE_COUNT = 'OTHER_RELATE_MERCHANT_HAS_NOT_COMPLETE_COUNT';
    /** 规则：关联账号按时结清首借的订单数小于首借放款订单数【拒绝】 */
    const RULE_OTHER_RELATE_NEW_FINISH_ON_TIME_LT_NEW_PAID_ORDER = 'OTHER_RELATE_NEW_FINISH_ON_TIME_LT_NEW_PAID_ORDER';
    /******************************************** 其他规则 *******************************************************/
    /** 规则：关联账号逾期小于等于[n]天并结清的的订单数大于等于[m]个【拒绝】 */
    const RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT = 'OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT';
    /** 规则：全量高危app击中数大于等于[n]个【拒绝】,app清单见附件全量高危app清单 */
    const RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT = 'OTHER_ALL_HIGH_RISK_APP_HIT_CNT';
    /** 规则：最早高危app安装时间距离申请时间间隔（按天）小于等于[n]天【拒绝】 */
    const RULE_OTHER_FIRST_INSTALL_TIME_AND_ORDER_CREATE_TIME_DIFF_DAYS = 'OTHER_FIRST_INSTALL_TIME_AND_ORDER_CREATE_TIME_DIFF_DAYS';
    
    /** 自建规则nio.wang **/
    const RULE_APP_ANTIFRAUD_RULE0001 = 'APP_ANTIFRAUD_RULE0001';
    const RULE_APP_ANTIFRAUD_RULE0002 = 'APP_ANTIFRAUD_RULE0002';
    const RULE_APP_ANTIFRAUD_RULE0003 = 'APP_ANTIFRAUD_RULE0003';
    const RULE_APP_ANTIFRAUD_RULE0004 = 'APP_ANTIFRAUD_RULE0004';
    const RULE_APP_ANTIFRAUD_RULE0005 = 'APP_ANTIFRAUD_RULE0005';
    const RULE_APP_ANTIFRAUD_RULE0006 = 'APP_ANTIFRAUD_RULE0006';
    const RULE_APP_ANTIFRAUD_RULE0007 = 'APP_ANTIFRAUD_RULE0007';
    const RULE_APP_ANTIFRAUD_RULE0008 = 'APP_ANTIFRAUD_RULE0008';
    const RULE_APP_ANTIFRAUD_RULE0009 = 'APP_ANTIFRAUD_RULE0009';
    const RULE_APP_ANTIFRAUD_RULE0010 = 'APP_ANTIFRAUD_RULE0010';
    const RULE_APP_ANTIFRAUD_RULE0011 = 'APP_ANTIFRAUD_RULE0011';
    const RULE_APP_ANTIFRAUD_RULE0012 = 'APP_ANTIFRAUD_RULE0012';
    const RULE_APP_ANTIFRAUD_RULE0013 = 'APP_ANTIFRAUD_RULE0013';
    const RULE_APP_ANTIFRAUD_RULE0014 = 'APP_ANTIFRAUD_RULE0014';
    const RULE_APP_ANTIFRAUD_RULE0015 = 'APP_ANTIFRAUD_RULE0015';
    const RULE_APP_ANTIFRAUD_RULE0016 = 'APP_ANTIFRAUD_RULE0016';
    
    //Kudos pan
    const RULE_KUDOS_COVER_PAN = "KUDOS_COVER_PAN";
    //Kreditone
    const RULE_KREDITONE_SCORE = "KREDITONE_SCORE";
    
    //What'sapp 
    const RULE_THIRD_DATA_WHATSAPP = "THIRD_DATA_WHATSAPP";
    const RULE_THIRD_DATA_RICHCLOUD_BLACK = "THIRD_DATA_RICHCLOUD_BLACK";
    const RULE_THIRD_DATA_RICHCLOUD_VERIFY = "THIRD_DATA_RICHCLOUD_VERIFY";
    const RULE_THIRD_DATA_AIRUDDER = "THIRD_DATA_AIRUDDER";
    
    //20200927 新规则
    const RULE_KUDOS_USER_SCORE_LOW = "KUDOS_USER_SCORE_LOW";
    const RULE_KUDOS_USER_SCORE_KREDITONE_LOW = "KUDOS_USER_SCORE_KREDITONE_LOW";
    const RULE_KUDOS_NULL = "KUDOS_NULL";
    const RULE_NO_WHATSAPP_USER_SCORE_LOW = "NO_WHATSAPP_USER_SCORE_LOW";
    
    #复贷新规则
    const RULE_WOMEN_LAST_LOAN_OVERDUE_DAYS = "WOMEN_LAST_LOAN_OVERDUE_DAYS";
    const RULE_MAN_LAST_LOAN_OVERDUE_DAYS = "MAN_LAST_LOAN_OVERDUE_DAYS";
    const RULE_WOMEN_HIS_OVERDUE_DAYS = "WOMEN_HIS_OVERDUE_DAYS";
    const RULE_MAN_HIS_OVERDUE_DAYS = "MAN_HIS_OVERDUE_DAYS";
    const RULE_WOMEN_EDU_HIS_OVERDUE_DAYS = "WOMEN_EDU_HIS_OVERDUE_DAYS";
    const RULE_MAN_EDU_HIS_OVERDUE_DAYS = "MAN_EDU_HIS_OVERDUE_DAYS";
    
    //check open order ruleCheckOpenOrder
    const RULE_CHECK_OPEN_ORDER = "CHECK_OPEN_ORDER";
    /** @var array 规则类型 */
    const TYPE_RELATE_RULE = [
        /** 申请规则 */
        self::RULE_APPLY_AGE_UNQUALIFIED => self::TYPE_RANGE,
        self::RULE_APPLY_HAS_REJECTED_ORDERS => self::TYPE_STRING,
        self::RULE_APPLY_EDUCATION_UNQUALIFIED => self::TYPE_OPTION,
        self::RULE_APPLY_CAREER_TYPE_UNQUALIFIED => self::TYPE_OPTION,
        self::RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME => self::TYPE_STRING,
        self::RULE_APPLY_CREATE_TIME => self::TYPE_MULTIPLE,
        self::RULE_APPLY_ADDRESS_UNQUALIFIED => self::TYPE_OPTION,
        self::RULE_APPLY_START_VERIFY_TIME => self::TYPE_RANGE,
        self::RULE_APPLY_IP_NOT_IN_INDIA => self::TYPE_STRING,
        self::RULE_APPLY_GPS_NOT_IN_INDIA => self::TYPE_STRING,
        self::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE => self::TYPE_MULTIPLE,
        self::RULE_APPLY_CHANNEL_ID_IN_LIST => self::TYPE_OPTION,
        self::RULE_APPLY_BANK_IN_REJECTED_LIST => self::TYPE_OPTION,
        self::RULE_APPLY_DISTINCT_DEVICE_COUNT => self::TYPE_STRING,
        self::RULE_CHECK_OPEN_ORDER => self::TYPE_STRING,
        /** 设备验证 */
        self::RULE_DEVICE_SAME_APPLY_COUNT => self::TYPE_STRING,
        self::RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT => self::TYPE_MULTIPLE,
        self::RULE_DEVICE_SAME_IN_APPROVE_COUNT => self::TYPE_STRING,
        self::RULE_DEVICE_SAME_OVERDUE_COUNT => self::TYPE_MULTIPLE,
        self::RULE_DEVICE_SAME_MATCHING_IDENTITY => self::TYPE_STRING,
        self::RULE_DEVICE_SAME_MATCHING_TELEPHONE => self::TYPE_STRING,
        self::RULE_DEVICE_TYPE => self::TYPE_STRING,
        self::RULE_DEVICE_PHONE_MEMORY => self::TYPE_STRING,
        self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => self::TYPE_STRING,
        self::RULE_DEVICE_PHONE_FREE_MEMORY => self::TYPE_STRING,
        /** 身份验证 */
        self::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => self::TYPE_STRING,
        self::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => self::TYPE_STRING,
        self::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => self::TYPE_MULTIPLE,
        self::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => self::TYPE_STRING,
        self::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => self::TYPE_STRING,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => self::TYPE_STRING,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => self::TYPE_STRING,
        self::RULE_AUTH_FACE_MATCH => self::TYPE_STRING,
        self::RULE_AUTH_OCR_NAME_COMPARISON => self::TYPE_STRING,
        /** 通讯录验证 */
        self::RULE_CONTACTS_IN_APPROVE_COUNT => self::TYPE_STRING,
        self::RULE_CONTACTS_IN_OVERDUE_COUNT => self::TYPE_MULTIPLE,
        self::RULE_CONTACTS_COUNT => self::TYPE_STRING,
        self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => self::TYPE_STRING,
        self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => self::TYPE_STRING,
        self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT => self::TYPE_STRING,
        /** 借款行为 */
        self::RULE_BEHAVIOR_REJECT_COUNT => self::TYPE_STRING,
        self::RULE_BEHAVIOR_OVERDUE_COUNT => self::TYPE_MULTIPLE,
        self::RULE_BEHAVIOR_MAX_OVERDUE_DAYS => self::TYPE_STRING,
        self::RULE_BEHAVIOR_LATELY_OVERDUE_DAYS => self::TYPE_STRING,
        self::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT => self::TYPE_STRING,
        /** 其他 */
        self::RULE_OTHER_HAS_LOAN_APP_COUNT => self::TYPE_STRING,
        self::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => self::TYPE_MULTIPLE,
        self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT => self::TYPE_MULTIPLE,
        self::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT => self::TYPE_STRING,
        /** 自建规则nio.wang **/
        self::RULE_APP_ANTIFRAUD_RULE0001 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0002 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0003 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0004 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0005 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0006 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0007 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0008 => self::TYPE_MULTIPLE,
        self::RULE_APP_ANTIFRAUD_RULE0009 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0010 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0011 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0012 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0013 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0014 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0015 => self::TYPE_STRING,
        self::RULE_APP_ANTIFRAUD_RULE0016 => self::TYPE_MULTIPLE,
        self::RULE_APPLY_IN_BLACKLIST => self::TYPE_STRING, //nio.wang 20200806
        self::RULE_THIRD_DATA_WHATSAPP => self::TYPE_STRING,
        self::RULE_KUDOS_COVER_PAN => self::TYPE_MULTIPLE,
        self::RULE_KREDITONE_SCORE => self::TYPE_STRING,
        self::RULE_THIRD_DATA_AIRUDDER => self:: TYPE_STRING,
        //20200927新首贷规则
        self::RULE_KUDOS_USER_SCORE_LOW => self::TYPE_STRING,
        self::RULE_KUDOS_USER_SCORE_KREDITONE_LOW => self::TYPE_MULTIPLE,
        self::RULE_KUDOS_NULL => self::TYPE_STRING,
        self::RULE_NO_WHATSAPP_USER_SCORE_LOW => self::TYPE_STRING,
        /** 复贷规则 **/
        self::RULE_WOMEN_LAST_LOAN_OVERDUE_DAYS => self::TYPE_STRING,
        self::RULE_MAN_LAST_LOAN_OVERDUE_DAYS => self::TYPE_STRING,
        self::RULE_WOMEN_HIS_OVERDUE_DAYS => self::TYPE_MULTIPLE,
        self::RULE_MAN_HIS_OVERDUE_DAYS => self::TYPE_MULTIPLE,
        self::RULE_WOMEN_EDU_HIS_OVERDUE_DAYS => self::TYPE_MULTIPLE,
        self::RULE_MAN_EDU_HIS_OVERDUE_DAYS => self::TYPE_MULTIPLE,
    ];
    /** @var string 申请规则 */
    const APPLY_RULE = 'apply_rule';
    /** @var string 设备验证 */
    const DEVICE_RULE = 'device_rule';
    /** @var string 身份验证 */
    const AUTH_RULE = 'auth_rule';

    /*************************************************************************************************************
     * Rule end
     ************************************************************************************************************/
    /** @var string 通讯录验证 */
    const CONTACTS_RULE = 'contacts_rule';

    /** 规则分类 */
    /** @var string 借款行为 */
    const BEHAVIOR_RULE = 'behavior_rule';
    /** @var string 其他 */
    const OTHER_RULE = 'other_rule';
    /** 新复贷规则 **/
    const NEW_RULE = 'new_rule';
    /** @var array 新用户：拒绝规则 */
    const USER_TYPE_NEW_RULE_DEFAULT = [
        self::APPLY_RULE => [
            /** 申请规则 */
            self::RULE_APPLY_AGE_UNQUALIFIED => [22, 45],
            self::RULE_APPLY_HAS_REJECTED_ORDERS => 29,
            self::RULE_APPLY_EDUCATION_UNQUALIFIED => [UserInfo::EDUCATIONAL_TYPE_PRIMARY],
            self::RULE_APPLY_CAREER_TYPE_UNQUALIFIED => [UserWork::EMPLOYMENT_TYPE_NO_JOB],
            self::RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME => 1,
            self::RULE_APPLY_CREATE_TIME => [["00:00", "05:59"], 0],
            self::RULE_APPLY_ADDRESS_UNQUALIFIED => ['Kerala', 'Karnataka', 'Bihar', 'Maharashtra', 'Assam', 'Megalaya', 'Tamilnadu', 'West Bengal'],
            self::RULE_APPLY_START_VERIFY_TIME => ['00:00', '05:59'],
            self::RULE_APPLY_IP_NOT_IN_INDIA => null,
            self::RULE_APPLY_GPS_NOT_IN_INDIA => null,
            self::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE => [
                [["07:00", "07:59"], ["18:00", "19:59"]],
                [UserInfo::MARITAL_UNMARRIED],
                [],
                [UserInfo::GENDER_MALE],
            ],
            self::RULE_APPLY_CHANNEL_ID_IN_LIST => [7],
            self::RULE_APPLY_BANK_IN_REJECTED_LIST => ['YES BANK'],
            self::RULE_APPLY_IN_BLACKLIST => null,
            self::RULE_THIRD_DATA_WHATSAPP => null,
            self::RULE_CHECK_OPEN_ORDER => null,
            self::RULE_THIRD_DATA_AIRUDDER => null,
            //20200927新的规则
            self::RULE_KUDOS_USER_SCORE_LOW => 503,
            self::RULE_KUDOS_USER_SCORE_KREDITONE_LOW => [516, 630],
            self::RULE_KUDOS_NULL => 503,
            self::RULE_NO_WHATSAPP_USER_SCORE_LOW => 503,
        ],
        /** 设备验证 */
        self::DEVICE_RULE => [
            self::RULE_DEVICE_SAME_APPLY_COUNT => 99,
            self::RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT => [1, 2],
            self::RULE_DEVICE_SAME_IN_APPROVE_COUNT => 2,
            self::RULE_DEVICE_SAME_OVERDUE_COUNT => [3, 2],
            self::RULE_DEVICE_SAME_MATCHING_IDENTITY => 2,
            self::RULE_DEVICE_SAME_MATCHING_TELEPHONE => 2,
            self::RULE_DEVICE_TYPE => null,
            self::RULE_DEVICE_PHONE_MEMORY => 9000,
            self::RULE_DEVICE_PHONE_FREE_MEMORY => 500,
            self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => null,
            /*             * 自建规则nio.wang* */
            self::RULE_APP_ANTIFRAUD_RULE0001 => 0.033,
            self::RULE_APP_ANTIFRAUD_RULE0002 => 27,
            self::RULE_APP_ANTIFRAUD_RULE0003 => 0.151,
            self::RULE_APP_ANTIFRAUD_RULE0004 => 15,
            self::RULE_APP_ANTIFRAUD_RULE0005 => 0.958,
            self::RULE_APP_ANTIFRAUD_RULE0006 => 0.9,
            self::RULE_APP_ANTIFRAUD_RULE0007 => 1,
            self::RULE_APP_ANTIFRAUD_RULE0008 => [1,0.7,10],
            self::RULE_APP_ANTIFRAUD_RULE0009 => 0.146,
            self::RULE_APP_ANTIFRAUD_RULE0010 => 0,
            self::RULE_APP_ANTIFRAUD_RULE0011 => 2,
            self::RULE_APP_ANTIFRAUD_RULE0012 => 3,
            self::RULE_APP_ANTIFRAUD_RULE0013 => 0,
            self::RULE_APP_ANTIFRAUD_RULE0014 => 0,
            self::RULE_APP_ANTIFRAUD_RULE0015 => 1,
            self::RULE_APP_ANTIFRAUD_RULE0016 => [0, 100],
        ],
        /** 身份验证 */
        self::AUTH_RULE => [
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => 2,
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => 2,
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => [1, 0],
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => 2,
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => 2,
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => 30,
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => null,
            self::RULE_AUTH_FACE_MATCH => 70,
            self::RULE_AUTH_OCR_NAME_COMPARISON => null,
            self::RULE_KUDOS_COVER_PAN => [0,0],
            self::RULE_KREDITONE_SCORE => 610,
        ],
        /** 通讯录验证 */
        self::CONTACTS_RULE => [
            self::RULE_CONTACTS_IN_APPROVE_COUNT => 0,
            self::RULE_CONTACTS_IN_OVERDUE_COUNT => [3, 0],
            self::RULE_CONTACTS_COUNT => 60,
            self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => 5,
            self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => 3,
            self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT => 10,
        ],
        /** 借款行为 */
        self::BEHAVIOR_RULE => [
            self::RULE_BEHAVIOR_REJECT_COUNT => 3,
        ],
        /** 其他 */
        self::OTHER_RULE => [
            self::RULE_OTHER_HAS_LOAN_APP_COUNT => 30,
            self::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => [30, 13],
            self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT => [1, 3],
            self::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT => 30,
        ]
    ];
    /**
     * 新用户规则-初始化为关闭的规则列表
     */
    const USER_TYPE_NEW_RULE_DEFAULT_STATUS_IS_CLOSE = [
        self::RULE_DEVICE_TYPE,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER,
        self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE,
        self::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE,
        self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT,
        self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT,
        self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT,
        self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT,
    ];
    /** @var array 老用户规则 */
    const USER_TYPE_OLD_RULE_DEFAULT = [
        /** 申请规则 */
        self::APPLY_RULE => [
            self::RULE_APPLY_CREATE_TIME => [["00:00", "05:59"], 99999],
            self::RULE_APPLY_ADDRESS_UNQUALIFIED => ['Kerala'],
            self::RULE_APPLY_IP_NOT_IN_INDIA => null,
            self::RULE_APPLY_GPS_NOT_IN_INDIA => null,
            self::RULE_APPLY_BANK_IN_REJECTED_LIST => ['YES BANK'],
            self::RULE_APPLY_DISTINCT_DEVICE_COUNT => 4,
            self::RULE_APPLY_IN_BLACKLIST => null,//nio.wang 200806
        ],
        /** 设备验证 */
        self::DEVICE_RULE => [
            self::RULE_DEVICE_SAME_IN_APPROVE_COUNT => 2,
            self::RULE_DEVICE_SAME_OVERDUE_COUNT => [3, 2],
            self::RULE_DEVICE_SAME_MATCHING_IDENTITY => 2,
            self::RULE_DEVICE_SAME_MATCHING_TELEPHONE => 2,
            self::RULE_DEVICE_TYPE => null,
            self::RULE_DEVICE_PHONE_MEMORY => 9000,
            self::RULE_DEVICE_PHONE_FREE_MEMORY => 500,
            self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => null,
        ],
        /** 身份验证 */
        self::AUTH_RULE => [
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => 2,
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => 2,
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => [1, 0],
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => 2,
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => 2,
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => 30,
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => null,
        ],
        /** 通讯录验证 */
        self::CONTACTS_RULE => [
            self::RULE_CONTACTS_IN_APPROVE_COUNT => 0,
            self::RULE_CONTACTS_IN_OVERDUE_COUNT => [3, 0],
            self::RULE_CONTACTS_COUNT => 60,
            self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => 10,
            self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => 3,
            self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT => 10,
        ],
        /** 借款行为 */
        self::BEHAVIOR_RULE => [
            self::RULE_BEHAVIOR_OVERDUE_COUNT => [1, 99],
            self::RULE_BEHAVIOR_MAX_OVERDUE_DAYS => 4,
            self::RULE_BEHAVIOR_LATELY_OVERDUE_DAYS => 2,
            self::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT => 3,
        ],
        /** 其他 */
        self::OTHER_RULE => [
            self::RULE_OTHER_HAS_LOAN_APP_COUNT => 13,
            self::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => [30, 200],
            self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT => [2, 5],
            self::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT => 40,
        ],
        self::NEW_RULE => [
            self::RULE_WOMEN_LAST_LOAN_OVERDUE_DAYS => 10,
            self::RULE_MAN_LAST_LOAN_OVERDUE_DAYS => 7,
            self::RULE_WOMEN_HIS_OVERDUE_DAYS => [5, 3000],
            self::RULE_MAN_HIS_OVERDUE_DAYS => [3, 3000],
            self::RULE_WOMEN_EDU_HIS_OVERDUE_DAYS => ["Other", 4, 3000],
            self::RULE_MAN_EDU_HIS_OVERDUE_DAYS => ["Other", 3, 3000],
        ]
    ];
    /**
     * 老用户用户规则-初始化为关闭的规则列表
     */
    const USER_TYPE_OLD_RULE_DEFAULT_STATUS_IS_CLOSE = [
        self::RULE_APPLY_CREATE_TIME,
        self::RULE_DEVICE_SAME_OVERDUE_COUNT,
        self::RULE_DEVICE_TYPE,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER,
        self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE,
        self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT,
        self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT,
        self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT,
        self::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT,
        self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT,
    ];
    /**
     * 在初始化脚本执行时需要强制更新的规则
     * 旧的规则进行了更新，如添加了阈值，修改了类型等需要强制修改已有规则
     */
    const NEED_FORCED_UPDATING_RULE = [
    ];
    /** 展示类别：准入拒绝 */
    const SHOW_TYPE_ACCESS_REFUSED = 'access_refused';
    /** 展示类别：人脸拒绝 */
    const SHOW_TYPE_FACE_REFUSED = 'face_refused';
    /** 展示类别：历史表现不良 */
    const SHOW_TYPE_HISTORICAL_UNDERPERFORMANCE = 'historical_underperformance';
    /** 展示类别：申请异常 */
    const SHOW_TYPE_APPLY_ABNORMAL = 'apply_abnormal';
    /** 展示类别：存在关联账户 */
    const SHOW_TYPE_EXIT_ASSOCIATED_ACCOUNT = 'exit_associated_account';
    /** 展示类别：通讯录不合格 */
    const SHOW_TYPE_CONTACT_UNQUALIFIED = 'contact_unqualified';
    /** 展示类别：征信不合格 */
    const SHOW_TYPE_CREDIT_UNQUALIFIED = 'credit_unqualified';
    /** 展示类别 */
    const SHOW_TYPE = [
        self::SHOW_TYPE_ACCESS_REFUSED => '准入拒绝',
        self::SHOW_TYPE_FACE_REFUSED => '人脸拒绝',
        self::SHOW_TYPE_HISTORICAL_UNDERPERFORMANCE => '历史表现不良',
        self::SHOW_TYPE_APPLY_ABNORMAL => '申请异常',
        self::SHOW_TYPE_EXIT_ASSOCIATED_ACCOUNT => '与其他账户存在关联关系',
        self::SHOW_TYPE_CONTACT_UNQUALIFIED => '通讯录不符合要求',
    ];
    /** 具体规则分类展示 */
    const SHOW_TYPE_CLASSIFY = [
        self::SHOW_TYPE_ACCESS_REFUSED => [
            self::RULE_APPLY_AGE_UNQUALIFIED => '年龄不符合',
            self::RULE_APPLY_EDUCATION_UNQUALIFIED => '学历不符合',
            self::RULE_APPLY_CAREER_TYPE_UNQUALIFIED => '职业不符合',
            self::RULE_APPLY_ADDRESS_UNQUALIFIED => '地址不符合',
            self::RULE_OTHER_HAS_LOAN_APP_COUNT => '设备不符合',
            self::RULE_OTHER_ALL_HIGH_RISK_APP_HIT_CNT => '设备不符合',
            self::RULE_OTHER_RECENT_INSTALL_LOAN_APP_COUNT => '设备不符合',
            self::RULE_APPLY_TIME_RANGE_MARRIAGE_EDUCATION_GENDER_MULTIPLE => '基础信息多重验证未通过',
            self::RULE_APPLY_CHANNEL_ID_IN_LIST => '渠道拒绝',
            self::RULE_APPLY_BANK_IN_REJECTED_LIST => '银行不合格',
            self::RULE_APPLY_DISTINCT_DEVICE_COUNT => '设备不符合',
            /*自建规则nio.wang*/
            self::RULE_APP_ANTIFRAUD_RULE0001 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0002 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0003 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0004 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0005 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0006 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0007 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0008 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0009 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0010 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0011 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0012 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0013 => "App安装列表不合格",
            self::RULE_APP_ANTIFRAUD_RULE0014 => "App安装列表不合格",
        ],
        self::SHOW_TYPE_FACE_REFUSED => [
            self::RULE_AUTH_FACE_MATCH => '人脸不合格',
        ],
        self::SHOW_TYPE_HISTORICAL_UNDERPERFORMANCE => [
            self::RULE_DEVICE_SAME_OVERDUE_COUNT => '设备有逾期历史',
            self::RULE_BEHAVIOR_REJECT_COUNT => '历史被拒次数过多',
            self::RULE_BEHAVIOR_OVERDUE_COUNT => '历史逾期次数过多',
            self::RULE_BEHAVIOR_MAX_OVERDUE_DAYS => '历史逾期天数过多',
            self::RULE_BEHAVIOR_LATELY_OVERDUE_DAYS => '历史逾期天数过多',
            self::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT => '历史催收次数过多',
        ],
        self::SHOW_TYPE_APPLY_ABNORMAL => [
            self::RULE_APPLY_HAS_REJECTED_ORDERS => '申请间隔过短',
            self::RULE_APPLY_FIRST_LOAN_INFORMATION_FILLING_TIME => '填写时长过短',
            self::RULE_APPLY_CREATE_TIME => '申请时间段不符合',
            self::RULE_DEVICE_SAME_IN_APPROVE_COUNT => '设备有在途申请',
            self::RULE_DEVICE_TYPE => '设备类型不符合',
            self::RULE_DEVICE_SAME_TIME_RANGE_APPLY_COUNT => '设备在规定天数申请多次',
            self::RULE_APPLY_START_VERIFY_TIME => '验证时间段不符合',
            self::RULE_DEVICE_PHONE_MEMORY => '设备属性不支持',
            self::RULE_DEVICE_PHONE_FREE_MEMORY => '设备属性不支持',
            self::RULE_AUTH_OCR_NAME_COMPARISON => '识别比对未匹配',
            self::RULE_APPLY_IP_NOT_IN_INDIA => '网络地址异常',
            self::RULE_APPLY_GPS_NOT_IN_INDIA => 'GPS地址异常',
            self::RULE_DEVICE_UNIQUE_NUMBER_CHANGE => '设备异常变更',
            self::RULE_APPLY_IN_BLACKLIST => '用户信息在黑名单内',
            self::RULE_THIRD_DATA_RICHCLOUD_BLACK => '用户在闪云黑名单内',
            self::RULE_THIRD_DATA_RICHCLOUD_VERIFY => '闪云的验证未通过',
            self::RULE_KUDOS_COVER_PAN => "Kudos风控数据未通过",
            self::RULE_CHECK_OPEN_ORDER => "存在未完成订单",
        ],
        self::SHOW_TYPE_EXIT_ASSOCIATED_ACCOUNT => [
            self::RULE_DEVICE_SAME_APPLY_COUNT => '设备重复申请',
            self::RULE_DEVICE_SAME_MATCHING_IDENTITY => '设备匹配多证件',
            self::RULE_DEVICE_SAME_MATCHING_TELEPHONE => '设备匹配多手机号',
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_TELEPHONE => '证件匹配多号码',
            self::RULE_AUTH_SAME_IDENTITY_MATCHING_DEVICE => '证件匹配多设备',
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_IDENTITY => '关联账号过多',
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_TELEPHONE => '关联账号过多',
            self::RULE_AUTH_SAME_BANK_CARD_MATCHING_MERCHANT_PAID_ORDER => '关联账号过多',
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS => '关联账号有被拒订单',
            self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER => '关联账号有在途申请',
            self::RULE_OTHER_RELATE_OVERDUE_DAYS_FINISH_ORDER_CNT => '关联账号申请条件不符',
        ],
        self::SHOW_TYPE_CONTACT_UNQUALIFIED => [
            self::RULE_CONTACTS_IN_APPROVE_COUNT => '通讯录在途申请过多',
            self::RULE_CONTACTS_IN_OVERDUE_COUNT => '通讯录逾期人数过多',
            self::RULE_CONTACTS_COUNT => '通讯录不合格',
            self::RULE_CONTACTS_REGISTER_COUNT_ACROSS_MERCHANT => '通讯录不合格',
            self::RULE_CONTACTS_HAS_APPLY_COUNT_ACROSS_MERCHANT => '通讯录不合格',
            self::RULE_CONTACTS_HIT_COMMON_TELEPHONE_CNT => '通讯录不合格',
        ]
    ];
    /** 规则类型：字符串 */
    const TYPE_STRING = 1;
    /** 规则类型：范围 */
    const TYPE_RANGE = 2;
    /** 规则类型：选项(单个属性，多个选项) */
    const TYPE_OPTION = 3;
    /** 规则类型：多个属性 */
    const TYPE_MULTIPLE = 4;
    /** 状态：删除 */
    const STATUS_DELETE = -1;
    /** 状态：开启 */
    const STATUS_NORMAL = 1;
    /** 状态：关闭-执行规则保存规则执行记录，但结果不影响机审结果 */
    const STATUS_CLOSE = 2;
    /** 状态：彻底关闭-彻底不执行规则(主要为防止某些规则执行占用时间) */
    const STATUS_ENTIRELY_CLOSE = 3;
    /**
     * 允许变更状态的规则(类型为[设置开关]的规则)
     */
    const ALLOWED_CHANGE_STATUS = [
        self::RULE_DEVICE_TYPE,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_REJECTED_ORDERS,
        self::RULE_AUTH_RELATE_ACCOUNT_HAS_UNDERWAY_ORDER,
    ];
    /** 用户类型：新用户 */
    const USER_QUALITY_NEW = 0;
    /** 用户类型：老用户 */
    const USER_QUALITY_OLD = 1;
    /** @var array 用户类型 */
    const USER_QUALITY = [
        self::USER_QUALITY_NEW => '新用户',
        self::USER_QUALITY_OLD => '老用户',
    ];
    /** 用户类型关联订单表用户类型 */
    const USER_QUALITY_RELATE = [
        Order::QUALITY_NEW => self::USER_QUALITY_NEW,
        Order::QUALITY_OLD => self::USER_QUALITY_OLD,
    ];
    /** 机审模块：基础 */
    const MODULE_BASIC = 'basic';
    /**
     * @var string
     */
    protected $table = 'system_approve_rule';
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

    public static function getAllRuleByQuality($userQuality)
    {
        $rules = [];
        switch ($userQuality) {
            case self::USER_QUALITY_NEW:
                $rules = array_keys(array_collapse(SystemApproveRule::USER_TYPE_NEW_RULE_DEFAULT));
                break;
            case self::USER_QUALITY_OLD:
                $rules = array_keys(array_collapse(SystemApproveRule::USER_TYPE_OLD_RULE_DEFAULT));
                break;
        }

        return $rules;
    }

    public static function updOrAdd($appId, $userType, $rule, $value, $status)
    {
        $data = [];

        if (isset($status) && in_array($rule, self::ALLOWED_CHANGE_STATUS)) {
            $data['status'] = $status;
        }

        if (isset($value)) {
            if (is_array($value)) {
                ArrayHelper::numericToInteger($value);
                $value = json_encode($value);
            }
            if (filter_var($value, FILTER_VALIDATE_INT)) {
                $value = filter_var($value, FILTER_VALIDATE_INT);
            }
            $data['value'] = $value;
        }

        if (!$data) {
            return true;
        }

        return self::updateOrCreate([
            'app_id' => $appId,
            'user_quality' => $userType,
            'rule' => $rule,
        ], $data);
    }

    public static function showTypeClassifyReason($rules)
    {
        $classify = self::changeoverShowTypeClassify();

        $res = array_only($classify, $rules);

        return $res;
    }

    public static function changeoverShowTypeClassify()
    {
        $classify = self::SHOW_TYPE_CLASSIFY;

        $res = [];
        foreach ($classify as $class => $arr) {
            foreach ($arr as $key => $value) {
                $res[$key] = [
                    'class' => self::SHOW_TYPE[$class] ?? '机审规则命中',
                    'reason' => $value,
                ];
            }
        }
        return $res;
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function getRule($appId, $userQuality, $containClose = false)
    {
        $where = [
            'app_id' => $appId,
            'user_quality' => $userQuality,
        ];

        $status = [self::STATUS_NORMAL];
        if ($containClose) {
            array_push($status, self::STATUS_CLOSE);
        }

        $rules = $this->newQuery()
            ->where($where)
            ->whereIn('status', $status)
            ->get();

        /** @var static $rule */
        foreach ($rules as $rule) {
            $rule->value = $rule->parseRule();
        }

        return $rules;
    }

    public function parseRule()
    {
        // 此处为方便 SystemApproveRuleTrait 获取参数做调整，多重类型传入两个参数，其他只传入一个参数
        switch ($this->type) {
            case self::TYPE_STRING:
                return [$this->value];
            case self::TYPE_RANGE:
            case self::TYPE_OPTION:
                return [json_decode($this->value, true)];
            case self::TYPE_MULTIPLE:
                return json_decode($this->value, true);
            default:
                throw new \Exception('system approve rule type incorrect!');
        }
    }

    public function getRuleToView($appId, $userQuality)
    {
        $where = [
            'app_id' => $appId,
            'user_quality' => $userQuality,
        ];

        $rules = $this->newQuery()
            ->where($where)
            ->whereIn('status', [self::STATUS_CLOSE, self::STATUS_NORMAL])
            ->get();

        return $rules;
    }
}
