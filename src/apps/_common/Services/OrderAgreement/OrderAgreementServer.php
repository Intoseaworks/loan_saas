<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/11
 * Time: 9:53
 */

namespace Common\Services\OrderAgreement;

use Admin\Models\Order\Order;
use Admin\Models\Upload\Upload;
use Admin\Services\BaseService;
use Api\Services\OrderAgreement\LoanContractTemplate;
use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Models\Order\ContractAgreement;
use Common\Models\Order\OrderSignDoc;
use Common\Services\Didio\DigioServer;
use Common\Services\Order\RenewalServer;
use Common\Services\Partner\PartnerAccountServer;
use Common\Utils\AadhaarApi\Api\EsignRequest;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Upload\OssHelper;
use Mpdf\Mpdf;
use Common\Models\Merchant\App;
use Common\Models\Config\LoanMultipleConfig;

/**
 * Class OrderAgreementServer
 * @package Common\Services\OrderAgreement
 * @phan-file-suppress PhanUndeclaredClassMethod, PhanUndeclaredTypeThrowsType, PhanUndeclaredClassProperty
 */
class OrderAgreementServer extends BaseService {

    public function preview($orderId) {
        return $this->generate($orderId, ContractAgreement::CASHNOW_LOAN_CONTRACT, false);
    }

