<?php

namespace Common\Libraries\PayChannel\Paymob;

use Common\Models\Trade\TradeLog;
use Common\Services\Pay\BasePayServer;
use JMD\Libs\Services\DataFormat;
use Risk\Common\Models\Business\Order\OrderDetail;

class PayoutHelper extends PaymobBase {

    public function callback($params) {
        $this->_writeLog($params, "paymob_payout_callback");
    }

    public function pull($queryDate = null, $queryPage = 1) {
        $date = date("Y-m-d");
        if ($queryDate) {
            $date = $queryDate;
        }
        $page = '/transaction/inquire/?page=' . $queryPage;
        $mapTradeLog = [];
        $tradeLogs = TradeLog::model()
                ->where("trade_platform", TradeLog::TRADE_PLATFORM_PAYMOB)
                ->where("trade_evolve_status", TradeLog::TRADE_EVOLVE_STATUS_TRADING)
                ->where(\DB::raw("DATE(created_at)"), $date)
                ->get();
        $config = $this->getConfig();
        $header = $this->authToken();
        $data = [
            "bank_transactions" => TRUE,
            "transactions_ids_list" => []
        ];
        if ($tradeLogs) {
            foreach ($tradeLogs as $tradeLog) {
                $data['transactions_ids_list'][] = $tradeLog->request_no;
                $mapTradeLog[$tradeLog->request_no] = $tradeLog;
            }
            $header = $this->authToken();
            $records = $this->post($data, $config['url'] . $page, null, $header);
            foreach ($records['results'] as $record) {
                print_r($record);
                if ('200' == $record['status_code']) {
                    $this->success($tradeLog, strtotime($record['updated_at']));
                }
                if ("failed" == $record['disbursement_status']) {
                    $this->failed($tradeLog, strtotime($record['updated_at']), "[{$record['status_code']}]{$record['status_description']}");
                }
            }
            if ($records['next']) {
                $queryPage += 1;
                $this->pull($date, $queryPage);
            }
        }
        echo "end";
    }

    public function success(TradeLog $tradeLog, $tradeTime) {
        if ($tradeLog->isSuccess()) {
            return true;
        }
        RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, $tradeTime);
        return true;
    }

    public function failed(TradeLog $tradeLog, $tradeTime, $failDescription) {
        if ($tradeLog->isFailed()) {
            return true;
        }
        RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, [
            'msg' => $failDescription,
        ]);
        return true;
    }

    public function run(TradeLog $tradeLog) {
        $config = $this->getConfig();
        $order = $tradeLog->order;
        $bankName = OrderDetail::model()->getBankName($order);
        $tradeAccountNo = OrderDetail::model()->getBankCardNo($order);
        $data = [
            "issuer" => "bank_card",
            "amount" => $tradeLog->business_amount,
//            "msisdn" => $tradeLog->user->telephone,
            "bank_card_number" => $tradeAccountNo,
            "bank_transaction_type" => "cash_transfer",
            "bank_code" => self::BANK_CODE[$bankName]?? "",
            "full_name" => $tradeLog->user->fullname,
        ];
        $header = $this->authToken();
        $arrPayout = $this->post($data, $config['url'] . '/disburse/', null, $header);
        if (isset($arrPayout['transaction_id']) && $arrPayout['transaction_id'] && "8000" == $arrPayout['status_code']) {
            $res = [
                "code" => DataFormat::OUTPUT_SUCCESS,
                "msg" => "success",
                "data" => [
                    "status" => BasePayServer::RESULT_SUCCESS,
                    "requestNo" => $arrPayout['transaction_id'],
                    "tradeNo" => $arrPayout['transaction_id'],
                    "msg" => $arrPayout['status_description'],
                ],
            ];
        } else {
            $res['code'] = DataFormat::OUTPUT_ERROR;
            $res['msg'] = "return status:{$arrPayout["status_code"]}";
            $res['data'] = $arrPayout;
        }
        return new DataFormat($res);
    }

}
