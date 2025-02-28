<?php

namespace Admin\Services\Repayment;

use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\Trade\TradeLog;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\Collection\CollectionServer;
use Admin\Services\Order\OrderServer;
use Carbon\Carbon;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Trade\TradeLogDetail;
use Common\Redis\CollectionStatistics\CollectionStatisticsRedis;
use Common\Services\NewClm\ClmServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\LoginHelper;
use Illuminate\Support\Facades\DB;
use Common\Services\Repay\RepayServer;

class ManualRepaymentServer extends BaseService {

    public function list($params) {
        $server = OrderServer::server()->manualList($params, $this->getExport());

        if ($server->isError()) {
            return $this->outputError($server->getMsg());
        }

        return $this->outputSuccess('', $server->getData());
    }

    public function detail($id) {
        $orderServer = OrderServer::server($id)->canManualRepayment();

        if ($orderServer->isError()) {
            return $this->outputError($orderServer->getMsg());
        }

        /** @var Order $order */
        $order = $orderServer->getData();
        $order->setScenario(Order::SCENARIO_LIST)->getText();
        $order->lastRepaymentPlan && $order->lastRepaymentPlan->setScenario(RepaymentPlan::SCENARIO_LIST)->getText();
        $order->user && $order->user->setScenario(User::SCENARIO_SIMPLE)->getText();
        $order->collection_staff = null;
        $order->collection && $order->collection_staff = optional($order->collection->staff)->username;

        return $this->outputSuccess('', $order);
    }

    public function getOverdueData($orderId, $repayTime, $repayAmount = null, $isPart = false) {
        $orderServer = OrderServer::server($orderId)->canManualRepayment();

        /** @var Order $order */
        $order = $orderServer->getData();
        if ($orderServer->isError()) {
            return $this->outputError($orderServer->getMsg());
        }

        if ($repayTime < Carbon::parse($order->paid_time)->toDateString()) {
            return $this->outputError('还款时间不能小于放款时间！请确认后重新提交');
        }

        $data = $this->calcOverdue($order, $repayTime, null, $repayAmount, $isPart);

        return $this->outputSuccess('', $data);
    }

    /**
     * 根据order计算逾期信息
     * @param Order $order
     * @param $repayTime
     * @param $repaymentPlan
     * @param $realRepayAmount
     * @param bool $isPart
     * @return array
     */
    public function calcOverdue($order, $repayTime, $repaymentPlan = '', $realRepayAmount = null, $isPart = false) {
        if (!$repaymentPlan) {
            $repaymentPlan = $order->firstProgressingRepaymentPlan;
        }
        $repaymentPlan = clone $repaymentPlan;
        $repaymentPlan->repay_time = $repayTime;

        // 逾期金额(包含GST)
        $overdueFeeInclGst = $order->getPenaltyFeeAddGst($repaymentPlan);
        // 应还本息
        $repayAmount = $order->repayAmount($repaymentPlan);
        // 减免金额
        $reductionFee = $order->getReductionFee($repaymentPlan);
        // 真实逾期天数
        $overdueDays = $order->getOverdueDays($repaymentPlan);

        // 不是部分还款 && 设置了$realRepayAmount 需要自动减免，如果真实还款金额小于应还金额，不足部分自动算作减免
        if (!$isPart && isset($realRepayAmount)) {
            $realReductionFee = MoneyHelper::sub($repayAmount, $realRepayAmount);
            $reductionFee = $realReductionFee > 0 ? MoneyHelper::add($realReductionFee, $reductionFee) : $reductionFee;
        }

        $data = [
            // 逾期天数
            'overdue_days' => $overdueDays,
            // 逾期金额+gst
            'overdue_fee' => $overdueFeeInclGst,
            // 逾期金额+gst
            'overdue_fee_incl_gst' => $overdueFeeInclGst,
            // 应还本息
            'receivable_amount' => $repayAmount,
            // 减免金额
            'reduction_fee' => $reductionFee,
        ];
        $order->lastRepaymentPlan->refresh();
        return $data;
    }

