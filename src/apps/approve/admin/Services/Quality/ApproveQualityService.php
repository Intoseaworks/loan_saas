<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-08
 * Time: 16:24
 */

namespace Approve\Admin\Services\Quality;


use Admin\Services\Order\OrderServer;
use Approve\Admin\Services\Approval\CallApproveService;
use Approve\Admin\Services\Approval\FirstApproveService;
use Approve\Admin\Services\Check\ApproveCheckService;
use Approve\Admin\Services\CommonService;
use Common\Exceptions\CustomException;
use Common\Interfaces\ApproveQualityHandlerInterface;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ApproveQuality;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Approve\ApproveUserPool;
use Common\Traits\GetInstance;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;

class ApproveQualityService
{
    use GetInstance;

    /**
     * @param array $condition
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|ApprovePool[]
     * @throws CustomException
     */
    public function getList($condition = [])
    {
        $query = ApproveQuality::query();
//        $query->withoutGlobalScopes();

        if (!empty($condition['approve_time'])) {
            $qualityTime = $condition['approve_time'];
            if (!empty($qualityTime[0])) {
                $query->where('finish_at', '>=', $qualityTime[0]);
            }
            if (!empty($qualityTime[1])) {
                $nextDay = CommonService::getInstance()->getNextDay($qualityTime[0] ?? 0, $qualityTime[1]);
                $query->where('finish_at', '<=', $nextDay);
            }
        }

        if (!empty($condition['order_no'])) {
            $query->where('order_no', $condition['order_no']);
        }

        if (!empty($condition['user'])) {
            $userId = CommonService::getInstance()->getUserId($condition['user']);
            $query->whereIn('user_id', $userId);
        }

        if (!empty($condition['approve_result'])) {
            $query->where('approve_user_pool.status', $condition['approve_result']);
        }

        if (!empty($condition['quality_status'])) {
            $query->where('quality_status', $condition['quality_status']);
        }

        $perPage = !empty($condition['per_page']) ? $condition['per_page'] : 10;

        $rows = $query->select(
            \DB::raw('approve_quality.id,
                approve_pool.order_no,
                approve_pool.user_id,
                approve_pool.telephone,
                approve_user_pool.finish_at,
                max(approve_pool_log.order_status) as order_status,
                approve_user_pool.status,
                approve_quality.quality_status,
                approve_quality.quality_result,
                approve_quality.admin_id,
                approve_pool.order_id')
        )
            ->leftJoin('approve_user_pool', 'approve_user_pool.id', 'approve_quality.approve_user_pool_id')
            ->leftJoin('approve_pool', 'approve_pool.id', 'approve_user_pool.approve_pool_id')
            ->leftJoin('approve_pool_log', 'approve_pool_log.approve_user_pool_id', 'approve_user_pool.id')
//            ->where('approve_quality.merchant_id', MerchantHelper::getMerchantId())
            ->whereIn('approve_user_pool.status', ApproveUserPool::CAN_QUALITY_STATUS)
            ->orderBy('id', 'DESC')
            ->groupBy('approve_quality.id')
//            ->having('approve_user_pool.status',max(['status']))
            ->paginate($perPage);
//        dd($query);

//        dd(\DB::table(\DB::raw("({$query->toSql()}) as questionstock"))
//            ->groupBy('id')
//            ->orderBy('status', 'desc')
//            ->mergeBindings($query->getQuery())->toSql());
//        $rows = \DB::table(\DB::raw("({$query->toSql()}) as questionstock"))
//            ->groupBy('id')
//            ->orderBy('id', 'desc')
//            ->mergeBindings($query->getQuery())        //注意这里需要合并绑定参数
//            ->paginate($perPage);
        $data = [];
        $userIds = $rows->pluck('user_id')
            ->unique()
            ->filter()
            ->toArray();
        $adminUserIds = $rows->pluck('admin_id')
            ->unique()
            ->filter()
            ->toArray();
        if ($userIds) {
            $userInfo = CommonService::getInstance()->getUserInfo($userIds);
        }
        if ($adminUserIds) {
            $adminUserInfo = CommonService::getInstance()->getAdminUserInfo($adminUserIds);
        }

        $approvePool = new ApprovePool();
        $approveUserPool = new ApproveUserPool();
        $approveQuality = new ApproveQuality();
        $rows = $rows->toArray();
//        dd($rows['data']);
        foreach ($rows['data'] as $row) {
            $row = (array)$row;
            $temp = [];
            $temp['id'] = $row['id'];
            $temp['order_no'] = $row['order_no'];
            $temp['order_id'] = $row['order_id'];
            $temp['user_name'] = $userInfo[$row['user_id']]['fullname'] ?? '';
            $temp['telephone'] = \Common\Utils\Data\StringHelper::desensitization($row['telephone']);
            $temp['approve_time'] = DateHelper::dateFormatByEnv($row['finish_at']);
            $temp['order_status'] = $approvePool->getOrderStatusText($row['order_status']);
            $temp['approve_result'] = t($approveUserPool->getStatusText($row['status']), 'approve');
            $temp['quality_status'] = $approveQuality->getQualityStatusText($row['quality_status']);
            $temp['quality_result'] = $approveQuality->getQualityResultText($row['quality_result']);
            $temp['quality_user_name'] = $adminUserInfo[$row['admin_id']]['nickname'] ?? '';
            $data[] = $temp;
        }

        $rows['data'] = $data;
//        dd($rows);

        return $rows;
    }

    /**
     * @return array
     */
    public function approveResultList()
    {
        $list = [
            0 => t('全部', 'approve'),
            ApproveUserPool::STATUS_FIRST_PASS => 'First approval pass',
            ApproveUserPool::STATUS_FIRST_RETURN => 'First approval return',
            ApproveUserPool::STATUS_FIRST_REJECT => 'First approval reject',
            ApproveUserPool::STATUS_CALL_PASS => 'Call approval pass',
            ApproveUserPool::STATUS_CALL_RETURN => 'Call approval return',
            ApproveUserPool::STATUS_CALL_REJECT => 'Call approval reject',
            ApproveUserPool::STATUS_NO_ANSWER => 'Call approval No answer',
        ];
        $list = ts($list, 'approve');
        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * @return array
     */
    public function qualityStatusList()
    {
        $list = (new ApproveQuality())->getQualityStatusList();
        $all = [0 => t('全部', 'approve')];
        return ArrayHelper::arrToOption($all + $list, 'key', 'val');
    }

    /**
     * @param $qualityId
     * @return array
     * @throws CustomException
     */
    public function detail($qualityId)
    {
        $data = [];
        /** @var ApproveQuality $quality */
        $quality = ApproveQuality::where('id', $qualityId)->first();
        if (!$quality) {
            throw new CustomException(t('data not exists'));
        }

        $data['orderStatus'] = '';
        $data['approveType'] = 1;

        /** 质检不取快照数据 避免数据格式混乱 */
        //$snapshoots = $this->getSnapShot($quality->approve_user_pool_id, $quality->approveUserPool->approve_pool_id);
        //CommonService::getInstance()->getSnapShootApproveResultByUserPoolId($quality->approveUserPool->id, $snapshoots);
        $baseDetail = FirstApproveService::getInstance($quality->approveUserPool->order_id)->bashDetail();
        $baseDetail = array_except($baseDetail, ['base_detail_status', 'fullname_radio_list', 'id_card_radio_list', 'refusal_code', 'suspected_fraud_status', 'user_face_comparison_selector']);
        $callDetail = CallApproveService::getInstance($quality->approveUserPool->order_id)->detail();
        $callDetail = array_except($callDetail, ['call_approve_result', 'contact_relation', 'refusal_code']);
        $callDetail['education_level'] = \Common\Models\Common\Dict::getNameByCode($callDetail['education_level']);
        $data['firstDetail'] = $baseDetail ?? null;
        $data['callDetail'] = $callDetail ?? null;

        // 获取订单状态
        $approvePoolLog = ApprovePoolLog::where('approve_user_pool_id', $quality->approve_user_pool_id)->first();
        if ($approvePoolLog) {
            $data['orderStatus'] = $approvePoolLog->getOrderStatusText();
            $data['approveType'] = $approvePoolLog->type;
        }

        $adminUserId = [
            $quality->approveUserPool->admin_id,
            $quality->admin_id,
        ];
        $adminUserInfo = CommonService::getInstance()->getAdminUserInfo($adminUserId);
        $data['approveUser'] = $adminUserInfo[$quality->approveUserPool->admin_id]['nickname'] ?? '';
        $data['approveTime'] = DateHelper::dateFormatByEnv($quality->approveUserPool->finish_at);

        $data['qualityResult'] = $quality->getQualityResultText();
        $data['qualityUser'] = $adminUserInfo[$quality->admin_id]['nickname'] ?? '';
        $data['qualityDone'] = $quality->quality_status == ApproveQuality::QUALITY_STATUS_DONE ? true : false;
        $data['remark'] = $quality->remark ?? '';

        $approveResult = CommonService::getInstance()->getApproveResult($quality->approveUserPool->approve_pool_id);
        return $data + $approveResult;

    }

    /**
     * @return array
     */
    public function qualityResultList()
    {
        $list = (new ApproveQuality())->getQualityResultList();
        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * @param $data
     * @return bool
     * @throws CustomException
     */
    public function qualitySubmit($data)
    {
        $id = array_get($data, 'id');
        $quality = ApproveQuality::query()->where('id', $id)->first();
        if (!$quality) {
            throw new CustomException('data no exists');
        }
        if ($quality->quality_status == ApproveQuality::QUALITY_STATUS_DONE) {
            throw new CustomException('The quality check has been completed');
        }
        $qualityResult = array_get($data, 'quality_result');
        $passOrder = array_get($data, 'pass_order');
        if($passOrder && $qualityResult == ApproveQuality::QUALITY_RESULT_1){
            $this->rejectToPass($quality);
        }
        $quality->setQualityResult($data);
        $quality->saveQualityResult();
    }

    public function rejectToPass(ApproveQuality $quality)
    {
        $approveUserPool = ApproveUserPool::query()->where('id', $quality->approve_user_pool_id)->first();
        if(!$approveUserPool){
            throw new CustomException('user pool no exists');
        }
        $approvePool = ApprovePool::query()->where('id', $approveUserPool->approve_pool_id)->first();
        if(!$approvePool){
            throw new CustomException('approve pool no exists');
        }
        return OrderServer::server($approveUserPool->order_id)->rejectToPass($approvePool->type);
    }

    protected function getSnapShot($userPoolId, $poolId)
    {
        $snapshoots = ApproveResultSnapshot::where('approve_pool_id', $poolId)
            ->orderBy('id', 'ASC')
            ->get()
            ->groupBy('approve_type');

        $data = [];
        foreach ($snapshoots as $k => $item) {
            $rows = $item->keyBy('approve_user_pool_id');
            if ($k == ApproveResultSnapshot::TYPE_FIRST_APPROVE) {
                if (isset($rows[$userPoolId])) {
                    $data['firstDetail'] = json_decode($rows[$userPoolId]->result, true);
                } else {
                    $data['firstDetail'] = json_decode($item->last()->result, true);
                }
            }
            if ($k == ApproveResultSnapshot::TYPE_CALL_APPROVE) {
                if (isset($rows[$userPoolId])) {
                    $data['callDetail'] = json_decode($rows[$userPoolId]->result, true);
                } else {
                    $data['callDetail'] = json_decode($item->last()->result, true);
                }
            }
        }

        ApproveCheckService::getInstance()->getOssUrl($data['firstDetail']);

        return $data;
    }
}
