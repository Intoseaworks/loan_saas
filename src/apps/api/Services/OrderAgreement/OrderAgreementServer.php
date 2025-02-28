<?php

namespace Api\Services\OrderAgreement;

use Api\Services\Order\OrderServer;
use Common\Events\Order\OrderAgreementEvent;
use Common\Jobs\ContractEmailSendJob;
use Common\Models\BankCard\BankCard;
use Common\Models\Common\Config;
use Common\Models\Merchant\App;
use Common\Models\Order\ContractAgreement;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\Order\OrderSignDoc;
use Common\Models\Third\ThirdPartyLog;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Utils\Data\DateHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\Upload\OssHelper;
use Illuminate\Support\Facades\Auth;

class OrderAgreementServer extends \Common\Services\OrderAgreement\OrderAgreementServer
{

    const STATUS_SIGN_SUCCESS = 'success';
    const STATUS_SIGN_FAIL = 'fail';

    /**
     * 构建协议数据
     * @param Order $order
     * @param bool $isNew
     * @return array
     * @throws \Exception
     */
    public function buildData($order, $isNew = false, $isUpdate = false)
    {
        $order->setScenario(Order::SCENARIO_LIST)->getText();
        /** @var User $user */
        $user = User::find($order->user_id);
        $userInfo = $user->userInfo;
        $userWork = optional($user->userWork);
        $bankCard = $isUpdate?BankCard::getUsableByUserIdAndNo($order->user_id, OrderDetail::model()->getBankCardNo($order)):$user->bankCard;
        $userContacts = optional($user->userContacts->first());
        $userContacts2 = optional($user->userContacts->last());

        if (!$userInfo) {
            EmailHelper::send("userinfo数据不存在 \n order_id:{$order->id}", 'cashnow-saas 合同数据构建错误', 'wangshaliang@jiumiaodai.com');
            throw new \Exception('userinfo数据不存在');
        }

        // 合同创建时间
        $contractCreatedDate = date('d/m/Y');
        $contractCreatedTime = date('H:i:s');
        if (isset($order->contractAgreementCashnowLoan)) {
            $contractCreatedDate = DateHelper::format($order->contractAgreementCashnowLoan->created_at, 'd/m/Y');
            $contractCreatedTime = DateHelper::format($order->contractAgreementCashnowLoan->created_at, 'H:i:s');
        }
        // 还款计划应还日期
        $appointmentPaidTime = DateHelper::format($order->appointment_paid_time, 'd/m/Y');

        $bankName = $bankCard->bank_name ?? "";//银行名称
        $bankNo = $bankCard->account_no ?? "";// 银行账户号码
        $bankIfsc = $bankCard->ifsc ?? "";// IFSC 号码
        $signFormatTime = date('l M d H:i:s Y');

        /**
         * 更新合同
         */
        if ($isUpdate) {
            $contractCreatedDate = DateHelper::format($order->signed_time, 'd/m/Y');
            $contractCreatedTime = DateHelper::format($order->signed_time, 'H:i:s');
            $appointmentPaidTime = DateHelper::format($order->lastRepaymentPlan->appointment_paid_time, 'd/m/Y');
            $signFormatTime = DateHelper::format($order->signed_time, 'l M d H:i:s Y');
        }

        $aadhaarCardNo = $userInfo->aadhaar_card_no;
        $address = $userInfo->permanent_city . '|' . $userInfo->permanent_province . '|' . $userInfo->permanent_address;

        $userFiles = $this->getUserFiles($user->id);

        $order->addHidden(['lastRepaymentPlan', 'orderDetails', 'repaymentPlanRenewal']);
        $appName = App::find($order->app_id)->app_name ?? 'Indiaox SaaS';

        if (!$order->merchant || !$order->merchant->lender) {
            DingHelper::notice(['order_id' => $order->id], '合同创建异常-商户关联NBFC账号未配置');
            throw new \Exception('商户关联NBFC账号未配置');
        }

        $data = [
            'app_name' => $appName,
            'date' => $contractCreatedDate, // 合同创建日期
            'time' => $contractCreatedTime, // 合同创建时间
            'sign_image_url' => $this->getSignUrl($user->id),
            'fullname' => $user->fullname, //姓名
            'father_name' => $userInfo->father_name, //用户父亲名称
            'birthday' => $userInfo->birthday, //出生年月日
            'gender' => $userInfo->gender, //性别
            'marital_status' => $userInfo->marital_status, //婚姻状况
            'profession' => $userWork->profession, //职业
            'residence_type' => $userInfo->residence_type, //住所类型
            'aadhaar_card_no' => $aadhaarCardNo, //Aadhaar  卡号码
            'pan_card_no' => $userInfo->pan_card_no, //Pan卡号码
            'aadhaar_address' => $address, //Aadhaar卡地址
            'address' => $userInfo->city . '|' . $userInfo->province . '|' . $userInfo->address, //现在住址
            'telephone' => $user->telephone, //手机号码
            'email' => $userInfo->email, // 邮箱
            'bank_name' => $bankName, //银行名称
            'bank_no' => $bankNo, // 银行账户号码
            'ifsc' => $bankIfsc, // IFSC 号码
            'contact_fullname' => $userContacts->contact_fullname, // 紧急联系人名字
            'contact_telephone' => $userContacts->contact_telephone, // 紧急联系人电话

            'contact_fullname2' => $userContacts2->contact_fullname, // 紧急联系人名字2
            'contact_telephone2' => $userContacts2->contact_telephone, // 紧急联系人电话2

            'contract_no' => $order->order_no, // 合同编号？
            'contract_city' => $userInfo->city,//借款城市

            'is_upload_bank_bill' => $user->getBankBillStatus() == '1' ? '√' : '',// 如已上传银行账号则添加√
            'is_upload_pay_slip' => $user->getPaySlipStatus() == '1' ? '√' : '',// 如已上传工资单则添加√

            'principal' => $order->principal, // 借款金额/贷款金额
            'charge' => OrderServer::server()->getProcessingFeeAddGst($order), // 处理费
            'repayment_amount' => $order->repayAmount(),//还款金额

            'loan_days' => $order->loan_days, // 借款期限

            'contract_time' => $contractCreatedDate, // 签约时间
            'appointment_paid_time' => $appointmentPaidTime, // 还款计划应还日期

            'user_img_file' => $userFiles,//$userImgFile,

            'lender' => $order->merchant->lender,//'STAR FINSERV INDIA LIMITED',//旧：SHABRI Investment Pvt ltd
            // 旧的TODO ..以上仅测试实现功能，其余参数待文案确定后优化添加
            'sign_pdf_url' => $this->getSignUrl($user->id),

            'sign_format_time' => $signFormatTime,
            'sign_domain' => 'Indiaox.in',
            'signed_by' => 'IndiaOx',
            'sign_reason' => 'loan',
            'daily_rate' => $order->daily_rate * 100,
            'overdue_rate' => $order->overdue_rate * 100,
            'loan_reason' => (new OrderDetail)->getLoanReason($order),
        ];

        return $data;
    }

