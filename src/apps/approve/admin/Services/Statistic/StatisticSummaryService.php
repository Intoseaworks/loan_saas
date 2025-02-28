<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-15
 * Time: 20:29
 */

namespace Approve\Admin\Services\Statistic;


use Approve\Admin\Services\CommonService;
use Common\Models\Approve\ApprovePageStatistic;
use Common\Traits\GetInstance;
use Common\Utils\Data\DateHelper;
use DB;

class StatisticSummaryService
{
    use GetInstance;

    /**
     * @param $adminIds
     * @param $approveTime
     * @return array
     */
    public function getStatisticSummary($adminIds, $approveTime)
    {
        $statisticsData = [];
        $query = ApprovePageStatistic::leftJoin('approve_user_pool', 'approve_user_pool.id', 'approve_page_statistic.approve_user_pool_id')
            ->whereIn('approve_page_statistic.admin_id', $adminIds);

        if (!empty($approveTime[0])) {
            $query->where('approve_user_pool.created_at', '>=', date('Y-m-d H:i:s', (int)$approveTime[0]));
        }
        if (!empty($approveTime[1])) {
            $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
            $query->where('approve_user_pool.created_at', '<=', date('Y-m-d H:i:s', $nextDay));
        }

        $rows = $query
            ->select([
                DB::raw('SUM(cost) as cost'),
                'approve_page_statistic.page',
                'approve_page_statistic.admin_id'
            ])
            ->effective()
            ->groupBy('approve_page_statistic.admin_id', 'approve_page_statistic.page')
            ->get()
            ->groupBy('admin_id');

        foreach ($rows as $k => $row) {
            $summary = $this->summary($row);
            $statisticsData[$k] = $summary;
        }

        return $statisticsData;
    }

    /**
     * @param $data
     * @param bool $withTotal
     * @return array
     */
    protected function summary($data, $withTotal = false)
    {
        $firstApproveText = [];
        $callApproveText = [];
        $total = 0;
        foreach ($data as $item) {
            $total += $item['cost'];
            $cost = DateHelper::useHours(time() - $item['cost']);
            switch ($item['page']) {
                case ApprovePageStatistic::CASHNOW_PAGE_BASE_DETAIL:
                    array_push($firstApproveText, 'Basic detail A.T.：' . $cost);
                    break;
                case ApprovePageStatistic::CASHNOW_PAGE_BANK_STATEMENT:
                    array_push($firstApproveText, 'Bank statement A.T.：' . $cost);
                    break;
                case ApprovePageStatistic::CASHNOW_PAGE_PAYSLIP:
                    array_push($firstApproveText, 'Pay slip A.T.：' . $cost);
                    break;
                case ApprovePageStatistic::CASHNOW_PAGE_EMPLOYEE_CARD:
                    array_push($firstApproveText, 'Employee card  A.T.：' . $cost);
                    break;
                case ApprovePageStatistic::CASHNOW_PAGE_EMPLOYEE_INFO:
                    array_push($firstApproveText, 'Employment info  A.T.：' . $cost);
                    break;
                case ApprovePageStatistic::CASHNOW_PAGE_CALL_BASEDETAIL:
                    array_push($callApproveText, 'Call approval A.T.：' . $cost);
            }
        }

        if ($withTotal) {
            $total = DateHelper::useHours(time() - $total);
            array_unshift($firstApproveText, 'Total A.T.：' . $total);
            array_unshift($callApproveText, 'Total A.T.：' . $total);
        }

        return [
            ApprovePageStatistic::TYPE_FIRST_APPROVE => $firstApproveText,
            ApprovePageStatistic::TYPE_CALL_APPROVE => $callApproveText,
        ];

    }

    /**
     * @param $userPoolIds
     * @return array
     */
    public function getPageSummary($userPoolIds)
    {
        $statisticsData = [];
        $rows = ApprovePageStatistic::whereIn('approve_user_pool_id', $userPoolIds)
            ->select([
                'page',
                'admin_id',
                'cost',
                'approve_user_pool_id',
                'type',
            ])
            ->effective()
            ->get()
            ->groupBy('approve_user_pool_id');

        foreach ($rows as $k => $item) {
            $summary = $this->summary($item, true);
            $statisticsData[$k] = $summary[$item->first()->type];
        }

        return $statisticsData;
    }
}
