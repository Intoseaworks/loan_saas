<?php

namespace Risk\Common\Models\Experian;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Experian\ExperianAccountDetail
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string $relate_type 关联类型  third_experian
 * @property string|null $detail_no 一份报告下有多份detail
 * @property string|null $identification_number IdentificationNumber
 * @property string|null $subscriber_name SubscriberName
 * @property string|null $account_number AccountNumber
 * @property string|null $portfolio_type PortfolioType
 * @property string|null $account_type AccountType
 * @property string|null $open_date OpenDate
 * @property string|null $credit_limit_amount CreditLimitAmount
 * @property string|null $highest_credit_or_original_loan_amount HighestCreditOrOriginalLoanAmount
 * @property string|null $account_status AccountStatus
 * @property string|null $payment_rating PaymentRating
 * @property string|null $payment_history_profile PaymentHistoryProfile
 * @property string|null $special_comment SpecialComment
 * @property string|null $current_balance CurrentBalance
 * @property string|null $amount_past_due AmountPastDue
 * @property string|null $original_charge_off_amount OriginalChargeOffAmount
 * @property string|null $date_reported DateReported
 * @property string|null $date_of_first_delinquency DateOfFirstDelinquency
 * @property string|null $date_closed DateClosed
 * @property string|null $date_of_last_payment DateOfLastPayment
 * @property string|null $suit_filed_willful_default_written_off_status SuitFiledWillfulDefaultWrittenOffStatus
 * @property string|null $suit_filed_wilful_default SuitFiledWilfulDefault
 * @property string|null $written_off_settled_status WrittenOffSettledStatus
 * @property string|null $value_of_credits_las_month ValueOfCreditsLasMonth
 * @property string|null $type_of_collateral TypeOfCollateral
 * @property string|null $written_off_amt_total WrittenOffAmtTotal
 * @property string|null $written_off_amt_principal WrittenOffAmtPrincipal
 * @property string|null $rate_of_interest RateOfInterest
 * @property string|null $repayment_tenure RepaymentTenure
 * @property string|null $promotional_rate_flag PromotionalRateFlag
 * @property string|null $income Income
 * @property string|null $income_indicator IncomeIndicator
 * @property string|null $income_frequency_indicator IncomeFrequencyIndicator
 * @property string|null $default_status_date DefaultStatusDate
 * @property string|null $litigation_status_date LitigationStatusDate
 * @property string|null $write_off_status_date WriteOffStatusDate
 * @property string|null $date_of_addition DateOfAddition
 * @property string|null $currency_code CurrencyCode
 * @property string|null $subscriber_comments SubscriberComments
 * @property string|null $account_holder_type_code AccountHolderTypeCode
 * @property string|null $cais_account_history_year Year
 * @property string|null $cais_account_history_month Month
 * @property string|null $days_past_due DaysPastDue
 * @property string|null $asset_classification AssetClassification
 * @property string|null $advanced_account_history_year Year
 * @property string|null $advanced_account_history_month Month
 * @property string|null $advanced_account_history_cash_limit CashLimit
 * @property string|null $advanced_account_history_credit_limit_amount CreditLimitAmount
 * @property string|null $advanced_account_history_emi_amount EMIAmount
 * @property string|null $advanced_account_history_current_balance CurrentBalance
 * @property string|null $advanced_account_history_amount_past_due AmountPastDue
 * @property string|null $surname_non_normalized SurnameNonNormalized
 * @property string|null $first_name_non_normalized FirstNameNonNormalized
 * @property string|null $middle_name1_non_normalized MiddleName1NonNormalized
 * @property string|null $middle_name2_non_normalized MiddleName2NonNormalized
 * @property string|null $middle_name3_non_normalized MiddleName3NonNormalized
 * @property string|null $alias Alias
 * @property string|null $gender_code GenderCode
 * @property string|null $income_taxpan IncomeTAXPAN
 * @property string|null $date_of_birth DateOfBirth
 * @property string|null $first_line_of_address_non_normalized FirstLineOfAddressNonNormalized
 * @property string|null $second_line_of_address_non_normalized SecondLineOfAddressNonNormalized
 * @property string|null $third_lin_of_address_non_normalized ThirdLinOfAddressNonNormalized
 * @property string|null $city_non_normalized CityNonNormalized
 * @property string|null $fifth_line_of_address_non_normalized FifthLineOfAddressNonNormalized
 * @property string|null $state_non_normalized StateNonNormalized
 * @property string|null $zip_postal_code_non_normalized ZipPostalCodeNonNormalized
 * @property string|null $country_code_non_normalized CountryCodeNonNormalized
 * @property string|null $address_indicator_non_normalized AddressIndicatorNonNormalized
 * @property string|null $residence_code_non_normalized ResidenceCodeNonNormalized
 * @property string|null $telephone_number TelephoneNumber
 * @property string|null $telephone_type TelephoneType
 * @property string|null $income_tax_pan IncomeTaxPan
 * @property string|null $pan_issue_date PanIssueDate
 * @property string|null $pan_expiration_date PanExpirationDate
 * @property string|null $driver_license_number DriverLicenseNumber
 * @property string|null $driver_license_issue_date DriverLicenseIssueDate
 * @property string|null $driver_license_expiration_date DriverLicenseExpirationDate
 * @property string|null $email_id EmailId
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAccountHolderTypeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAccountStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAddressIndicatorNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryAmountPastDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryCashLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryCreditLimitAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryEmiAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAdvancedAccountHistoryYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAmountPastDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereAssetClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCaisAccountHistoryMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCaisAccountHistoryYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCityNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCountryCodeNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCreditLimitAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateOfAddition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateOfFirstDelinquency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateOfLastPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDateReported($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDaysPastDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDefaultStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDetailNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDriverLicenseExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDriverLicenseIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereDriverLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereEmailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereFifthLineOfAddressNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereFirstLineOfAddressNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereFirstNameNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereGenderCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereHighestCreditOrOriginalLoanAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereIdentificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereIncomeFrequencyIndicator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereIncomeIndicator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereIncomeTaxpan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereLitigationStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereMiddleName1NonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereMiddleName2NonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereMiddleName3NonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereOpenDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereOriginalChargeOffAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePanExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePanIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePaymentHistoryProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePaymentRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePortfolioType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail wherePromotionalRateFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereRateOfInterest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereRelateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereRepaymentTenure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereResidenceCodeNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSecondLineOfAddressNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSpecialComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereStateNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSubscriberComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSubscriberName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSuitFiledWilfulDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSuitFiledWillfulDefaultWrittenOffStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereSurnameNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereTelephoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereTelephoneType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereThirdLinOfAddressNonNormalized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereTypeOfCollateral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereValueOfCreditsLasMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereWriteOffStatusDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereWrittenOffAmtPrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereWrittenOffAmtTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereWrittenOffSettledStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianAccountDetail whereZipPostalCodeNonNormalized($value)
 * @mixin \Eloquent
 */
class ExperianAccountDetail extends RiskBaseModel
{
    /** 关联：third_experian表 */
    const TYPE_THIRD_EXPERIAN = 'third_experian';
    /** 关联：third_report */
    const TYPE_THIRD_REPORT = 'third_report';
    public $table = 'experian_account_detail';
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