    /**
     * 生成协议内容
     * @param $orderId
     * @param string $type
     * @param bool $buildPdf
     * @param bool $isNew
     * @param string $signType
     * @return mixed|string
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function generate($orderId, $type = ContractAgreement::LOAN_AGREEMENT, $buildPdf = false, $isNew = false, $signType = '', $isUpdate = false) {
        $order = Order::model()->getOne($orderId);
        $replaceEol = true;
        $toThird = false;
        switch ($type) {
            case ContractAgreement::LOAN_AGREEMENT:
                if ($order->repaymentPlanRenewal->isEmpty()) {
                    $content = LoanAgreementTemplate::$content;
                } else {
                    $content = LoanAgreementTemplate::$contentOfRenewal;
                }
                break;
            case ContractAgreement::INTERMEDIARY_AGREEMENT:
                $content = IntermediaryAgreementTemplate::$content;
                break;
            case ContractAgreement::RENEWAL_AGREEMENT:
                $content = RenewalAgreementTemplate::$content;
                break;
            case ContractAgreement::CASHNOW_LOAN_CONTRACT:
                $content = LoanAgreementTemplate::$content;
                $replaceEol = false;
                //$toThird = true;
                if ($buildPdf || !$order->canSign()) {
                    $content = $content . LoanContractTemplate::$signContent;
                }
                break;
            default:
                $content = '';
        }
        $data = static::buildData($order, $isNew, $isUpdate);
        $data = ContractAgreement::model()->replaceFixByType($order->id, $type, $data);
        /** 遍历写入订单数据 */
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        $replaceEol && $content = str_replace(PHP_EOL, '<br/>', $content);
        /** 生成pdf & 保存到contract_agreement */
        if ($buildPdf) {
            $result = $this->buildPdf($content);
            if ($toThird) {
                $this->signInit($signType, $order, $result['local_file_path']);
            }
            $this->store($orderId, $result['oss_file_path'], $type, $data);

            @unlink($result['local_file_path']);
        }
        return $content;
    }

    /**
     * 生成pdf文件
     * @param $content
     * @param bool $needUpload
     * @return array
     * @throws \Mpdf\MpdfException
     */
    public function buildPdf($content, $needUpload = true) {
        $mpdf = new Mpdf([
            'mode' => '+aCJK'
        ]);
        $mpdf->SetDisplayMode('fullpage');
        /** 解决中文乱码问题 */
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->useAdobeCJK = true;
        $mpdf->WriteHTML($content);
        //输出到本地
        $dir = Upload::basePathUpload('tmp/');
        $fileName = StringHelper::generateUuid() . '.pdf';
        if (!is_dir($dir) && !@mkdir($dir, 0777)) {
            EmailHelper::send('目录不存在或生成失败', '合同本地保存失败');
        }
        $localFile = $dir . $fileName;
        $mpdf->Output($localFile);

        $ossFileName = $this->uploadToOss($localFile, $fileName);

        return [
            'oss_file_path' => $ossFileName,
            'local_file_path' => $localFile,
        ];
    }

    public function uploadToOss($localPath, $fileName) {
        $ossFileName = 'data/agreement/' . date('Ymd') . '/' . $fileName;
        /** 上传阿里云oss */
        $helper = OssHelper::helper();
        if (!$helper->uploadFile($ossFileName, $localPath)) {
            EmailHelper::send($helper->getErrors(), '合同上传oss失败');
        }
        return $ossFileName;
    }

    public function signInit($signType, $order, $localFilePath) {
        switch ($signType) {
            case OrderSignDoc::TYPE_DIGIO:
                $this->fileToDigio($order, $localFilePath);
                break;
            case OrderSignDoc::TYPE_ESIGN:
                $this->fileToEsign($order, $localFilePath);
                break;
        }
    }

    public function fileToDigio($order, $localFilePath) {
        $user = $order->user;
        $digioResult = DigioServer::server($user->id)
                ->uploadContractPdf($user->telephone, $localFilePath, $order->id, $user->fullname);
        $digioResult && isset($digioResult['id']) && OrderSignDoc::model()->add($user->id, $order->id, $digioResult['id'], OrderSignDoc::TYPE_DIGIO);
    }

    public function fileToEsign($order, $localFilePath) {
        $user = $order->user;
        $userInfo = $user->userInfo;
        $result = EsignRequest::server()->esignInit($user->id, $localFilePath, $user->fullname, $userInfo->city);

        if ($result->isSuccess()) {
            $data = $result->getData();
            $data && isset($data['id']) && OrderSignDoc::model()->add($user->id, $order->id, $data['id'], OrderSignDoc::TYPE_ESIGN);
        }
    }

    /**
     * 保存订单合同
     * @param $orderId
     * @param $path
     * @param $type
     * @param array $data
     * @return mixed
     * @throws \Throwable
     */
    public function store($orderId, $path, $type, $data = []) {
        return \DB::transaction(function () use ($orderId, $path, $type, $data) {
                    // 使历史协议失效
                    ContractAgreement::model()->toQuietByType($orderId, $type);

                    return ContractAgreement::model()->add($orderId, $path, $type, $data);
                });
    }

    /**
     * 构建协议数据
     * @param Order $order
     * @param $isNew
     * @return array
     * @throws \Exception
     */
    protected function buildData($order, $isNew = false, $isUpdate = false) {
        \Common\Utils\MerchantHelper::helper()->setMerchantId($order->merchant_id);
        $partnerDetail = PartnerAccountServer::server()->partnerDetail();
        $order->setScenario(Order::SCENARIO_LIST)->getText();
        $bankCardNo = $order->bank_card_no ?? ($order->user->bankCard->account_no ?? '---');
        $order->id_card_no = $order->user->id_card_no;
        $order->telephone = $order->user->telephone;
        $order->address = $order->userInfo->address ?? "";
        $order->signed_time = DateHelper::formatToDate($order->signed_time ?? Carbon::now()->toDateTimeString(), 'dS \of F ,Y');
        $order->created_at = DateHelper::formatToDate($order->created_at);
        $order->bank_card_no = StringHelper::maskBankCardNo($bankCardNo);
        $order->company_name = $partnerDetail['company_name'] ?? '---';
        $order->company_address = $partnerDetail['company_address'] ?? '---';
        $order->app_name = $partnerDetail['partner_account_app_name'] ?? App::find($order->app_id)->app_name ?? 'EPERASH';
        $order->admin_account_fullname = $partnerDetail['partner_account_lender_name'] ?? '---';
        $order->admin_account_id_card_no = $partnerDetail['partner_account_lender_id_card'] ?? '---';
        $order->overdue_rate_text = $order->overdue_rate * 100 . '%';
        $order->daily_rate_text = $order->daily_rate * 100 . '%';
        $order->daily_interest = round($order->daily_rate * $order->principal);
        $order->finished_renewal_days = $order->getRenewalDays();
        $order->withdrawal_service_charge = \Common\Services\Order\OrderServer::server()->getWithdrawalServiceCharge($order);

        $this->buildRenewalData($order, $isNew);
        $order->overdue_part_txt = $this->buildPenaltyRatesData($order);

        $order->addHidden(['user', 'channel']);
        return $order->toArray();
    }

    /**
     * 根据是否新生成续期获取续期协议信息
     * @param $order
     * @param bool $isNew
     * @throws \Exception
     */
    public function buildRenewalData($order, $isNew = false) {
        // 新续期(还款计划预览)
        if ($isNew) {
            $renewalInfo = RenewalServer::server()->getRenewalInfo($order);

            // 原应还日期：新续期协议为当前应还日期
            $order->previous_appointment_paid_time = $order->getAppointmentPaidTime(true);
            // 应还日期：新续期协议应动态计算出来
            $order->new_appointment_paid_time = $renewalInfo['appointment_paid_time'];
            $order->renewal_days = $renewalInfo['renewal_days']; // 续期天数
            $order->renewal_issue = $renewalInfo['issue']; // 续期期数
            $order->renewal_apply_date = DateHelper::date(); // 续期申请日期
            $order->renewal_interest = array_get($renewalInfo, 'detailed_fee_explain.renewal_interest'); // 续期息费
            $order->overdue_interest = array_get($renewalInfo, 'detailed_fee_explain.overdue_fee'); // 逾期息费
        } else {
            $lastRepaymentPlanRenewal = $order->lastRepaymentPlan->lastRepaymentPlanRenewal;
            if (!$lastRepaymentPlanRenewal) {
                return false;
            }
            // 原应还日期：查看已存在协议为还款计划上一次应还日期
            $order->previous_appointment_paid_time = DateHelper::formatToDate($order->lastRepaymentPlan->getPreviousAppointmentPaidTime());
            // 应还日期：查看已存在协议取当前应还日期
            $order->new_appointment_paid_time = DateHelper::formatToDate($order->getAppointmentPaidTime());
            $order->renewal_days = $lastRepaymentPlanRenewal->renewal_days; // 续期天数
            $order->renewal_issue = $lastRepaymentPlanRenewal->issue; // 续期期数
            $order->renewal_apply_date = DateHelper::formatToDate((string) $lastRepaymentPlanRenewal->created_at); // 续期申请日期
            $order->renewal_interest = $lastRepaymentPlanRenewal->renewal_interest; // 续期息费
            $order->overdue_interest = $lastRepaymentPlanRenewal->overdue_interest; // 逾期息费
        }
    }

    public function buildPenaltyRatesData($order) {
        $user = $order->user;
        $config = LoanMultipleConfig::getConfigByCnt($order->merchant_id, $user->getRepeatLoanCnt());
        $content = "a. Overdue Fee ( Daily charge item )<br />";
        foreach ($config['penalty_start_days'] as $key => $val) {
            $content .= "- Overdue for {$val} day to {$config['penalty_end_days'][$key]} days will be charged for ". $config['penalty_rates'][$key]*100 ."% of the contract amount.<br />";
        }
        $content .= "<br />b. Penalty Fee ( One time charge item )<br />";
        $content .= "- Overdue for {$config['forfeit_penalty_rate'][0]} days will be charged for {$config['forfeit_penalty_rate'][1]}% of the contract amount.<br />";
        return $content;
    }

}
