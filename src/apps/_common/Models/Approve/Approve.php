<?php

namespace Common\Models\Approve;

use Common\Traits\Model\StaticModel;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Approve\Approve
 *
 * @property int $id 审批记录id
 * @property int $type 类型
 * @property int $order_id 订单id
 * @property int $admin_id 审批操作人id
 * @property string $result 审批结果
 * @property string $approve_select 审批选项
 * @property mixed $approve_content 审批选项内容
 * @property string $remark 备注
 * @property int $status 状态  1：正常 -1：被覆盖
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereApproveContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereApproveSelect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $admin_username 审批人 名称
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereAdminUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve orderByCustom($column = null, $direction = 'asc')
 * @property int|null $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Approve\Approve whereMerchantId($value)
 */
class Approve extends Model
{
    use StaticModel;

    protected $table = 'approve';
    protected $guarded = [];

    /** type 预留字段 */
    const TYPE_MANUAL = 1;
    /** 审批结果：通过 */
    const RESULT_PASS = 'pass';
    /** 审批结果：重新提交材料 */
    const RESULT_REPLENISH = 'replenish';
    /** 审批结果：拒绝 */
    const RESULT_REJECTED = 'rejected';
    /** 审批结果 */
    const RESULT = [
        self::RESULT_PASS => '通过',
        self::RESULT_REPLENISH => '重新提交资料',
        self::RESULT_REJECTED => '拒绝',
    ];

    /** 初审 */
    const PROCESS_FIRST_APPROVAL = 'first_approval';
    /** 电审 */
    const PROCESS_CALL_APPROVAL = 'call_approval';
    const PROCESS = [
        self::PROCESS_FIRST_APPROVAL => 'First Approval',
        self::PROCESS_CALL_APPROVAL => 'Call Approval',
    ];

    /** 审批选项：审批通过：人审通过 */
    const SELECT_PASS = 'pass';
    /** 审批选项：重新提交：正面照不清晰 */
    const SELECT_REPLENISH_FRONT_BREEZING = 'replenish_front_breezing';
    /** 审批选项：重新提交：反面照不清晰 */
    const SELECT_REPLENISH_BACK_BREEZING = 'replenish_back_breezing';
    /** 审批选项：重新提交：活体照不规范 */
    const SELECT_REPLENISH_FACE_NONSTANDARD = 'replenish_face_nonstandard';
    /** 审批选项：重新提交：手持照不规范 */
    const SELECT_REPLENISH_HAND_ID_CARD_NONSTANDARD = 'replenish_hand_id_card_nonstandard';
    /** 审批选项：重新提交：身份证已过期 */
    const SELECT_REPLENISH_ID_CARD_OVERDUE = 'replenish_id_card_overdue';
    /** 审批选项：拒绝：基本信息未通过 */
    const SELECT_REJECTED_BASIC_NO_PASS = 'rejected_basic_no_pass';
    /** 审批选项：拒绝：运营商报告未通过 */
    const SELECT_REJECTED_OPERATOR_REPORT_NO_PASS = 'rejected_operator_report_no_pass';
    /** 审批选项：拒绝：通讯录未通过 */
    const SELECT_REJECTED_CONTACTS_NO_PASS = 'rejected_contacts_no_pass';
    /** 审批选项：拒绝：短信记录未通过 */
    const SELECT_REJECTED_SMS_NO_PASS = 'rejected_sms_no_pass';
    /** 审批选项：拒绝：借款信息未通过 */
    const SELECT_REJECTED_LOAN_INFO_NO_PASS = 'rejected_loan_info_no_pass';
    /** 审批选项：拒绝：银行卡未通过 */
    const SELECT_REJECTED_BANKCARD_NO_PASS = 'rejected_bankcard_no_pass';
    /** 审批选项：拒绝：位置信息未通过 */
    const SELECT_REJECTED_POSITION_NO_PASS = 'rejected_position_no_pass';
    /** 审批选项：拒绝：APP应用列表未通过 */
    const SELECT_REJECTED_APP_NO_PASS = 'rejected_app_no_pass';
    /** 审批选项：拒绝：人审风险报告未通过 */
    const SELECT_REJECTED_RISK_REPORTS_NO_PASS = 'risk_reports_no_pass';
    /** 审批选项：拒绝：多头报告未通过 */
    const SELECT_REJECTED_LONG_POSITION_REPORTS_NO_PASS = 'long_position_reports_no_pass';
    /** 审批选项：拒绝：支付宝报告未通过 */
    const SELECT_REJECTED_ALIPAY_REPORT_NO_PASS = 'alipay_report_no_pass';
    /** 审批选项 */
    const SELECT = [
        self::SELECT_PASS => '人审通过',

        self::SELECT_REPLENISH_FRONT_BREEZING => '正面照不清晰',
        self::SELECT_REPLENISH_BACK_BREEZING => '反面照不清晰',
        self::SELECT_REPLENISH_FACE_NONSTANDARD => '活体照不合规',
        self::SELECT_REPLENISH_HAND_ID_CARD_NONSTANDARD => '手持照不合规',
        self::SELECT_REPLENISH_ID_CARD_OVERDUE => '身份证已过期',

        self::SELECT_REJECTED_BASIC_NO_PASS => '基本信息未通过',
        self::SELECT_REJECTED_OPERATOR_REPORT_NO_PASS => '运营商报告未通过',
        self::SELECT_REJECTED_CONTACTS_NO_PASS => '通讯录未通过',
        self::SELECT_REJECTED_SMS_NO_PASS => '短信记录未通过',
        self::SELECT_REJECTED_LOAN_INFO_NO_PASS => '借款信息未通过',
        self::SELECT_REJECTED_BANKCARD_NO_PASS => '银行卡未通过',
        self::SELECT_REJECTED_POSITION_NO_PASS => '位置信息未通过',
        self::SELECT_REJECTED_APP_NO_PASS => 'APP应用列表未通过',
        self::SELECT_REJECTED_RISK_REPORTS_NO_PASS => '人审风险报告未通过',
        self::SELECT_REJECTED_LONG_POSITION_REPORTS_NO_PASS => '多头报告未通过',
        self::SELECT_REJECTED_ALIPAY_REPORT_NO_PASS => '支付宝报告未通过',
    ];
    /**
     * 选项分组
     */
    const SELECT_GROUP = [
        self::RESULT_PASS => [
            self::SELECT_PASS,
        ],
        self::RESULT_REPLENISH => [
            self::SELECT_REPLENISH_FRONT_BREEZING,
            self::SELECT_REPLENISH_BACK_BREEZING,
            self::SELECT_REPLENISH_FACE_NONSTANDARD,
            self::SELECT_REPLENISH_HAND_ID_CARD_NONSTANDARD,
            self::SELECT_REPLENISH_ID_CARD_OVERDUE,
        ],
        self::RESULT_REJECTED => [
            self::SELECT_REJECTED_BASIC_NO_PASS,
            self::SELECT_REJECTED_OPERATOR_REPORT_NO_PASS,
            self::SELECT_REJECTED_CONTACTS_NO_PASS,
            self::SELECT_REJECTED_SMS_NO_PASS,
            self::SELECT_REJECTED_LOAN_INFO_NO_PASS,
            self::SELECT_REJECTED_BANKCARD_NO_PASS,
            self::SELECT_REJECTED_POSITION_NO_PASS,
            self::SELECT_REJECTED_APP_NO_PASS,
            self::SELECT_REJECTED_RISK_REPORTS_NO_PASS,
            self::SELECT_REJECTED_LONG_POSITION_REPORTS_NO_PASS,
            self::SELECT_REJECTED_ALIPAY_REPORT_NO_PASS,
        ],
    ];

