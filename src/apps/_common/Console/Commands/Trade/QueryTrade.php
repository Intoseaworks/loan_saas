<?php

namespace Common\Console\Commands\Trade;

use Admin\Services\Repayment\ManualRepaymentServer;
use Admin\Services\TradeManage\RemitServer;
use Api\Services\Order\OrderServer;
use Common\Models\Order\Order;
use Common\Models\Trade\TradeLog;
use Common\Services\Order\RenewalServer;
use Common\Services\Order\TradeLogServer;
use Common\Services\Pay\BasePayServer;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueryTrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade:query-trade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '查询交易中交易结果';

    public function handle()
    {
        /** 代付放款 */
        $this->queryOrder();
    }

    protected function queryOrder()
    {
        // 暂注释，不从支付系统拿结果
        return true;
        /*
        // 商户可用自动代付渠道(使用了支付系统的渠道)
        $autoPayChannel = BasePayServer::server()->getAutoPayChannel();
        if (!$autoPayChannel) {
            return null;
        }
        return true;
        */

        // 暂时只查mobikwik的交易
        $tradeLogs = TradeLogServer::server()->getPayingTrade();
        $reportError = 0;
        foreach ($tradeLogs as $tradeLog) {
            MerchantHelper::setMerchantId($tradeLog->merchant_id);
            try {
                DB::beginTransaction();
                $tradeLog = TradeLog::query()->where('id', $tradeLog->id)->lockForUpdate()->first();
                if (!$tradeLog->isTrading()) {
                    DB::commit();
                    continue;
                }

                $orderPayServer = BasePayServer::server();

                $result = $orderPayServer->executeQueryOrder($tradeLog->transaction_no);

                if (!$result->isSuccess() || !isset($result->getData()['status'])) {
                    $reportError++ > 3 ||
                    DingHelper::notice("交易结果查询失败 transaction_no：{$tradeLog->transaction_no}", 'trade:query-trade 交易结果查询失败');
                    DB::commit();
                    continue;
                }
                $data = $result->getData();

                $tradeLog = TradeLog::getByTransactionNo($data['transactionNo']);
                $this->overTrade($tradeLog, $data);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                EmailHelper::sendException($e, '查询交易结果定时任务报错');
            }
        }
    }

    /**
     * 根据查询结果完结交易
     * @param $tradeLog
     * @param $queryResult
     * @throws \Exception
     */
    protected function overTrade($tradeLog, $queryResult)
    {
        switch ($tradeLog->business_type) {
            case TradeLog::BUSINESS_TYPE_MANUAL_REMIT:
                $this->_finishRemit($tradeLog, $queryResult);
                break;
            case TradeLog::BUSINESS_TYPE_REPAY:
                $this->_finishRepay($tradeLog, $queryResult);
                break;
            case TradeLog::BUSINESS_TYPE_RENEWAL:
                $this->_finishRenewal($tradeLog, $queryResult);
                break;
            default:
                throw new \Exception('trade_log业务类型不正确');
        }
    }

    /**
     * 代扣还款完结
     * @param $tradeLog TradeLog
     * @param $queryResult
     * @throws \Common\Exceptions\ApiException
     * @throws \Exception
     */
    private function _finishRepay($tradeLog, $queryResult)
    {
        $tradeTime = date('Y-m-d H:i:s', $queryResult['tradeTime']);
        //处理结果明确的  成功&失败
        if ($queryResult['status'] == BasePayServer::RESULT_SUCCESS) {
            $result = ManualRepaymentServer::server()->repaySuccess($tradeLog, $queryResult['tradeNo'], $tradeTime, $queryResult['amount']);
            if (!$result) {
                throw new \Exception('状态流转失败');
            }
        } elseif ($queryResult['status'] == BasePayServer::RESULT_FAILED) {
            // 如果之前没有进入还款中状态，则先流转还款中状态
            if (in_array($tradeLog->order->status, Order::WAIT_REPAYMENT_STATUS)) {
                OrderServer::server()->repaying($tradeLog->order->id);
            }
            ManualRepaymentServer::server()->repayFailed($tradeLog, $queryResult['requestNo'], $queryResult['tradeNo'], $tradeTime);
        } elseif ($queryResult['status'] == BasePayServer::RESULT_UNUSED) {
            $tradeLog->evolveStatusToUnused();
        }
        // 结果不明确，不处理
    }

    /**
     * 出款完结
     * @param $tradeLog
     * @param $queryResult
     * @throws \Exception
     */
    private function _finishRemit($tradeLog, $queryResult)
    {
        $tradeTime = date('Y-m-d H:i:s', $queryResult['tradeTime']);
        if ($queryResult['status'] == BasePayServer::RESULT_SUCCESS) {
            $queryResult['tradeAmount'] = array_get($queryResult, 'amount');
            RemitServer::server()->flowRemitSuccess($tradeLog, $queryResult['amount'], $tradeTime, true, array_only($queryResult, ['requestNo', 'tradeNo', 'tradeAmount']));
        } elseif ($queryResult['status'] == BasePayServer::RESULT_FAILED) {
            RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, array_only($queryResult, ['requestNo', 'tradeNo', 'tradeResultCode', 'msg']));
        }
        // 结果不明确，不处理
    }

    /**
     * 续期完结
     * @param $tradeLog TradeLog
     * @param $queryResult
     */
    private function _finishRenewal($tradeLog, $queryResult)
    {
        $tradeTime = date('Y-m-d H:i:s', $queryResult['tradeTime']);
        if ($queryResult['status'] == BasePayServer::RESULT_SUCCESS) {
            $tradeLog = $tradeLog->evolveStatusOverResultSuccess($queryResult['requestNo'], $tradeTime, $tradeTime, $queryResult['amount']);
            $businessIds = $tradeLog->tradeLogDetail->pluck('business_id')->all();
            RenewalServer::server()->overRenewalResultSuccess($tradeLog->master_related_id, $businessIds);
        } elseif ($queryResult['status'] == BasePayServer::RESULT_FAILED) {
            $tradeLog = $tradeLog->evolveStatusOverResultFailed($queryResult['requestNo'], $queryResult['tradeNo'], $tradeTime);
            $businessIds = $tradeLog->tradeLogDetail->pluck('business_id')->all();
            RenewalServer::server()->overRenewalResultFailed($tradeLog->master_related_id, $businessIds);
        }
        // 结果不明确，不处理
    }
}