    public function newRepaySubmit($params) {
        $orderId = $params['id'];
        $repayDate = DateHelper::timeToDateTime($params['repay_time']);
        $repayAmount = $params['repay_amount'];
        $isPart = $params['is_part'];

        $order = Order::model()->getOne($orderId);
        $user = $order->user;
        $repaymentPlan = $order->lastRepaymentPlan;

        $tradeParams = [
            'remark' => $params['remark'],
            'repay_name' => $user->fullname,
            'repay_telephone' => $params['repay_telephone'],
            'repay_account' => $params['repay_account'],
            'repay_time' => $repayDate ?? date('Y-m-d H:i:s'),
            'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL,
            'repay_amount' => $repayAmount
        ];


        DB::beginTransaction();
        $trade = ManualRepaymentServer::server()->addRepayTradeLog($order, TradeLog::TRADE_PLATFORM_MANUAL, $repayAmount, $tradeParams);
        $result = RepayServer::server($repaymentPlan, $trade)->completeRepay();
        DB::commit();
        if (!$result) {
            return $this->outputError('还款失败');
        }

        return $this->outputSuccess('提交成功');
    }

    /**
     * 人工还款提交还款
     * @param $params
     * @return ManualRepaymentServer
     * @throws \Exception
     */
    public function repaySubmit($params) {
        $orderId = $params['id'];
        $repayDate = DateHelper::timeToDateTime($params['repay_time']);
        $repayAmount = $params['repay_amount'];
        $isPart = $params['is_part'];

        $orderServer = OrderServer::server($orderId)->canManualRepayment();
        if ($orderServer->isError()) {
            return $this->outputError($orderServer->getMsg());
        }
        /** @var Order $order */
        $order = $orderServer->getData();

        if (!$repaymentPlan = $order->firstProgressingRepaymentPlan) {
            return $this->outputError('无进行中的还款计划');
        }
        if ($repayDate < Carbon::parse($order->paid_time)->toDateString()) {
            return $this->outputError('还款时间不能小于放款时间！请确认后重新提交');
        }

        $isFinalRepay = !$isPart;
        // 全部还款动态计算减免金额
        if ($isFinalRepay) {
            $calcRes = $this->calcOverdue($order, $repayDate, $repaymentPlan, $repayAmount, $isPart);
            $receivableAmount = $calcRes['receivable_amount'];
            // 如果还款金额少于 本金的90%，不让进行完结，只能部分还款
            // 需求无此要求，要求去除
//            if ($receivableAmount > 1 && MoneyHelper::compare($repayAmount, $receivableAmount * 0.9) === -1) {
//                return $this->outputError('还款金额不满足正常还款要求，请选择部分还款');
//            }

            RepaymentPlan::model()->updateDeductionFee($repaymentPlan, [
                'reduction_fee' => $calcRes['reduction_fee'],
            ]);
        }

        $repaymentPlanId = $repaymentPlan->id;
        $flowParams = array_merge(
                array_only($params, ['remark', 'repay_name', 'repay_telephone', 'repay_account',]),
                [
                    'repay_time' => $repayDate,
                    'repay_channel' => TradeLog::TRADE_PLATFORM_MANUAL,
                    'repay_amount' => $repayAmount,
                ]
        );

        $result = $this->flowRepaySuccess($order, $flowParams, [$repaymentPlanId], $isFinalRepay, true);
        if (!$result) {
            return $this->outputError('还款失败');
        }

        return $this->outputSuccess('提交成功');
    }

