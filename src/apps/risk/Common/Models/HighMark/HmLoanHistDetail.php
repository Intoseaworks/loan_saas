<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmLoanHistDetail
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $loan_no loan_no
 * @property string|null $acct_number ACCT-NUMBER
 * @property string|null $credit_guarantor CREDIT-GUARANTOR
 * @property string|null $acct_type ACCT-TYPE
 * @property string|null $date_reported DATE-REPORTED
 * @property string|null $ownership_ind OWNERSHIP-IND
 * @property string|null $account_status ACCOUNT-STATUS
 * @property string|null $disbursed_amt DISBURSED-AMT
 * @property string|null $disbursed_dt DISBURSED-DT
 * @property string|null $last_payment_date LAST-PAYMENT-DATE
 * @property string|null $closed_date CLOSED-DATE
 * @property string|null $installment_amt INSTALLMENT-AMT
 * @property string|null $installment_freq INSTALLMENT-freq
 * @property string|null $overdue_amt OVERDUE-AMT
 * @property string|null $write_off_amt WRITE-OFF-AMT
 * @property string|null $current_bal CURRENT-BAL
 * @property string|null $combined_payment_history COMBINED-PAYMENT-HISTORY
 * @property string|null $matched_type MATCHED-TYPE
 * @property string|null $linked_accounts LINKED-ACCOUNTS
 * @property string|null $security_details SECURITY-DETAILS
 * @property string|null $interest_rate INTEREST-RATE
 * @property string|null $actual_payment ACTUAL-PAYMENT
 * @property string|null $principal_write_off_amt PRINCIPAL-WRITE-OFF-AMT
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereAccountStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereAcctNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereAcctType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereActualPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereClosedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereCombinedPaymentHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereCreditGuarantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereCurrentBal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereDateReported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereDisbursedAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereDisbursedDt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereInstallmentAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereInstallmentFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereInterestRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereLastPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereLinkedAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereLoanNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereMatchedType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereOverdueAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereOwnershipInd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail wherePrincipalWriteOffAmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereSecurityDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmLoanHistDetail whereWriteOffAmt($value)
 * @mixin \Eloquent
 */
class HmLoanHistDetail extends RiskBaseModel
{
    public $table = 'hm_loan_hist_detail';
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
