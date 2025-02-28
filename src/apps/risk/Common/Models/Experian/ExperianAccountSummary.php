<?php

namespace Risk\Common\Models\Experian;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Experian\ExperianAccountSummary
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string $relate_type 关联类型  third_experian
 * @property string|null $bureau_score BureauScore
 * @property string|null $bureau_score_confid_level BureauScoreConfidLevel
 * @property string|null $caps_last_180_days CAPSLast180Days
 * @property string|null $caps_last_30_days CAPSLast30Days
 * @property string|null $caps_last_7_days CAPSLast7Days
 * @property string|null $caps_last_90_days CAPSLast90Days
 * @property string|null $cad_suit_filed_current_balance CADSuitFiledCurrentBalance
 * @property string|null $credit_account_active CreditAccountActive
 * @property string|null $credit_account_closed CreditAccountClosed
 * @property string|null $credit_account_default CreditAccountDefault
 * @property string|null $credit_account_total CreditAccountTotal
 * @property string|null $outstanding_balance_all OutstandingBalanceAll
 * @property string|null $outstanding_balance_secured OutstandingBalanceSecured
 * @property string|null $outstanding_balance_secured_percentage OutstandingBalanceSecuredPercentage
 * @property string|null $outstanding_balance_un_secured OutstandingBalanceUnSecured
 * @property string|null $outstanding_balance_un_secured_percentage OutstandingBalanceUnSecuredPercentage
 * @property string|null $non_credit_caps_last_180_days NonCreditCAPSLast180Days
 * @property string|null $non_credit_caps_last_30_days NonCreditCAPSLast30Days
 * @property string|null $non_credit_caps_last_7_days NonCreditCAPSLast7Days
 * @property string|null $non_credit_caps_last_90_days NonCreditCAPSLast90Days
 * @property string|null $total_caps_last_180_days TotalCAPSLast180Days
 * @property string|null $total_caps_last_30_days TotalCAPSLast30Days
 * @property string|null $total_caps_last_7_days TotalCAPSLast7Days
 * @property string|null $total_caps_last_90_days TotalCAPSLast90Days
 * @property string|null $exact_match ExactMatch
 * @property string|null $user_message_text UserMessageText
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereBureauScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereBureauScoreConfidLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCadSuitFiledCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCapsLast180Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCapsLast30Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCapsLast7Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCapsLast90Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCreditAccountActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCreditAccountClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCreditAccountDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereCreditAccountTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereExactMatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereNonCreditCapsLast180Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereNonCreditCapsLast30Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereNonCreditCapsLast7Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereNonCreditCapsLast90Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereOutstandingBalanceAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereOutstandingBalanceSecured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereOutstandingBalanceSecuredPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereOutstandingBalanceUnSecured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereOutstandingBalanceUnSecuredPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereRelateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereTotalCapsLast180Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereTotalCapsLast30Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereTotalCapsLast7Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereTotalCapsLast90Days($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountSummary whereUserMessageText($value)
 * @mixin \Eloquent
 */
class ExperianAccountSummary extends RiskBaseModel
{
    /** 关联：third_experian表 */
    const TYPE_THIRD_EXPERIAN = 'third_experian';
    /** 关联：third_report */
    const TYPE_THIRD_REPORT = 'third_report';
    public $table = 'experian_account_summary';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [];
    }
}
