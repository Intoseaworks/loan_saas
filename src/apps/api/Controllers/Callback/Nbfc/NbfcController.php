<?php

namespace Api\Controllers\Callback\Nbfc;

use Api\Models\Order\Order;
use Common\Models\Nbfc\NbfcReportConfig;
use Common\Models\Nbfc\NbfcReportLog;
use Common\Response\ServicesApiBaseController;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Nbfc\kudos\Kudos;

class NbfcController extends ServicesApiBaseController
{
    public function kudosNotice()
    {
        try {
            $params = $this->request->post();
            $partnerLoanId = array_get($params, 'partner_loan_id');
            $kudosLoanId = array_get($params, 'kudos_loan_id');
            MerchantHelper::setMerchantId(1);
            $order = Order::query()->where(['order_no' => $partnerLoanId])->first();
            if (!$order) {
                throw new \Exception('订单不存在');
            }
            $nbfcReportConfig = NbfcReportConfig::getConfig($order->merchant_id);
            list($logKudosBorrowerId, $logKudosLoanId) = (new Kudos(json_decode($nbfcReportConfig->config, true)))->getKudosIds($order);
            if ($logKudosLoanId != $kudosLoanId) {
                throw new \Exception("kudosloanid不一致({$logKudosLoanId})({$kudosLoanId})");
            }
            $request = $params;
            $jsonSuccessRes = json_encode([
                'status' => true,
                'result_code' => 200,
                'message' => 'Success!',
                "values" => [
                    'kudosloanid' => $logKudosLoanId,
                    'kudosborrowerid' => $logKudosBorrowerId,
                    'account_status' => 'ACTIVE',
                    'info' => 'Transaction update received by partner.'
                ]
            ]);
            $response = $jsonSuccessRes;
            NbfcReportLog::model(NbfcReportLog::SCENARIO_CREATE)->saveModel([
                'merchant_id' => $order->merchant_id,
                'user_id' => $order->user->id,
                'order_id' => $order->id,
                'type' => NbfcReportLog::TYPE_REPAY,
                'platform' => NbfcReportConfig::PLATFORM_KUDOS,
                'step' => Kudos::STEP_OFFLINE_TRANSACTION,
                'request' => is_array($request) ? json_encode($request, 256) : $request,
                'response' => is_array($response) ? json_encode($response, 256) : $response,
            ]);
            echo $jsonSuccessRes;
            exit();
        } catch (\Exception $e) {
            DingHelper::notice([
                'param' => $params,
                'e' => $e->getMessage(),
            ], 'kudos回调报错');
            $jsonFailRes = json_encode([
                'status' => false,
                'result_code' => 400,
                'message' => 'Failure!',
                "values" => [
                    'info' => 'A log entry has been made related to this error'
                ]
            ], 256);
            echo $jsonFailRes;
            exit();
        }
    }

}
