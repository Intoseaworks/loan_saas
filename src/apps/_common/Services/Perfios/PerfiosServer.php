<?php

namespace Common\Services\Perfios;

use App\Common\Helpers\ArrayHelp;
use App\Models\Perfios\PerfiosCustomerInfo;
use App\Models\Perfios\PerfiosMonthlyDetail;
use App\Models\Perfios\PerfiosSummaryAverage;
use App\Models\Perfios\PerfiosSummaryInfo;
use App\Models\Perfios\PerfiosSummaryTotal;
use App\Models\Perfios\PerfiosTop5FundsReceived;
use App\Models\Perfios\PerfiosTop5FundsTransferred;
use App\Models\Perfios\PerfiosXn;
use Common\Services\BaseService;
use Common\Services\Perfios\Api\Request;

class PerfiosServer extends BaseService
{
    /**
     * @var array
     */
    const DESTINATION_MAP = [
        1 => 'netbankingFetch',
        2 => 'statement',
        3 => 'bankAccountDetailsFetch',
    ];

    /**
     * @param $userId
     * @param $type
     * @param string $returnUrl
     * @return string
     */
    public function startProcess($userId, $type, $returnUrl = '')
    {
        $destination = static::DESTINATION_MAP[$type];
        return (new Request())->startProcess(['userId' => $userId, 'destination' => $destination, 'returnUrl' => $returnUrl]);
    }

    /**
     * @param $actionId
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function retrieve($actionId, $userId)
    {
        $data = (new Request())->retrieve(['actionId' => $actionId, 'userId' => $userId]);
        return $this->saveData($data, $userId);
    }

    /**
     * @param $data
     * @param $userId
     * @return bool
     * @throws \Exception
     */
    public function saveData($data, $userId)
    {
        ArrayHelp::transformKeysToSnakeCase($data);
        PerfiosCustomerInfo::saveReportOne($userId, array_get($data, 'customer_info.@attributes'));
        PerfiosSummaryInfo::saveReportOne($userId, array_get($data, 'summary_info.@attributes'));
        PerfiosSummaryTotal::saveReportOne($userId, array_get($data, 'summary_info.total.@attributes'));
        PerfiosSummaryAverage::saveReportOne($userId, array_get($data, 'summary_info.average.@attributes'));

        $handler = function ($data) {
            return array_map(function ($item) {
                return $item['@attributes'] ?? $item;
            }, $data);
        };

        $monthlyDetails = array_get($data, 'monthly_details.monthly_detail');
        PerfiosMonthlyDetail::saveReportAll($userId, $handler($monthlyDetails));

        $top5FundsReceived = array_get($data, 'top5_funds_received.item');
        PerfiosTop5FundsReceived::saveReportAll($userId, $handler($top5FundsReceived));

        $top5FundsTransferred = array_get($data, 'top5_funds_transferred.item');
        PerfiosTop5FundsTransferred::saveReportAll($userId, $handler($top5FundsTransferred));

        $xns = array_get($data, 'xns.xn');
        PerfiosXn::saveReportAll($userId, $handler($xns));

        return true;
    }

    /**
     * 检查是否爬取成功
     *
     * @param $responseInfo
     * @return bool
     */
    public function isPSuccess($responseInfo)
    {
        if (array_get($responseInfo, 'header.x-perfios-callback-type.0') == Request::TRANSACTION_COMPLETE &&
            array_get($responseInfo, 'post.status') == Request::STATUS_COMPLETE
        ) {
            return true;
        }

        return false;
    }
}
