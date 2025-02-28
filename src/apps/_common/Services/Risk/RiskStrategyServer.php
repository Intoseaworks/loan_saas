<?php

namespace Common\Services\Risk;

use Common\Models\Order\Order;
use Common\Models\Risk\RiskStrategyIndex;
use Common\Models\Risk\RiskStrategyResult;
use Common\Models\Risk\RiskStrategyTask;
use Common\Services\BaseService;
use Common\Utils\Email\EmailHelper;
use GuzzleHttp\Client;

/**
 * Class RiskStrategyServer
 * @package Common\Services\Risk
 */
class RiskStrategyServer extends BaseService
{
    const RISK_STRATEGY_URL = 'http://47.112.24.173:8181/gethandle';
    const CODE_SUCCESS = '000';
    const CODE_WAIT = '001';
    const STRATEGY_PASS = '通过';
    const STRATEGY_REJECT = '拒绝';

    /**
     * @param Order $order
     * @param $step
     */
    public function getDataByRulesPlatform($order, $step)
    {
        return $this->outputError('直接不返回urule规则结果');
        $params = [
            'merchantId' => $order->merchant_id,
            'strategyStep' => $step,
            'orderId' => $order->id
        ];
        $url = self::RISK_STRATEGY_URL . '?' . http_build_query($params);
        $client = new Client();
        $res = $client->request('POST', $url);
        $res = json_decode($res->getBody(), true);
        $msg = array_get($res, 'status.msgCn');
        $code = array_get($res, 'status.code');
        /** 策略平台未准备好，稍后再试 */

        if ($code == self::CODE_WAIT) return $this->outputError($msg);
        if ($code == self::CODE_SUCCESS) {
            $data = array_get($res, 'body');
            $result = array_get($data, 'result');
            $skipRiskControl2 = array_get($result, 'skipRiskControl2');
            $skipManualApproval = array_get($result, 'skipManualApproval');
            $rejectCode = array_get($result, 'rejectCode');
            $strategyResult = array_get($result, 'result');
            /** 指标 */
            $var = array_get($data, 'var');
            $params = [];
            foreach ($result as $key => $val) {
                $params[snake_case($key)] = $val;
            }
            $params['order_id'] = $order->id;
            $params['strategy_step'] = $step;
            \DB::beginTransaction();
            try {
                /** 写入规则平台执行结果 修改task状态 */
                if (RiskStrategyResult::model()->create($params)) {
                    foreach ($var as $key => $val) {
                        $params = [
                            'order_id' => $order->id,
                            'strategy_step' => RiskStrategyTask::RISK_STRATEGY_STEP_1,
                            'var' => $key,
                            'value' => $val
                        ];
                        /** 保存指标数据 */
                        RiskStrategyIndex::model()->create($params);
                    }
                    \DB::commit();
                }
            } catch (\Exception $exception) {
                \DB::rollBack();
                EmailHelper::send($result, '规则平台执行结果保存异常');
            }
            return $this->outputSuccess($msg, array_values(compact('skipRiskControl2', 'skipManualApproval', 'rejectCode', 'strategyResult')));
        } else {
            EmailHelper::send($res, '策略平台请求异常');
        }
    }
}
