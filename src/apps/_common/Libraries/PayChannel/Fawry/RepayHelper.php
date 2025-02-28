<?php

namespace Common\Libraries\PayChannel\Fawry;

use Common\Models\BankCard\BankCardRepay;
use Common\Models\Trade\TradeLog;
use Common\Services\Pay\BasePayServer;
use Common\Services\Repay\RepayServer;
use Common\Utils\Curl;
use Common\Utils\Data\DateHelper;
use JMD\Libs\Services\DataFormat;

class RepayHelper extends FawryBase {

    public function callback($params) {
        $this->_writeLog($params, "fawry_repay_callback");
        if (isset($params['orderItems'])) {
            foreach ($params['orderItems'] as $item) {
                if ($params['orderStatus'] == 'PAID') {
                    $tradeLog = TradeLog::model()->where('transaction_no', $item['itemCode'])->orderBy('id')->first();
                    if ($tradeLog) {
                        if ($tradeLog->isSuccess()) {
                            // 回调已处理
                            continue;
                        }
                        $repaymentPlan = $tradeLog->order->lastRepaymentPlan;
                        $tradeLog->evolveStatusOverResultSuccess($item['itemCode'], DateHelper::dateTime(), DateHelper::dateTime(), $item['price']);
                        RepayServer::server($repaymentPlan, $tradeLog)->completeRepay();
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function pull($merchantRefNumber) {
        $config = $this->getConfig();
        $merchantCode = $config['merchant_code'];
        $merchant_sec_key = $config['secure_key']; // For the sake of demonstration
        $signature = hash('sha256', $merchantCode . $merchantRefNumber . $merchant_sec_key);
        $response = Curl::get($config['url'] . "/ECommerceWeb/Fawry/payments/status/v2" . "?merchantCode=" . $merchantCode . "&merchantRefNumber=" . $merchantRefNumber . "&signature=" . $signature);
        return json_decode($response, TRUE);
    }

    public function repay(TradeLog $tradeLog, $method = null) {
        if (method_exists($this, $method)) {
            return $this->$method($tradeLog);
        }
        $res['code'] = DataFormat::OUTPUT_ERROR;
        $res['msg'] = "return status:Method ({$method}) Not Exists";
        $res['data'] = "";
        return new DataFormat($res);
    }

    public function app(TradeLog $tradeLog) {
        $config = $this->getConfig();
        $amount = $tradeLog->business_amount;
        $data = [
            "merchant_id" => $config['merchant_code'],
            "items" => [
                [
                    'itemId' => $tradeLog->transaction_no,
                    'description' => $tradeLog->master_business_no,
                    'price' => $amount,
                    'quantity' => '1'
                ]
            ],
        ];
        $res = [
            "code" => DataFormat::OUTPUT_SUCCESS,
            "msg" => "success",
            "data" => $data,
        ];
        return new DataFormat($res);
    }

    public function referenceNumber(TradeLog $tradeLog) {
        $action = "/ECommerceWeb/Fawry/payments/charge";
        $config = $this->getConfig();
        $merchantCode = $config['merchant_code'];
        $merchantRefNum = $tradeLog->transaction_no;
        $merchant_cust_prof_id = $tradeLog->user_id;
        $payment_method = 'PAYATFAWRY';
        $amount = $tradeLog->business_amount;
        $merchant_sec_key = $config['secure_key']; // For the sake of demonstration
        $signature = hash('sha256', $merchantCode . $merchantRefNum . $merchant_cust_prof_id . $payment_method . $amount . $merchant_sec_key);
        $data = [
            'merchantCode' => $merchantCode,
            'merchantRefNum' => $merchantRefNum,
            'customerName' => $tradeLog->user->fullname,
            'customerMobile' => $tradeLog->user->telephone,
            'customerEmail' => $tradeLog->user->userInfo->email,
            'customerProfileId' => $merchant_cust_prof_id,
            'amount' => $amount,
            'paymentExpiry' => (strtotime(date("Y-m-d 23:59:59")) - 60 * 60 * 6) * 1000,
            'currencyCode' => 'EGP',
            'language' => 'en-gb',
            'chargeItems' => [[
            'itemId' => $tradeLog->transaction_no,
            'description' => $tradeLog->master_business_no,
            'price' => $amount,
            'quantity' => '1'
                ]],
            'signature' => $signature,
            'paymentMethod' => $payment_method,
            'description' => date("Y-m-d")
        ];
        $response = $this->post($data, $config['url'] . $action);
        if (isset($response['statusCode']) && $response['statusCode'] && "200" == $response['statusCode']) {
            $res = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => "success",
                "data" => [
                    "status" => BasePayServer::RESULT_SUCCESS,
                    "requestNo" => $response['referenceNumber'],
                    "tradeNo" => $response['referenceNumber'],
                    "walletQr" => "",
                    "msg" => date("Y-m-d 23:59:59"),
                ],
            ];
        } else {
            $res['code'] = DataFormat::OUTPUT_ERROR;
            $res['msg'] = "return status:{$response["statusCode"]}[{$response['statusDescription']}]";
            $res['data'] = $response;
        }
        return new DataFormat($res);
    }

    public function paymentLink(TradeLog $tradeLog) {
        $token = $this->token();
        $config = $this->getConfig();
        $action = "/invoice-api/invoices/payment-link";
        $sendingDate = date("Y-m-d");
        $expiryDate = date("Y-m-d\T23:59:00.000\Z");
        $note = "invoice description";
        $releaseDate = date("Y-m-d\TH:i:s.000\Z");
        $alertMerchantUponExpiry = "false";
        $requiredCustomerData = [
            ["id" => $tradeLog->user_id,
                "code" => "NAME",
                "nameAr" => 'اسم العميل',
                "nameEn" => 'Customer name'
            ]
        ];
        $discount = [
            "value" => 20,
            "type" => "FLAT"
        ];
        $taxes = 0;
        $amount = 150.75;
        $items = [
            "nameEn" => "description 1",
            "nameAr" => "description 1",
            "itemCode" => "b2f35ed2d39e462abd5e4b1129a7305d",
            "purchasedQuantity" => 2,
            "price" => 150.75,
        ];
        $preferredPaymentMethod = "PayAtFawry";
        $header = [
            "content-type: application/json;charset=UTF-8",
            'Accept: application/json',
            'Authorization: Bearer sss' . $token
        ];
        $data = [
            'requiredCustomerData' => $requiredCustomerData,
            'amount' => $amount,
            'sendingDate' => $sendingDate,
            'expiryDate' => $expiryDate,
            'releaseDate' => $releaseDate,
            'note' => $note,
            'alertMerchantUponExpiry' => $alertMerchantUponExpiry,
            'discount' => $discount,
            'items' => $items,
            'taxes' => $taxes,
            'preferredPaymentMethod' => $preferredPaymentMethod,
        ];
        echo json_encode($data);
        $response = $this->post($data, "https://atfawry.fawrystaging.com/invoice-api/invoices/payment-link", $header);
        echo json_encode($response);
        exit;
    }

    public function eWallet(TradeLog $tradeLog) {
        $action = "/ECommerceWeb/api/payments/charge";
        $config = $this->getConfig();
        $merchantCode = $config['merchant_code'];
        $merchantRefNum = $tradeLog->transaction_no;
        $merchant_cust_prof_id = $tradeLog->user_id;
        $payment_method = 'MWALLET';
        $amount = $tradeLog->business_amount;
        $merchant_sec_key = $config['secure_key']; // For the sake of demonstration
        $signature = hash('sha256', $merchantCode . $merchantRefNum . $merchant_cust_prof_id . $payment_method . $amount . $merchant_sec_key);

        $data = [
            'merchantCode' => $merchantCode,
            'merchantRefNum' => $merchantRefNum,
            'customerMobile' => $tradeLog->user->telephone,
            'customerEmail' => $tradeLog->user->userInfo->email,
            'customerProfileId' => $merchant_cust_prof_id,
            'amount' => $amount,
            'currencyCode' => 'EGP',
            'language' => 'en-gb',
            'chargeItems' => [[
            'itemId' => $tradeLog->transaction_no,
            'description' => $tradeLog->master_business_no,
            'price' => $amount,
            'quantity' => '1'
                ]],
            'signature' => $signature,
            'paymentMethod' => $payment_method,
            'description' => date("Y-m-d")
        ];
        $response = $this->post($data, "https://atfawry.fawrystaging.com/ECommerceWeb/api/payments/charge");
        if (isset($response['statusCode']) && $response['statusCode'] && "200" == $response['statusCode']) {
            $res = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => "success",
                "data" => [
                    "status" => BasePayServer::RESULT_SUCCESS,
                    "requestNo" => $response['referenceNumber'],
                    "tradeNo" => $response['referenceNumber'],
                    "walletQr" => $response['walletQr'],
                    "msg" => "SUCCESS",
                ],
            ];
        } else {
            $res['code'] = DataFormat::OUTPUT_ERROR;
            $res['msg'] = "return status:{$response["statusCode"]}[{$response['statusDescription']}]";
            $res['data'] = $response;
        }
        return new DataFormat($res);
    }

    public function bank(TradeLog $tradeLog) {
        $action = "/ECommerceWeb/Fawry/payments/charge";
        $config = $this->getConfig();
        $bankCardRepay = BankCardRepay::model()->getOne($tradeLog->trade_account_no);
        $merchantCode = $config['merchant_code'];
        $merchantRefNum = $tradeLog->transaction_no;
        $merchant_cust_prof_id = $tradeLog->user_id;
        $payment_method = 'CARD';
        $amount = $tradeLog->business_amount;
        $cardNumber = $bankCardRepay->card_number;
        $cardExpiryYear = $bankCardRepay->expiry_year;
        $cardExpiryMonth = $bankCardRepay->expiry_month;
        $cvv = $bankCardRepay->cvv;
        $merchant_sec_key = '259af31fc2f74453b3a55739b21ae9ef'; // For the sake of demonstration
        $signature = hash('sha256', $merchantCode . $merchantRefNum . $merchant_cust_prof_id . $payment_method .
                $amount . $cardNumber . $cardExpiryYear . $cardExpiryMonth . $cvv . $merchant_sec_key);
        $data = [
            'merchantCode' => $merchantCode,
            'merchantRefNum' => $merchantRefNum,
            'customerMobile' => '01234567891',
            'customerEmail' => 'example@gmail.com',
            'customerProfileId' => '777777',
            'cardNumber' => $cardNumber,
            'cardExpiryYear' => $cardExpiryYear,
            'cardExpiryMonth' => $cardExpiryMonth,
            'cvv' => $cvv,
            'amount' => $amount,
            'currencyCode' => 'EGP',
            'language' => 'en-gb',
            'chargeItems' => [
                'itemId' => $merchantRefNum,
                'description' => $tradeLog->master_business_no,
                'price' => $amount,
                'quantity' => '1'
            ],
            'signature' => $signature,
            'paymentMethod' => 'CARD',
            'description' => ''
        ];
        $response = $this->post($data, $config['url'] . $action);
        if (isset($response['statusCode']) && $response['statusCode'] && "200" == $response['statusCode']) {
            $res = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => "success",
                "data" => [
                    "status" => BasePayServer::RESULT_SUCCESS,
                    "requestNo" => $response['referenceNumber'],
                    "tradeNo" => $response['referenceNumber'],
                    "walletQr" => $response['walletQr'],
                    "msg" => "SUCCESS",
                ],
            ];
        } else {
            $res['code'] = DataFormat::OUTPUT_ERROR;
            $res['msg'] = "return ".json_encode($response);
            $res['data'] = $response;
        }
        return new DataFormat($res);
    }

}
