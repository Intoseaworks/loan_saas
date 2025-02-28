<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-08
 * Time: 16:24
 */

namespace Approve\Admin\Services\Statistic;


use Approve\Admin\Services\CommonService;
use Common\Models\Approve\ApprovePageStatistic;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApproveUserPool;
use Common\Traits\GetInstance;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use DB;

class ApproveStatisticService
{
    use GetInstance;

    /**
     * @var integer
     */
    const SORT_PASS = 1;

    /**
     * @var integer
     */
    const SORT_RETURN = 2;

    /**
     * @var integer
     */
    const SORT_REJECT = 3;

    /**
     * @var integer
     */
    const SORT_MISSED = 4;

    /**
     * @param $condition
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|mixed
     * @throws \Common\Exceptions\CustomException
     */
    public function getAuditList($condition)
    {
        $where1 = "approve_pool_id=aup.approve_pool_id and aup.`status` not in (0, 10) and aup.`merchant_id` = ".MerchantHelper::getMerchantId();
        $where = " 1=1 ";

        $export = false;

        if (!empty($condition['export_file'])) {
            $export = true;
        }

        $approveTime = [];
        if (!empty($condition['approve_time'])) {
            if ($export) {
                $approveTime = explode(',', $condition['approve_time']);
            } else {
                $approveTime = $condition['approve_time'];
            }
            if (!empty($approveTime[0])) {
                $where .=" AND created_at>='".date('Y-m-d', (int)$approveTime[0])."'";
            }
            if (!empty($approveTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
                $where .=" AND created_at<='".date('Y-m-d H:i:s', $nextDay)."'";
            }
        }

        if (!empty($condition['admin_id'])) {
            $where .=" AND id=".$condition['admin_id'];
        }
        $sql = "SELECT id as admin_id,product_name,username,nickname,
        sum(case when `status`=2 THEN 1 ELSE 0 END) as pass,
        sum(case when `status`=6 THEN 1 ELSE 0 END) as reject,
        sum(case when `status`=6 OR `status`=2 THEN 1 ELSE 0 END) as total,
        created_at
FROM
(
select m.product_name,admin.username,admin.id,admin.nickname,ap.id as pool_id,ap.`status`,date(ap.updated_at) as created_at
FROM staff admin
INNER JOIN merchant m ON admin.merchant_id=m.id
INNER JOIN approve_user_pool aup ON aup.admin_id=admin.id
INNER JOIN approve_pool ap ON  aup.approve_pool_id=ap.id
WHERE aup.id = (SELECT id from approve_user_pool where $where1 order by id desc limit 1)
GROUP BY admin.id,ap.id,ap.`status`,created_at
) a
WHERE $where
GROUP BY admin_id,created_at
order by product_name desc,created_at desc,total desc";

        $perPage = !empty($condition['per_page']) ? $condition['per_page'] : 10;
        $currentPage = array_get($condition, 'page', 1);
        if (isset($condition['page']) && $condition['page']) {
            $currentPage = $condition['page'];
        }
        $data = \DB::table(\DB::raw("({$sql}) as questionstock"))
            ->paginate($perPage);
        foreach ($data->items() as $item) {
            $item->pass_rate = ($item->total?$item->pass/$item->total:0)*100;
            $item->pass_rate .='%';
        }

        $data = $data->toArray();

        // 导出
        if ($export) {
            \Admin\Exports\Approve\ApproveStatisticExport::getInstance()->export($data['data'], \Admin\Exports\Approve\ApproveStatisticExport::SCENE_STATISTIC_LIST, false);
            //return (new ApproveStatisticExport($summary))->setData($data['data'])->export();
        }
        return $data;

    }

    /**
     * @param $condition
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|mixed
     * @throws \Common\Exceptions\CustomException
     */
    public function getList($condition)
    {
        $query = ApproveUserPool::query()
            ->done()
            ->join('approve_pool_log', 'approve_pool_log.approve_user_pool_id', 'approve_user_pool.id');

        $export = false;
        if (!empty($condition['approve_type'])) {
            $query->where('approve_pool_log.type', $condition['approve_type']);
        }

        if (!empty($condition['export_file'])) {
            $export = true;
        }

        $approveTime = [];
        if (!empty($condition['approve_time'])) {
            if ($export) {
                $approveTime = explode(',', $condition['approve_time']);
            } else {
                $approveTime = $condition['approve_time'];
            }
            if (!empty($approveTime[0])) {
                $query->where('approve_user_pool.created_at', '>=', date('Y-m-d H:i:s', (int)$approveTime[0]));
            }
            if (!empty($approveTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
                $query->where('approve_user_pool.created_at', '<=', date('Y-m-d H:i:s', $nextDay));
            }
        }

        if (!empty($condition['admin_id'])) {
            $query->where('approve_user_pool.admin_id', $condition['admin_id']);
        }

        $statisticQuery = clone $query;
        $totalPeopleQuery = clone $query;

        $totalPeople = $totalPeopleQuery
            ->groupBy('approve_user_pool.admin_id')
            ->get()
            ->count();
        $perPage = !empty($condition['per_page']) ? $condition['per_page'] : 10;

        $data = $query->select([
            'approve_user_pool.admin_id',
            'approve_user_pool.created_at',
            'approve_pool_log.type',
            DB::raw('COUNT(approve_user_pool.id) as total')
        ])
            ->groupBy('approve_user_pool.admin_id', 'approve_pool_log.type')
            ->orderByDesc('approve_user_pool.created_at')
            ->paginate($export ? 10000 : $perPage);

        $adminIds = $data->pluck('admin_id')
            ->unique()
            ->filter()
            ->toArray();
        $statisticData = $statisticQuery->select([
            'approve_user_pool.admin_id',
            'approve_pool_log.type',
            'approve_user_pool.status',
        ])
            ->whereIn('approve_user_pool.admin_id', $adminIds)
            ->get()
            ->toArray();

        $statistics = $this->getStatisticSummary($statisticData);

        $statisticSummary = [];
        if ($export && $adminIds) {
            $statisticSummary = StatisticSummaryService::getInstance()->getStatisticSummary($adminIds, $approveTime);
        }
        $adminUserInfo = CommonService::getInstance()->getAdminUserInfo($adminIds);
        $approvePool = new ApprovePool();
        foreach ($data->items() as $item) {
            $key = $item['admin_id'] . '-' . $item['type'];
            $item->setAttribute('username', $adminUserInfo[$item['admin_id']]['nickname'] ?? '');
            $item->setAttribute('nickname', $adminUserInfo[$item['admin_id']]['username'] ?? '');
            if (!empty($statistics[$key])) {
                $statistics[$key]['pass_rate'] = $this->getPassRate($statistics[$key]['pass'], $item['total']);
                $statistics[$key]['cost'] = $this->getCost($item['admin_id'], $item['type'], $approveTime);
                if ($statisticSummary) {
                    $summary = $statisticSummary[$item['admin_id']][$item['type']] ?? [];
                    $statistics[$key]['summary'] = implode("\n", $summary);
                }
                $statistics[$key]['type_text'] = $approvePool->getApproveType($item['type']);
                $item->fill($statistics[$key]);
            }
        }

        $data = $data->toArray();

        // 导出
        if ($export) {
            $summary = [
                'approveTimeStart' => '',
                'approveTimeEnd' => '',
                'aprpoveType' => 'All',
                'approver' => 'All',
            ];
            if (!empty($condition['approve_time'])) {
                $approveTime = explode(',', $condition['approve_time']);
                if (!empty($approveTime[0])) {
                    $summary['approveTimeStart'] = date('Y-m-d H:i:s', $approveTime[0]);
                }
                if (!empty($approveTime[1])) {
                    $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
                    $summary['approveTimeEnd'] = date('Y-m-d H:i:s', $nextDay);
                }
            }

            if (!empty($condition['approve_type'])) {
                $summary['aprpoveType'] = $approvePool->getApproveType($condition['approve_type']);
            }

            if (!empty($condition['admin_id'])) {
                if (!$adminUserInfo) {
                    $adminUserInfo = CommonService::getInstance()->getAdminUserInfo((array)$condition['admin_id']);
                }
                $summary['approver'] = $adminUserInfo[$condition['admin_id']]['nickname'] ?? '';
            }

            \Admin\Exports\Approve\ApproveStatisticExport::getInstance()->export($data['data'], \Admin\Exports\Approve\ApproveStatisticExport::SCENE_STATISTIC_LIST, false);
            //return (new ApproveStatisticExport($summary))->setData($data['data'])->export();
        }

        $data['total_peopele'] = $totalPeople;
        return $data;

    }

    /**
     * @param $statisticData
     * @return array
     */
    protected function getStatisticSummary($statisticData)
    {
        $statistics = [];
        foreach ($statisticData as $row) {
            $key = $row['admin_id'] . '-' . $row['type'];
            if (empty($statistics[$key])) {
                $statistics[$key] = [
                    'pass' => 0,
                    'return' => 0,
                    'reject' => 0,
                    'missed' => 0,
                ];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_PASS)) {
                ++$statistics[$key]['pass'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_RETURN)) {
                ++$statistics[$key]['return'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_REJECT)) {
                ++$statistics[$key]['reject'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_MISSED)) {
                ++$statistics[$key]['missed'];
            }
        }

        return $statistics;
    }

    /**
     * @param $statisticData
     * @return array
     */
    protected function getAuditStatisticSummary($statisticData)
    {
        $statistics = [];
        foreach ($statisticData as $row) {
            $key = $row['admin_id'];
            if (empty($statistics[$key])) {
                $statistics[$key] = [
                    'pass' => 0,
                    'return' => 0,
                    'reject' => 0,
                    'missed' => 0,
                ];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_PASS)) {
                ++$statistics[$key]['pass'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_RETURN)) {
                ++$statistics[$key]['return'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_REJECT)) {
                ++$statistics[$key]['reject'];
            }

            if (in_array($row['status'], ApproveUserPool::SORT_MISSED)) {
                ++$statistics[$key]['missed'];
            }
        }

        return $statistics;
    }

