<?php

namespace Common\Services\Report;

use Common\Models\Nbfc\NbfcReportConfig;
use Common\Models\Nbfc\NbfcReportLog;
use Common\Models\Order\Order;
use Common\Services\BaseService;
use Common\Utils\Nbfc\kudos\Kudos21;
use Common\Utils\Nbfc\vishnupriya\Vishnupriya;

class NbfcReportServer extends BaseService
{
    public function handle(Order $order, $reportNode)
    {
        $nbfcReportConfig = NbfcReportConfig::getConfig($order->merchant_id);

        // 未配置，不需上报
        if (!$nbfcReportConfig) {
            return false;
        }

        $platform = $nbfcReportConfig->platform;
        $needReportNode = array_get(NbfcReportConfig::PLATFORM_NEED_REPORT_NODE, $platform);
        // 当前节点不在对应nbfc需要上报的节点，不需上报
        if (!in_array($reportNode, $needReportNode)) {
            return false;
        }

        $instance = $this->getInstance($platform, json_decode($nbfcReportConfig->config, true));

        $res = $instance->$reportNode($order);

        if ($platform != NbfcReportConfig::PLATFORM_KUDOS) {
            $request = $instance->getRequestInfo();
            $response = $instance->getResponseInfo();

            NbfcReportLog::model(NbfcReportLog::SCENARIO_CREATE)->saveModel([
                'merchant_id' => $order->merchant_id,
                'user_id' => $order->user->id,
                'order_id' => $order->id,
                'type' => $reportNode,
                'platform' => $platform,
                'request' => $request,
                'response' => $response,
            ]);
        }

        if (!$res) {
            throw new \Exception("nbfc上报失败." . get_class($instance) . ':' . $reportNode);
        }

        if ($reportNode == array_get(NbfcReportConfig::PLATFORM_PASS_NODE, $platform)) {
            $order->nbfcReportPass();
        }

        return $res;
    }

    public function getInstance($platform, $config = null)
    {
        switch ($platform) {
            case NbfcReportConfig::PLATFORM_VISHNUPRIYA:
                return new Vishnupriya($config);
            case NbfcReportConfig::PLATFORM_KUDOS:
                return new Kudos21($config);
            default:
                throw new \Exception('nbfc上报平台未定义');
        }
    }

    public function needReportNbfc($merchantId)
    {
        return (bool)NbfcReportConfig::getConfig($merchantId);
    }
}
