<?php

namespace Common\Jobs;

use Common\Models\Order\ContractAgreement;
use Common\Models\Order\Order;
use Common\Services\Upload\UploadServer;
use Common\Utils\Email\EmailHelper;

/**
 * Class ContractEmailSendJob
 * 电子合同发送指定email
 * @package CashNow\Common\Jobs
 */
class ContractEmailSendJob extends Job
{
    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    public $orderId;
    public $receiver = [];

    public function __construct($orderId, $email)
    {
        $this->orderId = $orderId;
        $this->receiver = (array)$email;
    }

    public function handle()
    {
        try {
            $order = Order::model()->getOne($this->orderId);
            $user = $order->user;
            $orderDigio = $order->orderDigio;
            /** @var ContractAgreement $contractAgreement */
            $contractAgreement = $order->contractAgreementCashnowLoan;

            if (!isset($orderDigio) || !$orderDigio->contract_agreement_id) {
                EmailHelper::send("用户：{$user->id} 合同未生成\norder_digio_id:{$order->id}", '【失败】合同发送', 'wangshaliang@jiumiaodai.com');
                return false;
            }
            if (!isset($contractAgreement) || !$contractAgreement->url) {
                $errorStr = "用户：{$user->id} 合同不存在\norder_digio_id:{$order->id}\ncontract_agreement_id:{$orderDigio->contract_agreement_id}";
                EmailHelper::send($errorStr, '【失败】合同异常，不存在', 'wangshaliang@jiumiaodai.com');
                return false;
            }

            $title = '[Cash Now]Contract ' . $order->order_no;
            $content = $this->getContent($order);

            $tmpPath = public_path('tmp');
            $attach = UploadServer::setOssTmp($tmpPath, $contractAgreement->url);
            if (!$attach) {
                throw new \Exception('获取不到pdf地址 ' . json_encode([
                        'sign_digio_id' => $order->sign_digio_id,
                        'attach' => $attach,
                    ], 256)
                );
            }

            $res = EmailHelper::send($content, $title, $this->receiver, false, $attach);

            @unlink($attach);
            if (!$res) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            EmailHelper::send('【异常】合同发送邮箱队列处理异常', json_encode([
                'order_id' => $order->id,
                'email_receiver' => $this->receiver,
                'e_msg' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ], 256), 'wangshaliang@jiumiaodai.com');

            throw $e;
        }
    }

    public function getTitle()
    {

    }

    /**
     * 邮件内容
     * @param Order $order
     * @return string
     */
    private function getContent($order)
    {
        $user = $order->user;
        //footer base64图片
        $footerImgBase64 = $this->getFooterImgBase64();

        $content = "Dear Mr./Ms.    [{$user->fullname}]：
<br>
<br>
Congratulations! We have confirmed the contract No,{$order->order_no}(Contract No,).
<br>
<br>
Thanks for your trust, Cash Now are happy to help our client for instant cash’s need.
And, once the transfer is done, you should pay attention your repayment day, in case there
is overdue,  which will bring bad effective on your credit for next loan in future.
<br>
<br>
On time repayment will help you become a vip on our platform. Get higher loan amount and
Faster transfer.
Have a good day with Cash Now! Best wishes!
<br>
<br>
<img src='{$footerImgBase64}'>
";

        return $content;
    }

    /**
     * 获取底部 base64 图片
     * @return string
     */
    public function getFooterImgBase64()
    {
        return 'data:image/png;base64,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';
    }
}
