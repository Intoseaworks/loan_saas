<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Test;

use Common\Services\Pay\BasePayServer;
use Common\Models\Trade\TradeLog;
use Common\Response\ApiBaseController;
use Common\Utils\Payment\Razorpay\Api as CAPI;
use Api\Services\Common\CallbackServer;

class PayController extends ApiBaseController {

    public function htmlpay() {
        $objApi = new CAPI(env('PAY_RAZORPAY_KEY_ID'), env('PAY_RAZORPAY_KEY_SECRET'));
        $contact = [
            "name" => "wangzhe"
        ];
        $arrContact = $objApi->contact->create($contact);
        print_r($arrContact['id']);
        $fund = [
            "contact_id" => $arrContact['id'],
            "account_type" => "bank_account",
            "bank_account" => [
                "name" => "Gaurav Kumar",
                "ifsc" => "HDFC0000053",
                "account_number" => "765432123456789"
            ]
        ];
        $arrFun = $objApi->fundaccount->create($fund);
        print_r($arrFun);
        $payOut = [
            "account_number" => "2323230087044190",
            "fund_account_id" => $arrFun['id'],
            "amount" => 100,
            "currency" => "INR",
            "mode" => "IMPS",
            "purpose" => "payout",
            "queue_if_low_balance" => true,
            "reference_id" => "Acme Transaction ID 12345"];
        $arrPayout = $objApi->payout->create($payOut);
        print_r($arrPayout);
        /* 测试paylink
          $pay = new BasePayServer();
          $tradeLog['amount'] = 123;
          $tradeLog['transaction_no'] = "test". Time();
          $tradeLog['transaction_info'] = "test". Time();
          $result = $pay->razorpayPayLink($tradeLog);
          $data = $result->getData();
          print_r($result->isSuccess());
          print_r($data); */
    }

    public function testCall() {
        $noticeInfo = [
            "tradeTime" => time(),
            "status" => "SUCCESS",
            "tradeNo" => "5F02A12F2203220200706",
            "tradeAmount" => "2009.8",
            "requestNo" => "asb",
        ];
        $tradeLog = TradeLog::getByTransactionNo($noticeInfo['tradeNo']);
        CallbackServer::server()->finishTrade($tradeLog, $noticeInfo);
    }

}
