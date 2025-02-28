<?php

namespace Risk\Common\Services\CreditReport;

use Carbon\Carbon;
use Common\Services\BaseService;
use Common\Utils\DingDing\DingHelper;
use Risk\Common\Models\CreditReport\ThirdExperian;
use Risk\Common\Models\Experian\ExperianAccountDetail;
use Risk\Common\Models\Experian\ExperianAccountSummary;
use Risk\Common\Models\Experian\ExperianIndividualInfo;
use Risk\Common\Models\Experian\ExperianRequestInfo;
use Risk\Common\Models\Third\ThirdReport;

class ExperianCreditReportParseServer extends BaseService
{
    /** 关联：third_experian表 */
    const TYPE_THIRD_EXPERIAN = 'third_experian';
    /** 关联：third_report */
    const TYPE_THIRD_REPORT = 'third_report';
    public $date;
    public $dateString;
    public $startOfDayTimeString;
    public $endOfDayTimeString;

    public function __construct(Carbon $date)
    {
        $this->date = $date->copy();
        $this->dateString = $date->toDateString();

        $this->startOfDayTimeString = $date->startOfDay()->toDateTimeString();
        $this->endOfDayTimeString = $date->endOfDay()->toDateTimeString();
    }

    public function parseToDatabase()
    {
        $reports = $this->getReports();

        foreach ($reports as $report) {
            try {
                $this->parseReportAndSave($report);
            } catch (\Exception $e) {
                DingHelper::notice(['third_report_id' => $report->id, 'msg' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()],
                    'Experian报告解析存储报错', DingHelper::AT_SOLIANG);
            }
        }
    }

    protected function getReports()
    {
        $where = [
            'type' => ThirdReport::TYPE_CREDIT_REPORT,
            'channel' => ThirdReport::CHANNEL_EXPERIAN,
            'status' => ThirdReport::STATUS_NORMAL,
        ];

        $reports = ThirdReport::query()
            ->withoutGlobalScope(ThirdReport::$bootScopeMerchant)
            ->where($where)
            ->whereBetween('created_at', [$this->startOfDayTimeString, $this->endOfDayTimeString])
            ->get();

        return $reports;
    }

    protected function parseReportAndSave(ThirdReport $model)
    {
        $reportData = $this->parseJson($model->report_body);
        $requestData = $model->request_info ? $this->parseJson($model->request_info) : null;

        $relateData = [
            'app_id' => $model->app_id,
            'user_id' => $model->user_id,
            'relate_id' => $model->id,
            'relate_type' => self::TYPE_THIRD_REPORT,
        ];

        if ($requestData) {
            // 查询信息
            $this->saveRequestInfo($relateData, $requestData);
        }

        // 个人信息
        $this->saveIndividualInfo($relateData, $reportData);

        // 账户概要
        $this->saveAccountSummary($relateData, $reportData);

        // 账户详情
        $this->saveAccountDetail($relateData, $reportData);
    }

    protected function parseJson($reportBody)
    {
        if (!$report = json_decode($reportBody, true)) {
            throw new \Exception('Experian征信解析存储宽表json格式错误-请检查');
        }

        return $report;
    }

    /**
     * 获取并存储-查询信息
     * @param $relateData
     * @param $requestData
     * @return mixed
     */
    protected function saveRequestInfo($relateData, $requestData)
    {
        $name = array_get($requestData, 'name');
        $nameArr = explode(' ', $name, 2);

        $gender = array_get(['M' => 1, 'F' => 2, 'T' => 3], array_get($requestData, 'gender'));

        $data = [
            'pan' => array_get($requestData, 'pan_card_no'),
            'func_type' => 3,
            'first_name' => $nameArr[0],
            'last_name' => $nameArr[1] ?? $nameArr[0],
            'mobile' => array_get($requestData, 'phone_no'),
            'gender' => $gender,
            'date_of_birth' => array_get($requestData, 'dob'),
            'city' => array_get($requestData, 'city'),
            'state' => array_get($requestData, 'state'),
            'pin_code' => array_get($requestData, 'pincode'),
            'address' => array_get($requestData, 'address'),
//            'office_name' => '',
//            'request_time' => '',
        ];

        $data = $this->formatData($data);

        return ExperianRequestInfo::updateOrCreate($relateData, $data);
    }