    /**
     * 还款成功处理(order相关逻辑处理)
     * @param $order
     * @param $repaymentPlanIds
     * @param $params
     * @param $isFinalRepay
     * @param $recordTradeLog
     * @return ManualRepaymentServer|bool
     * @throws \Common\Exceptions\ApiException|\Exception
     */
    public function flowRepaySuccess($order, $params, array $repaymentPlanIds, bool $isFinalRepay = false, $recordTradeLog = false) {
        if ($order->isFinished()) {
            return true;
        }
        $repayDateTime = DateHelper::timeToDateTime($params['repay_time']);
        $repayAmount = $params['repay_amount'];
        /** @var \Common\Models\Order\Order $order */
        // 按照还款计划期次来还款
        $repaymentPlans = $order->repaymentPlans->whereIn("id", $repaymentPlanIds)->sortBy('installment_num');

        DB::beginTransaction();

        try {
            if ($recordTradeLog === true) {
                // 管理后台人工还款=>记录交易记录。真实还款已生成，不需要记录
                $this->addRepayTradeLog($order, array_get($params, 'repay_channel', ''), $repayAmount, $params);
            }

            // 是否完结当前催收标识
            $needFinishCollection = false;

            $i = 0;
            $count = $repaymentPlans->count();
            /** @var \Common\Models\Order\RepaymentPlan $repaymentPlan */
            foreach ($repaymentPlans as $repaymentPlan) {
                $i++;

                $overdueDataItem = $this->calcOverdue($order, $repayDateTime, $repaymentPlan);
                // 部分还款逻辑处理
                if (MoneyHelper::compare($repayAmount, $overdueDataItem['receivable_amount']) !== -1 || $isFinalRepay) {
                    $repayAmountItem = $repayAmount;
                    if ($i < $count) {
                        // 没到最后一期，取当期应还，剩下的给下期
                        $repayAmountItem = $overdueDataItem['receivable_amount'];
                    }

                    $repayChannel = array_get($params, 'repay_channel', '');

                    if (!$repaymentPlan->repayFinish($repayDateTime, $repayAmountItem, $repayChannel)) {
                        throw new \Exception('repayment plan finish failure');
                    }

                    $repayAmount = MoneyHelper::sub($repayAmount, $repayAmountItem);
                    // 每期还款计划完结，完结催收
                    $needFinishCollection = true;
                } else {
                    $repaymentPlan->partRepayFinish($repayAmount);

                    if (!$isFinalRepay) {
                        // 前面期次都是部分还款了，剩下的期次不用继续执行
                        break;
                    }
                }
            }

            $order->refresh();

            $orderFinish = false;
            /** @var \Common\Models\Order\RepaymentPlan $firstProgressingRepaymentPlan */
            $firstProgressingRepaymentPlan = $order->firstProgressingRepaymentPlan;
            // 所有还款计划完结 => 订单完结 状态变更
            if (!$firstProgressingRepaymentPlan || !$firstProgressingRepaymentPlan->exists) {
                if (!OrderServer::server()->repayFinish($order, [
                            'overdue_days' => $order->lastRepaymentPlan->overdue_days
                        ])) {
                    // 状态变更失败
                    throw new \Exception('order finish failure');
                }

                $orderFinish = true;
                // 订单完结时，完结催收
                $needFinishCollection = true;
            } else {
                $inOverdue = $firstProgressingRepaymentPlan->inOverdue();
                if (!OrderServer::server()->revertToPaid($order->id, $inOverdue)) {
                    // 状态变更失败
                    throw new \Exception('order status flow failure');
                }
            }
            //逾期完结
            if ($needFinishCollection) {
                //二期直接减免 nio 20200717
                RepaymentPlanServer::server()->reductionRepaySuccess($order);
                CollectionServer::server()->finish($order->id);
            }

            DB::commit();
            //推送，每期推送
            //event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPAY_FINISH));
            event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_ORDER_REPAY));
            if ($orderFinish) {
                // NBFC还款上报
//                event(new NbfcReportEvent($order->id, NbfcReportConfig::REPORT_NODE_REPAY));
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            EmailHelper::sendException($exception, "支付回调问题");
            return false;
        }

        $order->refresh();

        //添加 redis 逾期催收统计
        if ($order->status == Order::STATUS_OVERDUE_FINISH) {
            $this->overdueCollectionStatistics($order);
        }

        if ($order->isFinished()) {
            /** CLM模块更新额度等级 */
            ClmServer::server()->adjustLevel($order);
        }

