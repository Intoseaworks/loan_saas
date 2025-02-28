<?php

namespace Admin\Services\NewApprove;

use Admin\Exports\Approve\ApproveExport;
use Admin\Models\Approve\Approve;
use Admin\Models\Order\Order;
use Admin\Models\Staff\Staff;
use Admin\Services\BaseService;
use Admin\Services\Staff\StaffServer;
use Approve\Admin\Config\Params;
use Approve\Admin\Services\Log\ApproveLogService;
use Approve\Admin\Services\Rule\ApproveRuleService;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\SystemApprove\SystemApproveTask;
use Common\Utils\Data\ArrayHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class NewApproveServer extends BaseService
{
    /**
     * 被拒订单列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator
     */
    public function rejectList($params)
    {
        $size = array_get($params, 'size');
        $with = ['manualApproveLog'];
        $query = Order::model()->search($params, $with);
        #审批人员
        if ($approveIds = array_get($params, 'approve_ids')) {
            $query->whereHas('manualApproveLog', function (Builder $query) use ($approveIds) {
                $query->whereIn('admin_id', (array)$approveIds);
            });
        }

        if ($this->getExport()) {
            ApproveExport::getInstance()->export($query, ApproveExport::SCENE_REJECT_LIST);
        }

        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($orders = $query->paginate($size) as $order) {
            /** @var Order $order */
            $order->user && $order->user->getText(['telephone', 'fullname', 'quality']);
            $order->setScenario(Order::SCENARIO_LIST)->getText();
            $order->reject_reason = ts($this->getNewRejectReasonText($order), 'approve');
            $approverId = $this->getRejectApproverIdByOrderId($order->id);
            $order->approver = Staff::model()->getNameById($approverId) ?? '---';
            $order->addHidden('channel', 'orderDetails');

            $order->user->telephone = \Common\Utils\Data\StringHelper::desensitization($order->user->telephone);
        }
        return $orders;
    }

    /**
     * 获取列表被拒原因文案描述
     * @param $order
     * @return mixed|string
     */
    public function getNewRejectReasonText($order)
    {
        $allRefusalCode = Params::allRefusalCode();
        $key = $order->refusal_code;
        return $reasons = ['remark'=>isset($allRefusalCode[$key])?$allRefusalCode[$key]:null];
//        switch ($order->status) {
//            case Order::STATUS_SYSTEM_REJECT:
//                $reasons = ['remark'=>ApproveRuleService::server()->getOperationByRule($order->refusal_code)];
////                $reasons = ['remark'=>$order->status_text];
//                break;
//            case Order::STATUS_MANUAL_REJECT:
//                $reasons = ['remark'=>ApproveRuleService::server()->getOperationByRule($order->refusal_code)];
////                $reasons = ArrayHelper::jsonToArray(optional(ApproveLogService::getInstance()->getFirstLogInstance($order->id, ManualApproveLog::APPROVE_TYPE_CALL))->remark);
//                break;
//        }
//        return $reasons ?? [];
    }

    /**
     * 获取列表被拒原因文案描述--新
     * @param $orderId
     * @param $status
     * @return mixed|string
     */
    public function getRejectReasonText($orderId, $status)
    {
        switch ($status) {
            case Order::STATUS_SYSTEM_REJECT:
                $reasons = collect(SystemApproveTask::getRejectReason($orderId))
                    ->unique()
                    ->slice(0, 3)
                    ->values()
                    ->toArray();
                break;
            case Order::STATUS_MANUAL_REJECT:
                $reasons = $this->getManualApproveSelect($orderId);
                if ($reasons && in_array('Other exception', $reasons)) {
                    if ($remark = $this->getManualApproveRemark($orderId)) {
                        $reasons[] = $remark;
                    }
                }
                break;
        }
        return $reasons ?? [];
    }

    /**
     * 获取人审被拒原因详情
     * @return array
     */
    public function getRejectReasonDetail($orderId)
    {
        if (!$orderId || !$order = Order::model()->getOne($orderId)) {
            $this->outputException('订单数据不存在');
        }
        /** 人审 */
        $orderId = $order->id;
        if ($order->status == Order::STATUS_MANUAL_REJECT) {
            $result = $this->buildManualRejectReasons();
            if (!$reasons = $this->getApproveSelect($orderId)) {
                return ArrayHelper::arrToOption($result, 'label', 'value');
            }
            $reasons = array_values(array_only(Approve::SELECT, $reasons));
            foreach ($result as $key => &$val) {
                foreach ($reasons as $reason) {
                    if (strpos($reason, $key) !== false) {
                        $val = Approve::RESULT_REJECTED;
                    }
                }
            }
            return ArrayHelper::arrToOption($result, 'label', 'value');
        } else {
            /** 机审 */
            $reason = SystemApproveTask::getRejectReason($orderId);
            $result = [];
            foreach ($reason as $item) {
                isset($item['reason']) && $result[$item['reason']] = Approve::RESULT_REJECTED;
            }
            return ArrayHelper::arrToOption($result, 'label', 'value');
        }
    }

    /**
     * 获取拒批订单审批人id
     * @param $orderId
     * @return mixed
     */
    public function getRejectApproverIdByOrderId($orderId)
    {
        return ManualApproveLog::query()->whereOrderId($orderId)
            ->whereIn('name', ManualApproveLog::NAME_REJECT)
            ->orderByDesc('id')
            ->value('admin_id');
    }

    /**
     * 获取审批人列表
     * @return mixed
     */
    public function getApproverList()
    {
        $merchantId = \Common\Utils\MerchantHelper::getMerchantId();
        return Cache::remember("NewApproveServer::getApproverList::{$merchantId}".date("Ymd"), 30, function () {
            $ids = $this->getApproverIds();
            $adminList = StaffServer::server()->getByIds($ids)->pluck('nickname', 'id');
            return $adminList;
        });
    }

    /**
     * 获取审批人ids列表
     * @return ManualApproveLog[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getApproverIds()
    {
        # 优化查询 Distinct性能差
        return ManualApproveLog::query()->select('admin_id')->groupBy("admin_id")->get();
        # return ManualApproveLog::query()->distinct()->select('admin_id')->get();
    }

    /**
     * 构建人审项文案详情
     * @return array
     */
    public function buildManualRejectReasons()
    {
        return [
            '人审风险报告' => Approve::RESULT_PASS,
            '机审详情' => Approve::RESULT_PASS,
            '基本信息' => Approve::RESULT_PASS,
            '运营商报告' => Approve::RESULT_PASS,
            '多头报告' => Approve::RESULT_PASS,
            '通讯录' => Approve::RESULT_PASS,
            '短信记录' => Approve::RESULT_PASS,
            '借款信息' => Approve::RESULT_PASS,
            '银行卡' => Approve::RESULT_PASS,
            '位置信息' => Approve::RESULT_PASS,
            'APP应用列表' => Approve::RESULT_PASS,
        ];
    }

    public function getManualApproveSelect($orderId)
    {
        $result = ManualApproveLog::query()->where('order_id', $orderId)
            ->whereIn('name', ManualApproveLog::NAME_REJECT)
            ->value('result');

        $return = json_decode($result, true);

        return $return;
    }

    public function getManualApproveRemark($orderId)
    {
        $remark = ManualApproveLog::query()->where('order_id', $orderId)
            ->whereIn('name', ManualApproveLog::NAME_REJECT)
            ->value('remark');

        return $remark;
    }

    /**
     * 获取人工审批结果
     * @param $orderId
     * @param string $status
     * @return mixed|string
     */
    public function getApproveSelect($orderId, $status = Approve::RESULT_REJECTED)
    {
        return Approve::model()->whereOrderId($orderId)
            ->whereResult($status)
            ->value('approve_select');
    }
}