    /**
     * 入库数据格式化
     * 主要是对xml解析后返回的空数组进行处理。处理为空字符串
     * @param $arr
     * @param $field
     * @param null $default
     * @return mixed|string
     */
    protected function formatData($arr)
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return json_encode($item);
            }
            return $item;
        }, $arr);
    }

    /**
     * 获取并存储-个人信息
     * @param $relateData
     * @param $reportData
     *
     */
    protected function saveIndividualInfo($relateData, $reportData)
    {
        $creditProfileHeader = array_get($reportData, 'CreditProfileHeader');
        $CAPSApplicationDetails = array_get($reportData, 'CAPS.CAPSApplicationDetails');
        $capsApplicantDetails = array_get($CAPSApplicationDetails, 'CapsApplicantDetails');
        $capsOtherDetails = array_get($CAPSApplicationDetails, 'CapsOtherDetails');
        $capsApplicantAddressDetails = array_get($CAPSApplicationDetails, 'CapsApplicantAddressDetails');

        $data = [
            'username' => array_get($creditProfileHeader, 'Username'), // Username
            'report_date' => array_get($creditProfileHeader, 'ReportDate'), // ReportDate
            'version' => null, // Version
            'report_number' => array_get($CAPSApplicationDetails, 'ReportNumber'), // ReportNumber
            'subscriber_name' => array_get($creditProfileHeader, 'SubscriberName'), // SubscriberName
            'date_of_birth_applicant' => array_get($capsApplicantDetails, 'DateOfBirthApplicant'), // DateOfBirthApplicant
            'driver_license_expiration_date' => array_get($capsApplicantDetails, 'DriverLicenseExpirationDate'), // DriverLicenseExpirationDate
            'driver_license_issue_date' => array_get($capsApplicantDetails, 'DriverLicenseIssueDate'), // DriverLicenseIssueDate
            'driver_license_number' => array_get($capsApplicantDetails, 'DriverLicenseNumber'), // DriverLicenseNumber
            'email_id' => array_get($capsApplicantDetails, 'EmailId'), // EmailId
            'first_name' => array_get($capsApplicantDetails, 'FirstName'), // FirstName
            'gender_code' => array_get($capsApplicantDetails, 'GenderCode'), // GenderCode
            'income_tax_pan' => array_get($capsApplicantDetails, 'IncomeTaxPan'), // IncomeTaxPan
            'last_name' => array_get($capsApplicantDetails, 'LastName'), // LastName
            'mobile_phone_number' => array_get($capsApplicantDetails, 'MobilePhoneNumber'), // MobilePhoneNumber
            'pan_expiration_date' => array_get($capsApplicantDetails, 'PanExpirationDate'), // PanExpirationDate
            'pan_issue_date' => array_get($capsApplicantDetails, 'PanIssueDate'), // PanIssueDate
            'passport_expiration_date' => array_get($capsApplicantDetails, 'PassportExpirationDate'), // PassportExpirationDate
            'passport_issue_date' => array_get($capsApplicantDetails, 'PassportIssueDate'), // PassportIssueDate
            'passport_number' => array_get($capsApplicantDetails, 'PassportNumber'), // PassportNumber
            'ration_card_expiration_date' => array_get($capsApplicantDetails, 'RationCardExpirationDate'), // RationCardExpirationDate
            'ration_card_issue_date' => array_get($capsApplicantDetails, 'RationCardIssueDate'), // RationCardIssueDate
            'ration_card_number' => array_get($capsApplicantDetails, 'RationCardNumber'), // RationCardNumber
            'telephone_type' => array_get($capsApplicantDetails, 'TelephoneType'), // TelephoneType
            'universal_id_expiration_date' => array_get($capsApplicantDetails, 'UniversalIdExpirationDate'), // UniversalIdExpirationDate
            'universal_id_issue_date' => array_get($capsApplicantDetails, 'UniversalIdIssueDate'), // UniversalIdIssueDate
            'universal_id_number' => array_get($capsApplicantDetails, 'UniversalIdNumber'), // UniversalIdNumber
            'voter_id_expiration_date' => array_get($capsApplicantDetails, 'VoterIdExpirationDate'), // VoterIdExpirationDate
            'voter_id_issue_date' => array_get($capsApplicantDetails, 'VoterIdIssueDate'), // VoterIdIssueDate
            'voters_identity_card' => array_get($capsApplicantDetails, 'VotersIdentityCard'), // VotersIdentityCard
            'employment_status' => array_get($capsOtherDetails, 'EmploymentStatus'), // EmploymentStatus
            'income' => array_get($capsOtherDetails, 'Income'), // Income
            'marital_status' => array_get($capsOtherDetails, 'MaritalStatus'), // MaritalStatus
            'number_of_major_credit_card_held' => array_get($capsOtherDetails, 'NumberOfMajorCreditCardHeld'), // NumberOfMajorCreditCardHeld
            'time_with_employer' => array_get($capsOtherDetails, 'TimeWithEmployer'), // TimeWithEmployer
            'city' => array_get($capsApplicantAddressDetails, 'City'), // City
            'state' => array_get($capsApplicantAddressDetails, 'State'), // State
            'pin_code' => array_get($capsApplicantAddressDetails, 'PinCode'), // PinCode
            'country_code' => array_get($capsApplicantAddressDetails, 'CountryCode'), // CountryCode
            'bldg_no_society_name' => array_get($capsApplicantAddressDetails, 'BldgNoSocietyName'), // BldgNoSocietyName
            'flat_no_plot_no_house_no' => array_get($capsApplicantAddressDetails, 'FlatNoPlotNoHouseNo'), // FlatNoPlotNoHouseNo
            'road_no_name_area_locality' => array_get($capsApplicantAddressDetails, 'RoadNoNameAreaLocality'), // RoadNoNameAreaLocality
        ];
        $data = $this->formatData($data);

        ExperianIndividualInfo::updateOrCreate($relateData, $data);
    }

    /**
     * 获取并存储-账户概要
     * @param $relateData
     * @param $reportData
     *
     */
    protected function saveAccountSummary($relateData, $reportData)
    {
        $score = array_get($reportData, 'SCORE');
        $CAPSSummary = array_get($reportData, 'CAPS.CAPSSummary');
        $creditAccount = array_get($reportData, 'CAISAccount.CAISSummary.CreditAccount');
        $totalOutstandingBalance = array_get($reportData, 'CAISAccount.CAISSummary.TotalOutstandingBalance');
        $nonCreditCAPSSummary = array_get($reportData, 'NonCreditCAPS.NonCreditCAPSSummary');
        $totalCAPSSummary = array_get($reportData, 'TotalCAPSSummary');

        $data = [
            'bureau_score' => array_get($score, 'BureauScore'), // BureauScore
            'bureau_score_confid_level' => array_get($score, 'BureauScoreConfidLevel'), // BureauScoreConfidLevel
            'caps_last_180_days' => array_get($CAPSSummary, 'CAPSLast180Days'), // CAPSLast180Days
            'caps_last_30_days' => array_get($CAPSSummary, 'CAPSLast30Days'), // CAPSLast30Days
            'caps_last_7_days' => array_get($CAPSSummary, 'CAPSLast7Days'), // CAPSLast7Days
            'caps_last_90_days' => array_get($CAPSSummary, 'CAPSLast90Days'), // CAPSLast90Days
            'cad_suit_filed_current_balance' => array_get($creditAccount, 'CADSuitFiledCurrentBalance'), // CADSuitFiledCurrentBalance
            'credit_account_active' => array_get($creditAccount, 'CreditAccountActive'), // CreditAccountActive
            'credit_account_closed' => array_get($creditAccount, 'CreditAccountClosed'), // CreditAccountClosed
            'credit_account_default' => array_get($creditAccount, 'CreditAccountDefault'), // CreditAccountDefault
            'credit_account_total' => array_get($creditAccount, 'CreditAccountTotal'), // CreditAccountTotal
            'outstanding_balance_all' => array_get($totalOutstandingBalance, 'OutstandingBalanceAll'), // OutstandingBalanceAll
            'outstanding_balance_secured' => array_get($totalOutstandingBalance, 'OutstandingBalanceSecured'), // OutstandingBalanceSecured
            'outstanding_balance_secured_percentage' => array_get($totalOutstandingBalance, 'OutstandingBalanceSecuredPercentage'), // OutstandingBalanceSecuredPercentage
            'outstanding_balance_un_secured' => array_get($totalOutstandingBalance, 'OutstandingBalanceUnSecured'), // OutstandingBalanceUnSecured
            'outstanding_balance_un_secured_percentage' => array_get($totalOutstandingBalance, 'OutstandingBalanceUnSecuredPercentage'), // OutstandingBalanceUnSecuredPercentage
            'non_credit_caps_last_180_days' => array_get($nonCreditCAPSSummary, 'NonCreditCAPSLast180Days'), // NonCreditCAPSLast180Days
            'non_credit_caps_last_30_days' => array_get($nonCreditCAPSSummary, 'NonCreditCAPSLast30Days'), // NonCreditCAPSLast30Days
            'non_credit_caps_last_7_days' => array_get($nonCreditCAPSSummary, 'NonCreditCAPSLast7Days'), // NonCreditCAPSLast7Days
            'non_credit_caps_last_90_days' => array_get($nonCreditCAPSSummary, 'NonCreditCAPSLast90Days'), // NonCreditCAPSLast90Days
            'total_caps_last_180_days' => array_get($totalCAPSSummary, 'TotalCAPSLast180Days'), // TotalCAPSLast180Days
            'total_caps_last_30_days' => array_get($totalCAPSSummary, 'TotalCAPSLast30Days'), // TotalCAPSLast30Days
            'total_caps_last_7_days' => array_get($totalCAPSSummary, 'TotalCAPSLast7Days'), // TotalCAPSLast7Days
            'total_caps_last_90_days' => array_get($totalCAPSSummary, 'TotalCAPSLast90Days'), // TotalCAPSLast90Days
            'exact_match' => array_get($reportData, 'MatchResult.ExactMatch'), // ExactMatch
            'user_message_text' => array_get($reportData, 'UserMessage.UserMessageText'), // UserMessageText
        ];

        $data = $this->formatData($data);

        ExperianAccountSummary::updateOrCreate($relateData, $data);
    }

    /**
     * 获取并存储-账户详情
     * @param $relateData
     * @param $reportData
     * @return bool
     */
    protected function saveAccountDetail($relateData, $reportData)
    {
        $CAISAccountDETAILs = array_get($reportData, 'CAISAccount.CAISAccountDETAILs');

        if (!$CAISAccountDETAILs) {
            return false;
        }

        $detailNo = 1;
        foreach ($CAISAccountDETAILs as $detail) {
            $relateData['detail_no'] = $detailNo;

            $CAISAccountHistory = array_get($detail, 'CAISAccountHistory');
            $advancedAccountHistory = array_get($detail, 'AdvancedAccountHistory');
            $CAISHolderDetails = array_get($detail, 'CAISHolderDetails');
            $CAISHolderAddressDetails = array_get($detail, 'CAISHolderAddressDetails');
            $CAISHolderPhoneDetails = array_get($detail, 'CAISHolderPhoneDetails');
            $CAISHolderIDDetails = array_get($detail, 'CAISHolderIDDetails');

            $data = [
                'identification_number' => array_get($detail, 'IdentificationNumber'), // IdentificationNumber
                'subscriber_name' => array_get($detail, 'SubscriberName'), // SubscriberName
                'account_number' => array_get($detail, 'AccountNumber'), // AccountNumber
                'portfolio_type' => array_get($detail, 'PortfolioType'), // PortfolioType
                'account_type' => array_get($detail, 'AccountType'), // AccountType
                'open_date' => array_get($detail, 'OpenDate'), // OpenDate
                'credit_limit_amount' => array_get($detail, 'CreditLimitAmount'), // CreditLimitAmount
                'highest_credit_or_original_loan_amount' => array_get($detail, 'HighestCreditOrOriginalLoanAmount'), // HighestCreditOrOriginalLoanAmount
                'account_status' => array_get($detail, 'AccountStatus'), // AccountStatus
                'payment_rating' => array_get($detail, 'PaymentRating'), // PaymentRating
                'payment_history_profile' => array_get($detail, 'PaymentHistoryProfile'), // PaymentHistoryProfile
                'special_comment' => array_get($detail, 'SpecialComment'), // SpecialComment
                'current_balance' => array_get($detail, 'CurrentBalance'), // CurrentBalance
                'amount_past_due' => array_get($detail, 'AmountPastDue'), // AmountPastDue
                'original_charge_off_amount' => array_get($detail, 'OriginalChargeOffAmount'), // OriginalChargeOffAmount
                'date_reported' => array_get($detail, 'DateReported'), // DateReported
                'date_of_first_delinquency' => array_get($detail, 'DateOfFirstDelinquency'), // DateOfFirstDelinquency
                'date_closed' => array_get($detail, 'DateClosed'), // DateClosed
                'date_of_last_payment' => array_get($detail, 'DateOfLastPayment'), // DateOfLastPayment
                'suit_filed_willful_default_written_off_status' => array_get($detail, 'SuitFiledWillfulDefaultWrittenOffStatus'), // SuitFiledWillfulDefaultWrittenOffStatus
                'suit_filed_wilful_default' => array_get($detail, 'SuitFiledWilfulDefault'), // SuitFiledWilfulDefault
                'written_off_settled_status' => array_get($detail, 'WrittenOffSettledStatus'), // WrittenOffSettledStatus
                'value_of_credits_las_month' => array_get($detail, 'ValueOfCreditsLasMonth'), // ValueOfCreditsLasMonth
                'type_of_collateral' => array_get($detail, 'TypeOfCollateral'), // TypeOfCollateral
                'written_off_amt_total' => array_get($detail, 'WrittenOffAmtTotal'), // WrittenOffAmtTotal
                'written_off_amt_principal' => array_get($detail, 'WrittenOffAmtPrincipal'), // WrittenOffAmtPrincipal
                'rate_of_interest' => array_get($detail, 'RateOfInterest'), // RateOfInterest
                'repayment_tenure' => array_get($detail, 'RepaymentTenure'), // RepaymentTenure
                'promotional_rate_flag' => array_get($detail, 'PromotionalRateFlag'), // PromotionalRateFlag
                'income' => array_get($detail, 'Income'), // Income
                'income_indicator' => array_get($detail, 'IncomeIndicator'), // IncomeIndicator
                'income_frequency_indicator' => array_get($detail, 'IncomeFrequencyIndicator'), // IncomeFrequencyIndicator
                'default_status_date' => array_get($detail, 'DefaultStatusDate'), // DefaultStatusDate
                'litigation_status_date' => array_get($detail, 'LitigationStatusDate'), // LitigationStatusDate
                'write_off_status_date' => array_get($detail, 'WriteOffStatusDate'), // WriteOffStatusDate
                'date_of_addition' => array_get($detail, 'DateOfAddition'), // DateOfAddition
                'currency_code' => array_get($detail, 'CurrencyCode'), // CurrencyCode
                'subscriber_comments' => array_get($detail, 'SubscriberComments'), // SubscriberComments
                'account_holder_type_code' => array_get($detail, 'AccountHolderTypeCode'), // AccountHolderTypeCode
                'cais_account_history_year' => array_get($CAISAccountHistory, 'Year'), // Year
                'cais_account_history_month' => array_get($CAISAccountHistory, 'Month'), // Month
                'days_past_due' => array_get($CAISAccountHistory, 'DaysPastDue'), // DaysPastDue
                'asset_classification' => array_get($CAISAccountHistory, 'AssetClassification'), // AssetClassification
                'advanced_account_history_year' => array_get($advancedAccountHistory, 'Year'), // Year
                'advanced_account_history_month' => array_get($advancedAccountHistory, 'Month'), // Month
                'advanced_account_history_cash_limit' => array_get($advancedAccountHistory, 'CashLimit'), // CashLimit
                'advanced_account_history_credit_limit_amount' => array_get($advancedAccountHistory, 'CreditLimitAmount'), // CreditLimitAmount
                'advanced_account_history_emi_amount' => array_get($advancedAccountHistory, 'EMIAmount'), // EMIAmount
                'advanced_account_history_current_balance' => array_get($advancedAccountHistory, 'CurrentBalance'), // CurrentBalance
                'advanced_account_history_amount_past_due' => array_get($advancedAccountHistory, 'AmountPastDue'), // AmountPastDue
                'surname_non_normalized' => array_get($CAISHolderDetails, 'SurnameNonNormalized'), // SurnameNonNormalized
                'first_name_non_normalized' => array_get($CAISHolderDetails, 'FirstNameNonNormalized'), // FirstNameNonNormalized
                'middle_name1_non_normalized' => array_get($CAISHolderDetails, 'MiddleName1NonNormalized'), // MiddleName1NonNormalized
                'middle_name2_non_normalized' => array_get($CAISHolderDetails, 'MiddleName2NonNormalized'), // MiddleName2NonNormalized
                'middle_name3_non_normalized' => array_get($CAISHolderDetails, 'MiddleName3NonNormalized'), // MiddleName3NonNormalized
                'alias' => array_get($CAISHolderDetails, 'Alias'), // Alias
                'gender_code' => array_get($CAISHolderDetails, 'GenderCode'), // GenderCode
                'income_taxpan' => array_get($CAISHolderDetails, 'IncomeTAXPAN'), // IncomeTAXPAN
                'date_of_birth' => array_get($CAISHolderDetails, 'DateOfBirth'), // DateOfBirth
                'first_line_of_address_non_normalized' => array_get($CAISHolderAddressDetails, 'FirstLineOfAddressNonNormalized'), // FirstLineOfAddressNonNormalized
                'second_line_of_address_non_normalized' => array_get($CAISHolderAddressDetails, 'SecondLineOfAddressNonNormalized'), // SecondLineOfAddressNonNormalized
                'third_lin_of_address_non_normalized' => array_get($CAISHolderAddressDetails, 'ThirdLinOfAddressNonNormalized'), // ThirdLinOfAddressNonNormalized
                'city_non_normalized' => array_get($CAISHolderAddressDetails, 'CityNonNormalized'), // CityNonNormalized
                'fifth_line_of_address_non_normalized' => array_get($CAISHolderAddressDetails, 'FifthLineOfAddressNonNormalized'), // FifthLineOfAddressNonNormalized
                'state_non_normalized' => array_get($CAISHolderAddressDetails, 'StateNonNormalized'), // StateNonNormalized
                'zip_postal_code_non_normalized' => array_get($CAISHolderAddressDetails, 'ZipPostalCodeNonNormalized'), // ZipPostalCodeNonNormalized
                'country_code_non_normalized' => array_get($CAISHolderAddressDetails, 'CountryCodeNonNormalized'), // CountryCodeNonNormalized
                'address_indicator_non_normalized' => array_get($CAISHolderAddressDetails, 'AddressIndicatorNonNormalized'), // AddressIndicatorNonNormalized
                'residence_code_non_normalized' => array_get($CAISHolderAddressDetails, 'ResidenceCodeNonNormalized'), // ResidenceCodeNonNormalized
                'telephone_number' => array_get($CAISHolderPhoneDetails, 'TelephoneNumber'), // TelephoneNumber
                'telephone_type' => array_get($CAISHolderPhoneDetails, 'TelephoneType'), // TelephoneType
                'income_tax_pan' => array_get($CAISHolderIDDetails, 'IncomeTaxPan'), // IncomeTaxPan
                'pan_issue_date' => array_get($CAISHolderIDDetails, 'PanIssueDate'), // PanIssueDate
                'pan_expiration_date' => array_get($CAISHolderIDDetails, 'PanExpirationDate'), // PanExpirationDate
                'driver_license_number' => array_get($CAISHolderIDDetails, 'DriverLicenseNumber'), // DriverLicenseNumber
                'driver_license_issue_date' => array_get($CAISHolderIDDetails, 'DriverLicenseIssueDate'), // DriverLicenseIssueDate
                'driver_license_expiration_date' => array_get($CAISHolderIDDetails, 'DriverLicenseExpirationDate'), // DriverLicenseExpirationDate
                'email_id' => array_get($CAISHolderIDDetails, 'EmailId'), // EmailId
            ];

            $data = $this->formatData($data);

            ExperianAccountDetail::updateOrCreate($relateData, $data);
            $detailNo++;
        }
    }

    /**
     * ThirdExperian 跑批表解析
     * @param $batchNo
     */
    public function parseBatchReport($batchNo)
    {
        $reports = ThirdExperian::query()
//            ->whereBetween('created_at', [$this->startOfDayTimeString, $this->endOfDayTimeString])
            ->where('batch_no', $batchNo)
            ->get();

        foreach ($reports as $report) {
            try {
                if (!$report->report_body) {
                    continue;
                }

                $reportData = $this->parseJson($report->report_body);
                $requestData = $this->parseJson($report->request_info);

                $relateData = [
                    'user_id' => 0,
                    'relate_id' => $report->id,
                    'relate_type' => self::TYPE_THIRD_EXPERIAN,
                ];

                // 查询信息
                $this->saveRequestInfo($relateData, $requestData);

                // 个人信息
                $this->saveIndividualInfo($relateData, $reportData);

                // 账户概要
                $this->saveAccountSummary($relateData, $reportData);

                // 账户详情
                $this->saveAccountDetail($relateData, $reportData);
            } catch (\Exception $e) {
                DingHelper::notice(['third_report_id' => $report->id, 'msg' => $e->getMessage(), 'file' => $e->getFile() . ':' . $e->getLine()],
                    '跑批Experian报告解析存储报错', DingHelper::AT_SOLIANG);
            }
        }
    }

    protected function dateFormat($arr, $field)
    {
        $date = array_get($arr, $field);
        if (is_null($date)) {
            return null;
        }

        $time = strtotime($date);

        return $time ? date('Y-m-d', $time) : '';
    }

}
