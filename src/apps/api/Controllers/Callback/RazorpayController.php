<?php

namespace Api\Controllers\Callback;

use Api\Services\Common\CallbackServer;
use Admin\Services\TradeManage\RemitServer;
use Api\Services\Order\OrderServer;
use Common\Models\Trade\TradeLog;
use Common\Response\ServicesApiBaseController;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;
use Razorpay\Api\Api;
use Api\Models\Payment\PaymentRazorpayNotify;

class RazorpayController extends ServicesApiBaseController {

    public function webhook() {
        $params = $this->request->post();
//        $params = json_decode('{"entity":"event","account_id":"acc_ETJSxGBQJrjhNW","event":"payment.captured","contains":["payment"],"payload":{"payment":{"entity":{"id":"pay_Fe3xm0i02Q5jQe","entity":"payment","amount":353100,"currency":"INR","status":"captured","order_id":"order_Fe3mKqiyIAUjPA","invoice_id":"inv_Fe3mKpx3CQKsPU","international":false,"method":"card","amount_refunded":0,"refund_status":null,"captured":true,"description":"#inv_Fe3mKpx3CQKsPU","card_id":"card_Fe3xmIpyRSpjlG","card":{"id":"card_Fe3xmIpyRSpjlG","entity":"card","name":"pramendra Kumar Gupta","last4":"0067","network":"RuPay","type":"debit","issuer":"SYNB","international":false,"emi":false,"sub_type":"consumer"},"bank":null,"wallet":null,"vpa":null,"email":"prem.gupta2208@gmail.com","contact":"+919752311409","notes":[],"fee":418,"tax":64,"error_code":null,"error_description":null,"error_source":null,"error_step":null,"error_reason":null,"acquirer_data":{"auth_code":"944813"},"created_at":1600338263}}},"created_at":1600338285}', true);
        $this->validateSign();
        if (isset($params['event'])) {
            PaymentRazorpayNotify::model(PaymentRazorpayNotify::SCENARIO_CREATE)->saveModel([
                "event" => $params['event'],
                "data" => json_encode($params)
            ]);
        }
        switch ($params['event']) {
            case "order.paid":
                exit('SUCCESS');
//                $this->paid($params);
                break;
            case "payment.authorized":
                break;
            case "payment.failed":
                break;
            case "payment.captured";
                $this->captured($params);
                break;
            case "payout.processed":
                $this->daifuNotice($params);
                break;
            case "payout.reversed":
                $this->daifuNotice($params);
                break;
        }
        exit('SUCCESS');
    }