    /** 状态：正常 */
    const STATUS_NORMAL = 1;
    /** 状态：被覆盖 */
    const STATUS_COVER = -1;
    /** 状态 */
    const STATUS = [
        self::STATUS_NORMAL => '正常',
        self::STATUS_COVER => '被覆盖',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [
            'time' => [
            ],
            'array' => [
            ],
        ];
    }

    public function getApproveSelectAttribute($value)
    {
        return ArrayHelper::jsonToArray($value) ?? $value;
    }

    public function setApproveSelectAttribute($value)
    {
        $this->attributes['approve_select'] = ArrayHelper::arrayToJson((array)$value);
    }

    /**
     *
     * @param $value
     * @return mixed
     */
    public function getApproveContentAttribute($value)
    {
        return ArrayHelper::jsonToArray($value) ?? $value;
    }

    public function setApproveContentAttribute($value)
    {
        $this->attributes['approve_content'] = ArrayHelper::arrayToJson((array)$value);;
    }

    public static function add($orderId, $result, $approveSelect, $remark)
    {
        $data = [
            'merchant_id' => MerchantHelper::getMerchantId(),
            'type' => self::TYPE_MANUAL,    // 暂时写死
            'order_id' => $orderId,
            'admin_id' => LoginHelper::getAdminId(),
            'admin_username' => LoginHelper::$modelUser['username'] ?? '',
            'result' => $result,
            'approve_select' => $approveSelect,
            'approve_content' => array_values(array_only(self::SELECT, $approveSelect)),
            'remark' => $remark,
            'status' => self::STATUS_NORMAL,
        ];
        return self::create($data);
    }

    /**
     * 判断select是否同一组
     * @param $select
     * @return bool
     */
    public static function selectIsSameGroup($select)
    {
        $isSame = false;
        foreach (self::SELECT_GROUP as $group) {
            if (array_diff($select, $group) == null) {
                $isSame = true;
                break;
            }
        }
        return $isSame;
    }

    public static function coverByOrderId($orderId)
    {
        $where = [
            'order_id' => $orderId,
            'status' => self::STATUS_NORMAL,
        ];
        $update = [
            'status' => self::STATUS_COVER,
        ];
        return static::query()->where($where)->update($update);
    }
}