    /**
     * @param $pass
     * @param $total
     * @return string
     */
    protected function getPassRate($pass, $total)
    {
        return ($total ? round($pass / $total, 2) * 100 : 0) . '%';
    }

    /**
     * @param int $adminId
     * @param int $type
     * @param $approveTime
     * @return string
     */
    protected function getCost($adminId, $type, $approveTime)
    {
        $query = ApprovePageStatistic::leftJoin('approve_user_pool', 'approve_user_pool.id', 'approve_page_statistic.approve_user_pool_id')
            ->where(['approve_page_statistic.admin_id' => $adminId, 'approve_page_statistic.type' => $type]);

        if (!empty($approveTime[0])) {
            $query->where('approve_user_pool.created_at', '>=', date('Y-m-d H:i:s', (int)$approveTime[0]));
        }
        if (!empty($approveTime[1])) {
            $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
            $query->where('approve_user_pool.created_at', '<=', date('Y-m-d H:i:s', $nextDay));
        }

        $sumCost = $query
            ->effective()
            ->sum('cost');
        $cost = DateHelper::useHours(time() - $sumCost);
        return $cost;
    }

    /**
     * @return array
     */
    public function getApproveTypeList()
    {
        $list = [0 => t('全部', 'approve')] + ApprovePool::approveTypeList();
        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * @return array
     * @throws \Common\Exceptions\CustomException
     */
    public function getApproveUserList()
    {
        $adminUserIds = ApproveUserPool::select('admin_id')
            ->groupBy('admin_id')
            ->pluck('admin_id')
            ->toArray();
        $adminUserInfo = CommonService::getInstance()->getAdminUserInfo($adminUserIds);
        $nickname = [0 => 'All'] + collect($adminUserInfo)->pluck('nickname', 'id')->toArray();
        return ArrayHelper::arrToOption($nickname, 'key', 'val');
    }

    /**
     * @param $condition
     * @return array
     * @throws \Common\Exceptions\CustomException
     */
    public function detail($condition)
    {
        $query = $this->buildQuery($condition);
        $totalQuery = clone $query;

        $query->whereIn('approve_user_pool.status', $this->statusMap()[$condition['sort_type']]);
        $perPage = $condition['per_page'] ?? 10;
        $data = $query->select([
            'approve_pool.order_no',
            'approve_pool_log.order_status',
            'approve_pool_log.wait_time',
            'approve_user_pool.id',
            'approve_user_pool.status'
        ])
            ->join('approve_pool', 'approve_pool.id', 'approve_pool_log.approve_pool_id')
            ->paginate($perPage);

        $userPoolIds = $data
            ->pluck('id')
            ->toArray();
        if ($userPoolIds) {
            $summaryData = StatisticSummaryService::getInstance()->getPageSummary($userPoolIds);
        }

        $approvePool = new ApprovePool();
        foreach ($data->items() as $item) {
            $item->setAttribute('order_status_text', $approvePool->getOrderStatusText($item['order_status']));
            $item->setAttribute('page_summary', ArrayHelper::arrToOption($summaryData[$item['id']] ?? [], 'key', 'val'));
            $item->setAttribute('wait_time', DateHelper::usedDays(time() - $item['wait_time']));
            $item->setAttribute('miss_reason', $item->getMissReasonText());
        }

        $username = CommonService::getInstance()
                ->getAdminUserInfo([$condition['admin_id']])[$condition['admin_id']]['nickname'] ?? '';
        $totalOrders = $totalQuery
            ->where('admin_id', $condition['admin_id'])
            ->count();

        $data = $data->toArray();

        if (!empty($condition['approve_time'])) {
            $approveTime = explode(',', $condition['approve_time']);
            $start = $end = '';
            if (!empty($approveTime[0])) {
                $start = date('Y-m-d H:i:s', (int)$approveTime[0]);
            }
            if (!empty($approveTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
                $end = date('Y-m-d H:i:s', $nextDay);
            }
            $data['approve_time'] = $start . '~' . $end;
        } else {
            $data['approve_time'] = 'all';
        }

        $data['username'] = $username;
        $data['total_orders'] = $totalOrders;

        return $data;

    }

    /**
     * @param $condition
     * @return ApproveUserPool|\Illuminate\Database\Eloquent\Builder
     */
    protected function buildQuery($condition)
    {
        $query = ApproveUserPool::query()
            ->done()
            ->join('approve_pool_log', 'approve_pool_log.approve_user_pool_id', 'approve_user_pool.id');

        if (!empty($condition['approve_time'])) {
            $approveTime = explode(',', $condition['approve_time']);
            if (!empty($approveTime[0])) {
                $query->where('approve_user_pool.created_at', '>=', date('Y-m-d H:i:s', (int)$approveTime[0]));
            }
            if (!empty($approveTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($approveTime[0] ?? 0, $approveTime[1]);
                $query->where('approve_user_pool.created_at', '<=', date('Y-m-d H:i:s', $nextDay));
            }
        }

        $query->where('approve_user_pool.admin_id', $condition['admin_id']);
        $query->where('approve_pool_log.type', $condition['approve_type']);

        return $query;
    }

    /**
     * @return array
     */
    protected function statusMap()
    {
        return [
            static::SORT_PASS => ApproveUserPool::SORT_PASS,
            static::SORT_RETURN => ApproveUserPool::SORT_RETURN,
            static::SORT_REJECT => ApproveUserPool::SORT_REJECT,
            static::SORT_MISSED => ApproveUserPool::SORT_MISSED,
        ];
    }

    /**
     * @param $condition
     * @return array
     */
    public function userStatisticSummary($condition)
    {
        $query = $this->buildQuery($condition);
        $statisticData = $query->select([
            'approve_user_pool.admin_id',
            'approve_pool_log.type',
            'approve_user_pool.status',
        ])
            ->done()
            ->get()
            ->toArray();

        $statistic = current($this->getStatisticSummary($statisticData));

        $list = [
            [
                'key' => static::SORT_PASS,
                'val' => $statistic['pass'],
                'text' => 'Passed Case',
            ],
            [
                'key' => static::SORT_RETURN,
                'val' => $statistic['return'],
                'text' => 'Retured Case',
            ],
            [
                'key' => static::SORT_REJECT,
                'val' => $statistic['reject'],
                'text' => 'Rejected Case',
            ],
            [
                'key' => static::SORT_MISSED,
                'val' => $statistic['missed'],
                'text' => 'Missed Case',
            ],
        ];

        return $list;

    }

}