    /**
     * 代付回调
     */
    protected function daifuNotice($params) {
        try {
            $traNo = $params['payload']['payout']['entity']['reference_id'];
            $status = $params['payload']['payout']['entity']['status'];
            $tradeLog = TradeLog::getByTransactionNo($traNo);
            $noticeInfo = [
                "status" => "FAILED",
                "tradeTime" => $params['created_at'],
                "requestNo" => $params['payload']['payout']['entity']['id'],
                "tradeNo" => $traNo,
                "tradeAmount" => $params['payload']['payout']['entity']['amount'] / 100,
                "msg" => isset($params['payload']['payout']['entity']['failure_reason']) ? $params['payload']['payout']['entity']['failure_reason'] : ""
            ];
            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            $this->validateSign();

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代付出款，交易记录未找到');
                exit('交易记录未获取到');
            }

            $tradeTime = date('Y-m-d H:i:s', $noticeInfo['tradeTime'] ?: time());
            if ($status == 'processed') {
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
                RemitServer::server()->flowRemitSuccess($tradeLog, $noticeInfo['tradeAmount'], $tradeTime, true, array_only($noticeInfo, ['requestNo', 'tradeNo', 'tradeAmount']));
            }
            if ($status == 'reversed') {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
                RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, array_only($noticeInfo, ['requestNo', 'tradeNo', 'tradeResultCode', 'msg']));
            }
            exit('SUCCESS');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            EmailHelper::send(var_export($params, true) . "\n e:{$msg}", '代付出款报错');
            exit($e->getMessage());
        }
    }

    protected function paid($params) {
        try {

            DB::beginTransaction();

            $traNo = $params['payload']['order']['entity']['receipt'];
            $status = $params['payload']['order']['entity']['status'];
            $tradeLog = TradeLog::getByTransactionNo($traNo);
            $noticeInfo = [
                "status" => "FAILED",
                "tradeTime" => $params['payload']['payment']['entity']['created_at'],
                "requestNo" => $params['payload']['payment']['entity']['id'],
                "tradeNo" => $traNo,
                "tradeAmount" => $params['payload']['payment']['entity']['amount'] / 100,
                "msg" => $params['payload']['payment']['entity']['description'] ?? ""
            ];
            if (!$tradeLog || !$tradeLog->order) {
                DingHelper::notice(var_export($params, true), 'htmlpay回调，交易记录未找到' . $traNo);
                exit('交易记录未获取到');
            }
            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            // 加排他锁
            $tradeLog = TradeLog::query()->where('id', $tradeLog->id)->lockForUpdate()->first();

            if ($status == 'paid') {
                $noticeInfo['status'] = "SUCCESS";
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
            } else {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
            }

            CallbackServer::server()->finishTrade($tradeLog, $noticeInfo);
            DB::commit();
            exit('SUCCESS');
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(json_encode($params) . "\n" . $e->getMessage(), 'htmlpay回调报错');
            exit($e->getMessage());
        }
    }

    public function captured($params) {
        DB::beginTransaction();
        $tradeLog = TradeLog::query()->where('request_no', $params['payload']['payment']['entity']['order_id'])->lockForUpdate()->first();
        $noticeInfo = [
            "status" => "FAILED",
            "tradeTime" => $params['created_at'],
            "requestNo" => $params['payload']['payment']['entity']['id'],
            "tradeNo" => '',
            "tradeAmount" => $params['payload']['payment']['entity']['amount'] / 100,
            "msg" => $params['payload']['payment']['entity']['description'] ?? ""
        ];
        MerchantHelper::setMerchantId($tradeLog->merchant_id);
        $status = $params['payload']['payment']['entity']['status'];
        if ($status == 'captured') {
            $noticeInfo['status'] = "SUCCESS";
            if ($tradeLog->isSuccess() && strpos($tradeLog->trade_desc, $params['payload']['payment']['entity']['id']) !== FALSE) {
                // 回调已处理
                exit('SUCCESS');
            }
        } else {
            if ($tradeLog->isFailed()) {
                // 回调已处理
                exit('SUCCESS');
            }
        }

        $tradeLog->trade_desc .= "[{$params['payload']['payment']['entity']['id']}:{$params['payload']['payment']['entity']['amount']}]";
        $tradeLog->save();
        CallbackServer::server()->finishTrade($tradeLog, $noticeInfo);
        DB::commit();
        try {
            exit('SUCCESS');
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(json_encode($params) . "\n" . $e->getMessage(), 'htmlpay回调报错');
            exit($e->getMessage());
        }
    }
    
    public function manual(){
        $jsons = [
            '{"entity":"event","account_id":"acc_ETJSxGBQJrjhNW","event":"payment.captured","contains":["payment"],"payload":{"payment":{"entity":{"id":"pay_FPQ2r7CQpPJ6YN","entity":"payment","amount":49500,"currency":"INR","status":"captured","order_id":"order_FPPzkPx7wmx6nV","invoice_id":"inv_FPPzkPVFJf5rHW","international":false,"method":"card","amount_refunded":0,"refund_status":null,"captured":true,"description":"#inv_FPPzkPVFJf5rHW","card_id":"card_FPQ2rDVmEXDxul","card":{"id":"card_FPQ2rDVmEXDxul","entity":"card","name":"shameem banu","last4":"6431","network":"Visa","type":"debit","issuer":"INDB","international":false,"emi":false,"sub_type":"consumer"},"bank":null,"wallet":null,"vpa":null,"email":"shamsnisaa1991@gmail.com","contact":"+919848202235","notes":[],"fee":238,"tax":0,"error_code":null,"error_description":null,"error_source":null,"error_step":null,"error_reason":null,"acquirer_data":{"auth_code":"556821"},"created_at":1597140925}}},"created_at":1597140962}'
            ];
        foreach($jsons as $json){
                $json = json_decode($json, true);
        $this->captured($json);
        }
    }

    /**
     * 验签
     */
    protected function validateSign() {
        $params = $this->request->post();
        $header = $this->request->header("x-razorpay-signature");
        $api = new Api(env('PAY_RAZORPAY_KEY_ID'), env('PAY_RAZORPAY_KEY_SECRET'));
        try {
            $api->utility->verifyWebhookSignature(file_get_contents("php://input"), $header, env('PAY_RAZORPAY_MY_SECRET'));
            //EmailHelper::send("route:" . request()->getRequestUri() . "\n" . json_encode($params) . "\n " . json_encode($header), "延签成功");
        } catch (\Exception $e) {
            EmailHelper::send("route:" . request()->getRequestUri() . "\n param\n" . json_encode($params) . "\n header\n" . json_encode($header), 'razorpay回调验签失败' . $e->getMessage());
            exit('验签错误');
        }
    }

}
