<?php

namespace Risk\Common\Services\CreditReport;

use Carbon\Carbon;
use Common\Services\BaseService;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\HighMark\HmAccountSummary;
use Risk\Common\Models\HighMark\HmEmploymentDetail;
use Risk\Common\Models\HighMark\HmIndividualInfo;
use Risk\Common\Models\HighMark\HmInfoVariation;
use Risk\Common\Models\HighMark\HmInquriyHistDetail;
use Risk\Common\Models\HighMark\HmLoanHistDetail;
use Risk\Common\Models\Third\ThirdReport;

class HmCreditReportParseServer extends BaseService
{
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
                    'HM报告解析存储报错', DingHelper::AT_SOLIANG);
            }
        }
    }

    protected function getReports()
    {
        $where = [
            'type' => ThirdReport::TYPE_CREDIT_REPORT,
            'channel' => ThirdReport::CHANNEL_HIGH_MARK,
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
        $reportData = $this->parseReport($model);

        $relateData = [
            'app_id' => $model->app_id,
            'user_id' => $model->user_id,
            'relate_id' => $model->id,
            'report_id' => array_get($reportData, 'HEADER.REPORT-ID', ''),
        ];

        // 个人信息
        $this->saveIndividualInfo($relateData, $reportData);

        // 被查询历史
        $this->saveInquriyHistDetail($relateData, $reportData);

        // 贷款历史
        $this->saveLoanHistDetail($relateData, $reportData);

        // 个人信息变化记录
        $this->saveInfoVariation($relateData, $reportData);

        // 职业信息
        $this->saveEmploymentDetail($relateData, $reportData);

        // 账户信息
        $this->saveAccountSummary($relateData, $reportData);
    }

    protected function parseReport(ThirdReport $model)
    {
        if (!$report = StringHelper::xmlParser($model->report_body)) {
            throw new \Exception('HM征信报告解析存储宽表xml格式错误-请检查');
        }

        return $report;
    }

    /**
     * 获取并存储-个人信息
     * @param $relateData
     * @param $reportData
     * @return mixed
     */
    protected function saveIndividualInfo($relateData, $reportData)
    {
        $header = array_get($reportData, 'HEADER');
        $request = array_get($reportData, 'REQUEST');
        $employmentDetail = array_get($reportData, 'EMPLOYMENT-DETAILS.EMPLOYMENT-DETAIL');
        $score = array_get($reportData, 'SCORES.SCORE');

        $data = [
            'date_of_request' => $this->dateFormat($header, 'DATE-OF-REQUEST'), // DATE-OF-REQUEST
            'date_of_issue' => $this->dateFormat($header, 'DATE-OF-ISSUE'), // DATE-OF-ISSUE
            'batch_id' => array_get($header, 'BATCH-ID'), // BATCH-ID
            'status' => array_get($header, 'STATUS'), // STATUS
            'name' => array_get($request, 'NAME'), // NAME
            'father' => array_get($request, 'FATHER'), // FATHER
            'dob' => $this->dateFormat($request, 'DOB'), // DOB
            'pan' => array_get($request, 'PAN'), // PAN
            'email_1' => array_get($request, 'EMAIL-1'), // EMAIL-1
            'address_1' => array_get($request, 'ADDRESS-1'), // ADDRESS-1
            'address_2' => array_get($request, 'ADDRESS-2'), // ADDRESS-2
            'address_3' => array_get($request, 'ADDRESS-3'), // ADDRESS-3
            'phone_1' => array_get($request, 'PHONE-1'), // PHONE-1
            'phone_2' => array_get($request, 'PHONE-2'), // PHONE-2
            'phone_3' => array_get($request, 'PHONE-3'), // PHONE-3
            'occupation' => array_get($employmentDetail, 'OCCUPATION'), // OCCUPATION
            'income_frequency' => array_get($employmentDetail, 'INCOME-FREQUENCY'), // INCOME-FREQUENCY
            'income_indicator' => array_get($employmentDetail, 'INCOME-INDICATOR'), // INCOME-INDICATOR
            'score_type' => array_get($score, 'SCORE-TYPE'), // SCORE-TYPE
            'score_value' => array_get($score, 'SCORE-VALUE'), // SCORE-VALUE
            'score_comments' => array_get($score, 'SCORE-COMMENTS'), // SCORE-COMMENTS
        ];

        $data = $this->formatData($data);

        return HmIndividualInfo::updateOrCreate($relateData, $data);
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
                return empty($item) ? '' : json_encode($item);
            }
            return $item;
        }, $arr);
    }

    /**
     * 获取并存储-被查询历史
     * @param $relateData
     * @param $reportData
     *
     */
    protected function saveInquriyHistDetail($relateData, $reportData)
    {
        $inquiryHistory = array_get($reportData, 'INQUIRY-HISTORY.HISTORY');

        if (!$inquiryHistory) {
            return false;
        }

        if (ArrayHelper::isAssocArray($inquiryHistory)) {
            $inquiryHistory = [$inquiryHistory];
        }

        $inquryNo = 1;
        foreach ($inquiryHistory as $item) {
            $relateData['inqury_no'] = $inquryNo;
            $data = [
                'member_name' => array_get($item, 'MEMBER-NAME'), // MEMBER-NAME
                'inquiry_date' => $this->dateFormat($item, 'INQUIRY-DATE'), // INQUIRY-DATE
                'purpose' => array_get($item, 'PURPOSE'), // PURPOSE
                'ownership_type' => array_get($item, 'OWNERSHIP-TYPE'), // OWNERSHIP-TYPE
                'amount' => array_get($item, 'AMOUNT'), // AMOUNT
                'remark' => array_get($item, 'REMARK'), // REMARK
            ];

            $data = $this->formatData($data);

            HmInquriyHistDetail::updateOrCreate($relateData, $data);
            $inquryNo++;
        }
    }

    /**
     * 获取并存储-贷款历史
     * @param $relateData
     * @param $reportData
     *
     */
    protected function saveLoanHistDetail($relateData, $reportData)
    {
        $response = array_get($reportData, 'RESPONSES.RESPONSE');
        if (isset($response['LOAN-DETAILS'])) {
            $loanDetails = $response;
        } else {
            $loanDetails = array_pluck($response, 'LOAN-DETAILS');
        }
        if (!$loanDetails) {
            return false;
        }

        $inquryNo = 1;
        foreach ($loanDetails as $item) {
            $relateData['loan_no'] = $inquryNo;

            // INSTALLMENT-AMT 字段不存在，两字段全置null。 值不存在/ ，Freq置null
            $INSTALLMENT_AMT = array_get($item, 'INSTALLMENT-AMT');
            $installmentAmtArr = isset($INSTALLMENT_AMT) ? explode('/', $INSTALLMENT_AMT, 2) : [];
            $installmentAmt = array_first($installmentAmtArr);
            $installmentFreq = $installmentAmtArr[1] ?? null;

            $data = [
                'acct_number' => array_get($item, 'ACCT-NUMBER'), // ACCT-NUMBER
                'credit_guarantor' => array_get($item, 'CREDIT-GUARANTOR'), // CREDIT-GUARANTOR
                'acct_type' => array_get($item, 'ACCT-TYPE'), // ACCT-TYPE
                'date_reported' => $this->dateFormat($item, 'DATE-REPORTED'), // DATE-REPORTED
                'ownership_ind' => array_get($item, 'OWNERSHIP-IND'), // OWNERSHIP-IND
                'account_status' => array_get($item, 'ACCOUNT-STATUS'), // ACCOUNT-STATUS
                'disbursed_amt' => array_get($item, 'DISBURSED-AMT'), // DISBURSED-AMT
                'disbursed_dt' => $this->dateFormat($item, 'DISBURSED-DT'), // DISBURSED-DT
                'last_payment_date' => $this->dateFormat($item, 'LAST-PAYMENT-DATE'), // LAST-PAYMENT-DATE
                'closed_date' => $this->dateFormat($item, 'CLOSED-DATE'), // CLOSED-DATE
                'installment_amt' => $installmentAmt, // INSTALLMENT-AMT
                'installment_freq' => $installmentFreq, // INSTALLMENT-freq 截取 INSTALLMENT-AMT 斜杠之后的内容存储
                'overdue_amt' => array_get($item, 'OVERDUE-AMT'), // OVERDUE-AMT
                'write_off_amt' => array_get($item, 'WRITE-OFF-AMT'), // WRITE-OFF-AMT
                'current_bal' => array_get($item, 'CURRENT-BAL'), // CURRENT-BAL
                'combined_payment_history' => array_get($item, 'COMBINED-PAYMENT-HISTORY'), // COMBINED-PAYMENT-HISTORY
                'matched_type' => array_get($item, 'MATCHED-TYPE'), // MATCHED-TYPE
                'linked_accounts' => array_get($item, 'LINKED-ACCOUNTS'), // LINKED-ACCOUNTS
                'security_details' => array_get($item, 'SECURITY-DETAILS'), // SECURITY-DETAILS
                'interest_rate' => array_get($item, 'INTEREST-RATE'), // INTEREST-RATE
                'actual_payment' => array_get($item, 'ACTUAL-PAYMENT'), // ACTUAL-PAYMENT
                'principal_write_off_amt' => array_get($item, 'PRINCIPAL-WRITE-OFF-AMT'), // PRINCIPAL-WRITE-OFF-AMT
            ];

            $data = $this->formatData($data);

            HmLoanHistDetail::updateOrCreate($relateData, $data);
            $inquryNo++;
        }
    }

    /**
     * 获取并存储-个人信息变化记录
     * @param $relateData
     * @param $reportData
     * @throws \Throwable
     */
    protected function saveInfoVariation($relateData, $reportData)
    {
        $personalInfoVariation = array_get($reportData, 'PERSONAL-INFO-VARIATION');
        $appId = $relateData['app_id'];
        $userId = $relateData['user_id'];
        $relateId = $relateData['relate_id'];
        $reportId = $relateData['report_id'];

        if (!$personalInfoVariation) {
            return false;
        }

        foreach ($personalInfoVariation as $key => $info) {
            $insertData = [];
            $variation = array_get($info, 'VARIATION');
            if (empty($variation)) {
                continue;
            }
            if (ArrayHelper::isAssocArray($variation)) {
                $variation = [$variation];
            }
            foreach ($variation as $item) {
                $data = [
                    'app_id' => $appId,
                    'user_id' => $userId,
                    'relate_id' => $relateId,
                    'report_id' => $reportId,
                    'key' => $key,
                    'value' => array_get($item, 'VALUE'),
                    'reported_date' => $this->dateFormat($item, 'REPORTED-DATE'),
                ];

                $insertData[] = $this->formatData($data);
            }

            DB::connection((new HmInfoVariation())->getConnectionName())->transaction(function () use ($appId, $userId, $relateId, $key, $insertData) {
                HmInfoVariation::clearByKey($appId, $userId, $relateId, $key);
                HmInfoVariation::insert($insertData);
            });
        }
    }

    /**
     * 获取并存储-职业信息
     * @param $relateData
     * @param $reportData
     * @return bool
     */
    protected function saveEmploymentDetail($relateData, $reportData)
    {
        $employmentDetail = array_get($reportData, 'EMPLOYMENT-DETAILS.EMPLOYMENT-DETAIL');

        if (!$employmentDetail) {
            return false;
        }

        $data = [
            'acct_type' => array_get($employmentDetail, 'ACCT-TYPE'), // ACCT-TYPE
            'date_reported' => $this->dateFormat($employmentDetail, 'DATE-REPORTED'), // DATE-REPORTED
            'occupation' => array_get($employmentDetail, 'OCCUPATION'), // OCCUPATION
            'income' => array_get($employmentDetail, 'INCOME'), // INCOME
            'income_frequency' => array_get($employmentDetail, 'INCOME-FREQUENCY'), // INCOME-FREQUENCY
            'income_indicator' => array_get($employmentDetail, 'INCOME-INDICATOR'), // INCOME-INDICATOR
        ];

        $data = $this->formatData($data);

        return HmEmploymentDetail::updateOrCreate($relateData, $data);
    }

    /**
     * 获取并存储-账户信息
     */
    protected function saveAccountSummary($relateData, $reportData)
    {
        $derivedAttributes = array_get($reportData, 'ACCOUNTS-SUMMARY.DERIVED-ATTRIBUTES');
        $primaryAccountsSummary = array_get($reportData, 'ACCOUNTS-SUMMARY.PRIMARY-ACCOUNTS-SUMMARY');
        $secondaryAccountsSummary = array_get($reportData, 'ACCOUNTS-SUMMARY.SECONDARY-ACCOUNTS-SUMMARY');

        $data = [
            'inquries_in_last_six_months' => array_get($derivedAttributes, 'INQURIES-IN-LAST-SIX-MONTHS'), // INQURIES-IN-LAST-SIX-MONTHS
            'length_of_credit_history_year' => array_get($derivedAttributes, 'LENGTH-OF-CREDIT-HISTORY-YEAR'), // LENGTH-OF-CREDIT-HISTORY-YEAR
            'length_of_credit_history_month' => array_get($derivedAttributes, 'LENGTH-OF-CREDIT-HISTORY-MONTH'), // LENGTH-OF-CREDIT-HISTORY-MONTH
            'average_account_age_year' => array_get($derivedAttributes, 'AVERAGE-ACCOUNT-AGE-YEAR'), // AVERAGE-ACCOUNT-AGE-YEAR
            'average_account_age_month' => array_get($derivedAttributes, 'AVERAGE-ACCOUNT-AGE-MONTH'), // AVERAGE-ACCOUNT-AGE-MONTH
            'new_accounts_in_last_six_months' => array_get($derivedAttributes, 'NEW-ACCOUNTS-IN-LAST-SIX-MONTHS'), // NEW-ACCOUNTS-IN-LAST-SIX-MONTHS
            'new_delinq_account_in_last_six_months' => array_get($derivedAttributes, 'NEW-DELINQ-ACCOUNT-IN-LAST-SIX-MONTHS'), // NEW-DELINQ-ACCOUNT-IN-LAST-SIX-MONTHS

            'primary_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-NUMBER-OF-ACCOUNTS'), // PRIMARY-NUMBER-OF-ACCOUNTS
            'rimary_active_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-ACTIVE-NUMBER-OF-ACCOUNTS'), // RIMARY-ACTIVE-NUMBER-OF-ACCOUNTS
            'primary_overdue_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-OVERDUE-NUMBER-OF-ACCOUNTS'), // PRIMARY-OVERDUE-NUMBER-OF-ACCOUNTS
            'primary_secured_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-SECURED-NUMBER-OF-ACCOUNTS'), // PRIMARY-SECURED-NUMBER-OF-ACCOUNTS
            'primary_unsecured_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-UNSECURED-NUMBER-OF-ACCOUNTS'), // PRIMARY-UNSECURED-NUMBER-OF-ACCOUNTS
            'primary_untagged_number_of_accounts' => array_get($primaryAccountsSummary, 'PRIMARY-UNTAGGED-NUMBER-OF-ACCOUNTS'), // PRIMARY-UNTAGGED-NUMBER-OF-ACCOUNTS
            'primary_current_balance' => array_get($primaryAccountsSummary, 'PRIMARY-CURRENT-BALANCE'), // PRIMARY-CURRENT-BALANCE
            'primary_sanctioned_amount' => array_get($primaryAccountsSummary, 'PRIMARY-SANCTIONED-AMOUNT'), // PRIMARY-SANCTIONED-AMOUNT
            'rimary_disbursed_amount' => array_get($primaryAccountsSummary, 'PRIMARY-DISBURSED-AMOUNT'), // RIMARY-DISBURSED-AMOUNT

            'secondary_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-NUMBER-OF-ACCOUNTS'), // SECONDARY-NUMBER-OF-ACCOUNTS
            'secondary_active_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-ACTIVE-NUMBER-OF-ACCOUNTS'), // SECONDARY-ACTIVE-NUMBER-OF-ACCOUNTS
            'secondary_overdue_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-OVERDUE-NUMBER-OF-ACCOUNTS'), // SECONDARY-OVERDUE-NUMBER-OF-ACCOUNTS
            'secondary_secured_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-SECURED-NUMBER-OF-ACCOUNTS'), // SECONDARY-SECURED-NUMBER-OF-ACCOUNTS
            'secondary_unsecured_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-UNSECURED-NUMBER-OF-ACCOUNTS'), // SECONDARY-UNSECURED-NUMBER-OF-ACCOUNTS
            'secondary_untagged_number_of_accounts' => array_get($secondaryAccountsSummary, 'SECONDARY-UNTAGGED-NUMBER-OF-ACCOUNTS'), // SECONDARY-UNTAGGED-NUMBER-OF-ACCOUNTS
            'secondary_current_balance' => array_get($secondaryAccountsSummary, 'SECONDARY-CURRENT-BALANCE'), // SECONDARY-CURRENT-BALANCE
            'secondary_sanctioned_amount' => array_get($secondaryAccountsSummary, 'SECONDARY-SANCTIONED-AMOUNT'), // SECONDARY-SANCTIONED-AMOUNT
            'secondary_disbursed_amount' => array_get($secondaryAccountsSummary, 'SECONDARY-DISBURSED-AMOUNT'), // SECONDARY-DISBURSED-AMOUNT
        ];

        $data = $this->formatData($data);

        return HmAccountSummary::updateOrCreate($relateData, $data);
    }
}
