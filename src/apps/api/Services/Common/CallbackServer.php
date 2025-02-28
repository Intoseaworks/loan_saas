<?php

namespace Api\Services\Common;

use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\Repayment\RepaymentPlanServer;
use Api\Services\BaseService;
use Api\Services\Order\OrderServer;
use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Services\Order\RenewalServer;
use Illuminate\Support\Facades\DB;

use Common\Services\Third\FeixiangServer;
class CallbackServer extends BaseService
{
    /**
     * 根据回调结果完结交易
     * @param $tradeLog
     * @param $noticeInfo
     * @throws \Exception
     */
    public function finishTrade($tradeLog, $noticeInfo)
    {
        switch ($tradeLog->business_type) {
            case TradeLog::BUSINESS_TYPE_REPAY:
                $this->_finishRepay($tradeLog, $noticeInfo);
                break;
            case TradeLog::BUSINESS_TYPE_RENEWAL:
                $this->_finishRenewal($tradeLog, $noticeInfo);
                break;
            default:
                throw new \Exception('trade_log业务类型不正确');
        }
    }

    /**
     * 完结还款
     * @param $tradeLog
     * @param $noticeInfo
     * @throws \Exception
     */
    private function _finishRepay($tradeLog, $noticeInfo)
    {
        try {
            DB::beginTransaction();
            $tradeTime = date('Y-m-d H:i:s', $noticeInfo['tradeTime']);

            // 如果之前没有进入还款中状态，则先流转还款中状态
            if (in_array($tradeLog->order->status, Order::WAIT_REPAYMENT_STATUS)) {
                OrderServer::server()->repaying($tradeLog->order->id);
            }

            if ($noticeInfo['status'] == 'SUCCESS') {
                $result = ManualRepaymentServer::server()->repaySuccess($tradeLog, $noticeInfo['tradeNo'], $tradeTime, $noticeInfo['tradeAmount']);
                
                if (!$result) {
                    throw new \Exception('状态流转失败');
                }
                FeixiangServer::server()->paymentNotify($tradeLog->order, $noticeInfo['tradeAmount'], $tradeLog->transaction_no);
            } else {
                ManualRepaymentServer::server()->repayFailed($tradeLog, $noticeInfo['requestNo'], $noticeInfo['tradeNo'], $tradeTime);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 完结续期
     * @param $tradeLog TradeLog
     * @param $noticeInfo
     * @throws \Exception
     */
    private function _finishRenewal($tradeLog, $noticeInfo)
    {
        try {
            DB::beginTransaction();
            $tradeTime = date('Y-m-d H:i:s', $noticeInfo['tradeTime']);
            if ($noticeInfo['status'] == 'SUCCESS') {
                $tradeLog = $tradeLog->evolveStatusOverResultSuccess($noticeInfo['requestNo'], $tradeTime, $tradeTime, $noticeInfo['tradeAmount']);
                $businessIds = $tradeLog->tradeLogDetail->pluck('business_id')->all();
                RenewalServer::server()->overRenewalResultSuccess($tradeLog->master_related_id, $businessIds);
            } else {
                $tradeLog = $tradeLog->evolveStatusOverResultFailed($noticeInfo['requestNo'], $noticeInfo['tradeNo'], $tradeTime);
                $businessIds = $tradeLog->tradeLogDetail->pluck('business_id')->all();
                RenewalServer::server()->overRenewalResultFailed($tradeLog->master_related_id, $businessIds);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