    protected function getSignUrl($userId)
    {
        $files = Upload::model()->getFileByUser($userId, Upload::TYPE_DOGIO_SIGN);
        $file = $files->last();

        if (!$file) {
            return '';
        }
        return OssHelper::helper()->pdfTokenUrl($file['path']);
    }

    protected function getUserFiles($userId)
    {
        $files = [];
        $data = Upload::model()->getFileByUser($userId);
        foreach ($data as $item) {
            /*if (in_array($item['type'], [Upload::TYPE_AADHAAR_CARD_FRONT, Upload::TYPE_AADHAAR_CARD_BACK])) {
                continue;
            }*/
            $files[$item['type']] = $item;
        }

        $userImgFile = '';
        foreach ($files as $userFile) {
//            if (in_array($userFile['type'], [
//                UserFile::PAN_CARD,
//                UserFile::PASSPORT_IDENTITY,
//                UserFile::PASSPORT_DEMOGRAPHICS,
//                UserFile::VOTER_ID_CARD_FRONT,
//                UserFile::VOTER_ID_CARD_BACK,
//                UserFile::DRIVING_LICENSE_FRONT,
//                UserFile::DRIVING_LICENSE_BACK,
//                UserFile::ADDRESS_AADHAAR_FRONT,
//                UserFile::ADDRESS_AADHAAR_BACK,
//            ])) {
            $imgUrl = OssHelper::helper()->picTokenUrl($userFile['path']);
            $fileType = Upload::TYPE[$userFile['type']] ?? 'image';
            $userImgFile .= "
<span>{$fileType}</span><br><img style='margin: 20px;width: 287px;height: 340px' src='{$imgUrl}'><br>";
//            }
        }

        return $userImgFile;
    }

    public function sendContract($order, $email)
    {
        dispatch(new ContractEmailSendJob($order->id, $email));
    }