        return true;
    }

    /**
     * 还款成功处理 trade_log 流转&order相关逻辑处理
     * @param $tradeLog \Common\Models\Trade\TradeLog
     * @param $tradeNo
     * @param $tradeResultTime
     * @param $tradeAmount
     * @return ManualRepaymentServer|bool
     * @throws \Common\Exceptions\ApiException
     */
    public function repaySuccess($tradeLog, $tradeNo, $tradeResultTime, $tradeAmount) {
        $tmpTradeAmount = $tradeAmount;
        if ($tradeLog->business_amount > $tradeLog->trade_amount) {
            $tradeAmount = $tradeAmount + $tradeLog->trade_amount;
        }
        $tradeLog->evolveStatusOverResultSuccess($tradeNo, $tradeResultTime, $tradeResultTime, $tradeAmount);
        $flowParams = [
            'repay_time' => $tradeResultTime,
            'repay_channel' => $tradeLog->trade_platform,
            'repay_amount' => $tmpTradeAmount,
        ];
        $repaymentPlanIds = $tradeLog->tradeLogDetail->pluck('business_id')->toArray();


        if (MoneyHelper::compare($tradeLog->business_amount, $tradeAmount) === 0) {
            $isFinalRepay = $tradeLog->tradeLogDetail->pluck('flag')->contains(TradeLogDetail::FLAG_IS_FINAL_REPAY);
        } else {
            $isFinalRepay = false;
        }
        $result = ManualRepaymentServer::server()->flowRepaySuccess($tradeLog->order, $flowParams, $repaymentPlanIds, $isFinalRepay);
        return $result;
    }

    /**
     * 还款失败处理 trade_log 流转&order相关逻辑处理
     * @param $tradeLog
     * @param $requestNo
     * @param $tradeNo
     * @param $tradeResultTime
     * @return bool
     * @throws \Exception
     */
    public function repayFailed($tradeLog, $requestNo, $tradeNo, $tradeResultTime) {
        $tradeLog->evolveStatusOverResultFailed($requestNo, $tradeNo, $tradeResultTime);

        $result = true;
        if ($tradeLog->order->refresh()->status == Order::STATUS_REPAYING) {
            $result = OrderServer::server()->revertStatus($tradeLog->order->id); // 订单状态流转回之前的
        }

        event(new OrderFlowPushEvent($tradeLog->order, OrderFlowPushEvent::TYPE_DAIKOU_FAILED));
        return $result;
    }

    /**
     * 逾期结清 添加 催收成功统计
     * @param Order $order
     * @return bool
     */
    public function overdueCollectionStatistics($order) {
        CollectionStatisticsRedis::redis()->incr(CollectionStatisticsRedis::KEY_OVERDUE_FINISH_COUNT, $order->merchant_id);

        if ($collection = $order->collection) {
            CollectionStatisticsRedis::redis()->hIncr(
                    $collection->admin_id,
                    CollectionStatisticsRedis::FIELD_STAFF_OVERDUE_FINISH,
                    $order->merchant_id
            );
        }

        return true;
    }

    /**
     * 记录还款交易记录
     * @param Order $order
     * @param $tradePlatform
     * @param $amount
     * @param $otherInfo
     * @return TradeLog
     * @throws \Common\Exceptions\ApiException
     */
    public function addRepayTradeLog(Order $order, $tradePlatform, $amount, $otherInfo) {
        $repayTime = $otherInfo['repay_time'];
        if (!in_array($tradePlatform, TradeLog::TRADE_PLATFORM_HAS_REPAY)) {
            return $this->outputException('交易流水渠道配置缺失');
        }
        $params = [
            'merchant_id' => $order->merchant_id,
            'trade_platform' => $tradePlatform,
            'user_id' => $order->user_id,
            //'admin_trade_account_id' => $adminTradeAccount->id,
            'admin_trade_account_id' => '',
            'master_related_id' => $order->id,
            'master_business_no' => $order->order_no,
            'business_type' => TradeLog::BUSINESS_TYPE_REPAY,
            'trade_type' => TradeLog::TRADE_TYPE_RECEIPTS,
            'request_type' => TradeLog::REQUEST_TYPE_ADMIN,
            'business_amount' => $amount,
            'trade_amount' => $amount,
            //'bank_name' => $adminTradeAccount->bank_name,
            'bank_name' => '',
            'trade_account_telephone' => array_get($otherInfo, 'repay_telephone'),
            'trade_account_name' => array_get($otherInfo, 'repay_name'),
            'trade_account_no' => '',
            'trade_desc' => array_get($otherInfo, 'remark', ''),
            //'trade_result_time' => $params['trade_result_time'],
            //'trade_notice_time' => $params['trade_notice_time'],
            //'trade_settlement_time' => $params['trade_settlement_time'],
            //'trade_request_time' => $params['trade_request_time'],
            'admin_id' => LoginHelper::getAdminId(),
            'handle_name' => LoginHelper::$modelUser['username'] ?? '',
        ];

        $repaymentPlans = RepaymentPlan::getNeedRepayRepaymentPlans($order);
        $detailData = [];
        foreach ($repaymentPlans as $repaymentPlan) {
            $detailData[] = [
                'business_id' => $repaymentPlan->id,
                'business_no' => $repaymentPlan->no,
                'amount' => $order->repayAmount($repaymentPlan),
            ];
        }

        $tradeLog = TradeLog::model()->add($params, $detailData);

        $tradeNo = array_get($otherInfo, 'repay_account');

        return $tradeLog->evolveStatusOverResultSuccess($tradeNo, $repayTime, $repayTime);
    }

    /**
     * 允许还款
     * @param $repaymentPlanId
     * @return $this|bool|void
     */
    public function allowRenewal($repaymentPlanId) {
        if (!($repaymentPlan = RepaymentPlan::model()->getOne($repaymentPlanId))) {
            return $this->outputException(t('还款计划不存在', 'exception'));
        }
        if (!($this->order = $repaymentPlan->order)) {
            return $this->outputException(t('订单数据不存在', 'exception'));
        }

        /** @var $repaymentPlan RepaymentPlan */
        if (!$repaymentPlan->allowRenewal()) {
            return $this->outputError('允许还款设置失败');
        }

        return true;
    }

}
