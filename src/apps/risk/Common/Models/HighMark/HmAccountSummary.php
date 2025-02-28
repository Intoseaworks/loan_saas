<?php

namespace Risk\Common\Models\HighMark;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\HighMark\HmAccountSummary
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string|null $report_id REPORT-ID
 * @property string|null $inquries_in_last_six_months INQURIES-IN-LAST-SIX-MONTHS
 * @property string|null $length_of_credit_history_year LENGTH-OF-CREDIT-HISTORY-YEAR
 * @property string|null $length_of_credit_history_month LENGTH-OF-CREDIT-HISTORY-MONTH
 * @property string|null $average_account_age_year AVERAGE-ACCOUNT-AGE-YEAR
 * @property string|null $average_account_age_month AVERAGE-ACCOUNT-AGE-MONTH
 * @property string|null $new_accounts_in_last_six_months NEW-ACCOUNTS-IN-LAST-SIX-MONTHS
 * @property string|null $new_delinq_account_in_last_six_months NEW-DELINQ-ACCOUNT-IN-LAST-SIX-MONTHS
 * @property string|null $primary_number_of_accounts PRIMARY-NUMBER-OF-ACCOUNTS
 * @property string|null $rimary_active_number_of_accounts RIMARY-ACTIVE-NUMBER-OF-ACCOUNTS
 * @property string|null $primary_overdue_number_of_accounts PRIMARY-OVERDUE-NUMBER-OF-ACCOUNTS
 * @property string|null $primary_secured_number_of_accounts PRIMARY-SECURED-NUMBER-OF-ACCOUNTS
 * @property string|null $primary_unsecured_number_of_accounts PRIMARY-UNSECURED-NUMBER-OF-ACCOUNTS
 * @property string|null $primary_untagged_number_of_accounts PRIMARY-UNTAGGED-NUMBER-OF-ACCOUNTS
 * @property string|null $primary_current_balance PRIMARY-CURRENT-BALANCE
 * @property string|null $primary_sanctioned_amount PRIMARY-SANCTIONED-AMOUNT
 * @property string|null $rimary_disbursed_amount RIMARY-DISBURSED-AMOUNT
 * @property string|null $secondary_number_of_accounts SECONDARY-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_active_number_of_accounts SECONDARY-ACTIVE-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_overdue_number_of_accounts SECONDARY-OVERDUE-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_secured_number_of_accounts SECONDARY-SECURED-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_unsecured_number_of_accounts SECONDARY-UNSECURED-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_untagged_number_of_accounts SECONDARY-UNTAGGED-NUMBER-OF-ACCOUNTS
 * @property string|null $secondary_current_balance SECONDARY-CURRENT-BALANCE
 * @property string|null $secondary_sanctioned_amount SECONDARY-SANCTIONED-AMOUNT
 * @property string|null $secondary_disbursed_amount SECONDARY-DISBURSED-AMOUNT
 * @property string $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereAverageAccountAgeMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereAverageAccountAgeYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereInquriesInLastSixMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereLengthOfCreditHistoryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereLengthOfCreditHistoryYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereNewAccountsInLastSixMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereNewDelinqAccountInLastSixMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimaryCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimaryNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimaryOverdueNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimarySanctionedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimarySecuredNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimaryUnsecuredNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary wherePrimaryUntaggedNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereRimaryActiveNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereRimaryDisbursedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryActiveNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryDisbursedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryOverdueNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondarySanctionedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondarySecuredNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryUnsecuredNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereSecondaryUntaggedNumberOfAccounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HmAccountSummary whereUserId($value)
 * @mixin \Eloquent
 */
class HmAccountSummary extends RiskBaseModel
{
    public $table = 'hm_account_summary';
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