    /**
     * 验证并创建合同
     * @param Order $order
     * @param $user
     * @param $sync
     * @return OrderAgreementServer
     */
    public function generateContract(Order $order, $user, $sync = true)
    {
        if (isset($order->contractAgreementCashnowLoan)) {
            return $this->outputSuccess(t("合同已生成"));
        }

        $thirdPartyLog = new ThirdPartyLog();
        $thirdPartyLog->setUserId($user->id);
        $thirdPartyLog->createByRequest(ThirdPartyLog::NAME_SIGN);

        $this->validateCanGenerate($order, $user);
        if ($this->isError()) {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL, ['order_id' => $order->id]);
            //(new AuthRequestHelper())->sign($order, $user, self::STATUS_SIGN_FAIL);
            return $this->outputError($this->getMsg());
        }
        $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, ['order_id' => $order->id]);
        //(new AuthRequestHelper())->sign($order, $user, self::STATUS_SIGN_SUCCESS);

        if ($sync) {
            return $this->generateContractSync($order, $user);
        } else {
            return $this->generateContractAsync($order, $user);
        }
    }

    public function generateContractSync($order, $user)
    {
        $bankCard = $user->bankCard;
        if (!$bankCard) {
            return $this->outputError(t("未绑定银行卡"));
        }
        try {
            $this->generate($order->id, ContractAgreement::CASHNOW_LOAN_CONTRACT, true, false);
        } catch (\Exception $e) {
            DingHelper::notice(
                $e->getMessage() . '|' . $e->getFile() . ':' . $e->getLine() . "order_id:{$order->id}",
                '实时合同生成错误',
                DingHelper::AT_SOLIANG
            );
            return $this->outputError(t("合同生成失败，请重试"));
        }

        return $this->outputSuccess(t('合同已生成'));
    }

    public function generateContractAsync($order, $user)
    {
        $this->generateExec($order, $user);
        if ($this->isError()) {
            return $this->outputError($this->msg, $this->data);
        }
        return $this->outputSuccess(t('合同创建中'));
    }

    /**
     * @param Order $order
     * @param User $user
     * @return OrderAgreementServer
     */
    private function validateCanGenerate(Order $order, $user)
    {
        if (in_array($order->status, Order::WAIT_PAY_STATUS)) {
            return $this->outputException(t("签约已完成，请勿重复操作", 'messages'));
        }
        if (!$order->canSign()) {
            return $this->outputException(t("订单状态异常, 请取消当前订单重新申请", 'messages'));
        }
//        if ($user->getIsCompleted() == false) {
//            return $this->outputException(t("资料未完善", 'messages'));
//        }
    }

    /**
     * 执行合同生成
     * @param $order
     * @param User $user
     * @param $signType
     * @return OrderAgreementServer
     */
    private function generateExec($order, $user, $signType = '')
    {
        $bankCard = $user->bankCard;
        if (!$bankCard) {
            return $this->outputError(t("未绑定银行卡"));
        }

        $lock = LockRedisHelper::helper()->addLock(LockRedisHelper::LOCK_ORDER_SIGN_INTERVAL, 60);
        if (!$lock) {
            return $this->outputSuccess(t('合同创建中'));
        }

        event(new OrderAgreementEvent($order->id, ContractAgreement::CASHNOW_LOAN_CONTRACT, $signType));

        return $this->outputSuccess(t('合同创建中'));
    }

    /**
     * 获取签名认证方的链接
     * digio | esign
     * @param $signType
     * @param $isThird
     * @return OrderAgreementServer
     */
    public function getOrderSignUrl($signType, $isThird = false)
    {
        // 判断是否有 禁止订单签约提醒
        if ($tip = Config::model()->getOrderNoSignTip()) {
            return $this->outputError($tip);
        }

        /** @var \Api\Models\User\User $user */
        $user = Auth::user();
        $order = $user->order;
        if (!$order) {
            return $this->outputError(t('order error'));
        }
        $orderId = $order->id;

        $this->validateCanGenerate($order, $user);
        if ($this->isError()) {
            return $this->outputError($this->getMsg());
        }

        if (!$isThird) {
            return $this->outputSuccess(
                'success',
                ['url' => str_finish(config('config.h5_client_domain'), '/') . 'contract']
            );
        }

        if ($orderSignDoc = OrderSignDoc::model()->getDocIdByOrderId($orderId, $signType)) {
            if ($orderSignDoc->doc_id) {
                return $this->outputSuccess(
                    'success',
                    ['url' => $orderSignDoc->getSignUrlByType()]
                );
            }
        }

        $lock = LockRedisHelper::helper()->hasLock(LockRedisHelper::LOCK_ORDER_SIGN_INTERVAL);
        // 如果不存在缓存，则重新请求生成合同
        if (!$lock) {
            $this->generateExec($order, $user, $signType);
            if ($this->isError()) {
                return $this->outputError($this->msg, $this->data);
            }
        }

        return $this->outputError(t("In the contract creation, you must sign the contract within 30 minutes after the creation."));
    }

    /**
     * 签约提交
     *
     * @return OrderAgreementServer
     */
    public function signSubmit()
    {
        // 判断是否有 禁止订单签约提醒
        if ($tip = Config::model()->getOrderNoSignTip()) {
            return $this->outputError($tip);
        }

        /** @var \Api\Models\User\User $user */
        $user = Auth::user();
        $order = $user->order;
        if (!$order) {
            return $this->outputError(t('order error', 'order'));
        }

        $this->validateCanGenerate($order, $user);
        if ($order->status == Order::STATUS_SIGN) {
            return $this->outputSuccess('订单已签约', 'order');
        }
        $lock = LockRedisHelper::helper()->orderSign($order->id);
        // 如果存在缓存，返回提示
        if ($lock) {
            return $this->outputException(t('合同签约中', 'order'));
        }

        $orderAgreementServer = OrderAgreementServer::server();
        $type = ContractAgreement::CASHNOW_LOAN_CONTRACT;
        $buildPdf = true;
        $isNew = true;
        $orderAgreementServer->generate($order->id, $type, $buildPdf, $isNew);
        OrderServer::server()->sign($user);
        return $this->outputSuccess();
    }
}
