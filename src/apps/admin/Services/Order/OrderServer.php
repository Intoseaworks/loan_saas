<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:06
 */

namespace Admin\Services\Order;


use Admin\Exports\Order\OrderExport;
use Admin\Exports\Repayment\ManualRepaymentExport;
use Admin\Models\Approve\Approve;
use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\User\User;
use Admin\Services\Config\ConfigServer;
use Admin\Services\Data\DataServer;
use Admin\Services\Risk\RiskServer;
use Common\Models\Approve\ApprovePool;
use Common\Services\Order\OrderCheckServer;
use Illuminate\Support\Facades\DB;
use Common\Models\Crm\SmsTemplate;
use Common\Utils\Sms\SmsEgpHelper;
use Common\Models\Merchant\App;
use Illuminate\Support\Facades\Cache;
use Common\Models\Trade\TradeLog;

class OrderServer extends \Common\Services\Order\OrderServer
{
    private $order;

    /**
     * @param null $orderId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($orderId = null)
    {
        if ($orderId !== null && !$this->order = Order::model()->getOne($orderId)) {
            $this->outputException('订单数据不存在');
        }
    }

    /**
     * 订单列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params) {
        $query = Order::model()->search($params, ['lastRepaymentPlan.repaymentPlanRenewal']);
        if ($this->getExport()) {
            $this->outputError("Discontinue use here");
            OrderExport::getInstance()->export($query, OrderExport::SCENE_BORROW);
        }

        $size = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->user->telephone = \Common\Utils\Data\StringHelper::desensitization($data->user->telephone, "3", '4');
            $data->user && $data->user->channel && $data->user->channel->getText(['channel_code', 'channel_name']);
            $data->user && $data->user->setScenario(User::SCENARIO_INFO)->getText();
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->addHidden('repaymentPlanRenewal');
        }
        return $datas;
    }

    /**
     * 放款失败列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\Paginator[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function failList($params)
    {
        $datas = Order::model()->getFailList($params)->get();
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->user && $data->user->getText(['telephone', 'fullname']);
            $data->user->userInfo && $data->user->email = $data->user->userInfo->email ?? '';
            $data->lastTradeLog && $data->lastTradeLog
                ->getText(['trade_result_time', 'trade_desc', 'trade_platform']);
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->payout_channel = $data->payment_type=='other' ? $data->channel:$data->payment_type;
            $data->first_failure_time = TradeLog::model()
                    ->where("master_related_id", $data->id)
                    ->where('trade_result', TradeLog::TRADE_RESULT_FAILED)
                    ->where("trade_evolve_status", TradeLog::TRADE_EVOLVE_STATUS_OVER)
                    ->where("business_type", TradeLog::BUSINESS_TYPE_MANUAL_REMIT)
                    ->orderBy("id")->first();
            if($data->first_failure_time){
                $data->first_failure_time = $data->first_failure_time->created_at->toDateTimeString();
            }
        }
        return $datas;
    }

    /**
     * 人工放款列表
     *
     * @param $params
     * @param bool $export
     * @return OrderServer
     */
    public function manualList($params, $export = false)
    {
        $query = Order::model()->getManualList($params);
        if ($export) {
            ManualRepaymentExport::getInstance()->export($query, ManualRepaymentExport::SCENE_MANUAL_REPAYMENT_LIST);
        }

        $size = array_get($params, 'size');
        $datas = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data Order */
            $data->setScenario(Order::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['telephone', 'fullname']);
            $data->lastRepaymentPlan && $data->lastRepaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
            unset($data->repaymentPlans);
        }
        return $this->outputSuccess('', $datas);
    }

    /**
     * 批量取消订单
     * @param $params
     * @return OrderServer|bool
     */
    public function cancelBatch($params)
    {
        $ids = (array)array_get($params, 'ids');
        $orderStatus = array_merge(Order::WAIT_CONFIRM_PAY_STATUS,Order::PAY_FAIL_STATUS);
        $orders = Order::whereIn('status', $orderStatus)->whereIn('id', $ids)->get();
        if (count($orders) != count($ids)) {
            return $this->outputError('订单中有非失败订单');
        }
        $smsTemplate = null;
        if (isset($params['sms_tpl_id']) && $params['sms_tpl_id']) {
            $smsTemplate = SmsTemplate::model()->getOne($params['sms_tpl_id']);
        }
        DB::transaction(function () use ($orders, $smsTemplate) {
            foreach ($orders as $order) {
                if (!$this->systemCancel($order, $order->status)) {
                    $this->outputException('更新订单失败');
                }
                if ($smsTemplate) {
                    $appId = App::model()->where("merchant_id", $order->merchant_id)->get()->first()->id;
                    $sendId = App::getDataById($appId, 'send_id');
                    SmsEgpHelper::helper()->sendMarketing($order->user->telephone, $smsTemplate->tpl_content, [], $sendId);
                }
            }
            return true;
        });

        return true;
    }

    /**
     * 批量流回待放款
     * @param $params
     * @return OrderServer|bool
     * @throws \Throwable
     */
    public function toWaitRemitBatch($params)
    {
        $ids = (array)array_get($params, 'ids');
        $orders = Order::whereIn('status', Order::PAY_FAIL_STATUS)->whereIn('id', $ids)->get();
        if (count($orders) != count($ids)) {
            return $this->outputError('订单中有非失败订单');
        }

        DB::transaction(function () use ($orders,$params) {
            foreach ($orders as $order) {
                if (!$this->manualPayFailToManualPass($order->id)) {
                    $this->outputException('更新订单失败');
                }else{
                    //后台更新放款渠道
                    if ( isset($params['payment_type']) && $params['payment_type']){
                        $order->pay_channel = $params['payment_type'];
                        $order->save();
                    }
                }
            }
            return true;
        });

        return true;
    }

    /**
     * 订单详情
     * @return array
     */
    public function view()
    {
        $tabs = [
            DataServer::ORDER,
            DataServer::USER,
//            RiskServer::CONTACTS,
            DataServer::ORDER_LIST,
//            RiskServer::OPERATOR_REPORT,
            DataServer::BANK_CARDS,
//            RiskServer::USER_POSITION,
//            RiskServer::USER_SMS,
            RiskServer::USER_APP,
        ];
        return DataServer::server($this->order->user_id, $this->order->id)->list($tabs);
    }

    /**
     * @return Order|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * 判断能否进行人工放款
     * @param $orderIds
     * @return OrderServer
     * @throws \Exception
     */
    public function canManualRemit($orderIds)
    {
        $orders = Order::whereIn('id', $orderIds)->get();

        $payAmount = 0;
        foreach ($orders as $order) {
            /** @var $order Order */
            if (!in_array($order->status, Order::WAIT_SIGN)) {
                return $this->outputError('订单状态不正确！订单号为:'.$order->order_no);
            }

            $payAmount += $order->getPaidAmount(false);

//        if (!$this->canExecRemit($this->order)) {
//            return $this->outputError('订单暂不能放款');
//        }
        }

        // 检查是否超过日最大放款金额
        if (OrderCheckServer::server()->reachMaxLoanAmount($payAmount)) {
            return $this->outputError('超过日最大放款金额！');
        }

        return $this->outputSuccess('', $orders);
    }

    /**
     * 判断能否进行人工还款
     * @return Order|OrderServer|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function canManualRepayment()
    {
        $this->order->refresh();

        if (!in_array($this->order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return $this->outputError('订单状态不正确');
        }

        return $this->outputSuccess('', $this->order);
    }

    public function rejectToPass($approvePoolType)
    {
        $order = $this->order;
        if (!in_array($order->status, [Order::STATUS_MANUAL_REJECT, Order::STATUS_SYSTEM_CANCEL])) {
            return $this->outputException('订单状态不正确');
        }
        $lastOrder = Order::query()->where('user_id', $order->user_id)->orderBy('id', 'desc')->first();
        if ($order->id != $lastOrder->id) {
            return $this->outputException('用户已创建新订单');
        }
        $lastOrderLog = $order->lastOrderLog;
        if (!$lastOrderLog || !in_array($lastOrderLog->from_status, array_keys(\Common\Models\Order\Order::STATUS_ALIAS))) {
            return $this->outputException('订单日志状态不正确');
        }
        $approveBeforeStatus = $lastOrderLog->from_status;
        # 初审
        if (in_array($approveBeforeStatus, [Order::STATUS_WAIT_MANUAL_APPROVE]) && $approvePoolType == ApprovePool::ORDER_FIRST_GROUP) {
            $userApproveConfig = ConfigServer::server()->getUserApproveConfig($order);
            if (in_array(Approve::PROCESS_CALL_APPROVAL, $userApproveConfig)) {
                OrderServer::server()->rejectManualToCall($order->id);
//                dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_APPROVE_QA_TO_CALL));
            } else {
                // 不需要电审,直接通过
                OrderServer::server()->rejectManualToPass($order->id);
//                dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_APPROVE_QA_TO_PASS));
            }
        } # 电审
        else if (in_array($approveBeforeStatus, Order::APPROVAL_CALL_STATUS) && $approvePoolType == ApprovePool::ORDER_CALL_GROUP) {
            OrderServer::server()->rejectCallToPass($this->order->id);
//            dispatch(new SmsByCommonJob($order->user_id, SmsHelper::EVENT_APPROVE_QA_TO_PASS));
        } # 其他状态
        else {
            return $this->outputException('该审批单无法修改订单状态');
        }
    }

    public function getMaxOverdueDays($userId) {
        return RepaymentPlan::getByUserId($userId)->max('overdue_days');
    }

    public function prevOrder($order) {
        return Order::model()->where('user_id', $order->user_id)->where("id", "<", $order->id)->whereIn('status', Order::FINISH_STATUS)->first();
    }

}
