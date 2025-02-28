<?php

namespace Api\Controllers\Callback;

use Api\Services\Common\CallbackServer;
use Api\Services\Order\OrderServer;
use Common\Models\Trade\TradeLog;
use Common\Response\ServicesApiBaseController;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;

class PayController extends ServicesApiBaseController
{
    public function htmlPay()
    {
        $params = $this->request->post();
        try {
            DB::beginTransaction();

            $tradeLog = TradeLog::getByTransactionNo($params['transactionNo']);

            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            $this->validateSign();

            if (!$tradeLog || !$tradeLog->order) {
                DingHelper::notice(var_export($params, true), 'htmlpay回调，交易记录未找到');
                exit('交易记录未获取到');
            }

            // 加排他锁
            $tradeLog = TradeLog::query()->where('id', $tradeLog->id)->lockForUpdate()->first();

            if ($params['status'] == 'SUCCESS') {
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
            } else {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
            }

            CallbackServer::server()->finishTrade($tradeLog, $params);
            DB::commit();
            exit('SUCCESS');
        } catch (\Exception $e) {
            DB::rollBack();
            DingHelper::notice(var_export($params, true) . "\n" . $e->getMessage(), 'htmlpay回调报错');
            exit($e->getMessage());
        }
    }

    /**
     * 验签
     */
    protected function validateSign()
    {
        $params = $this->request->post();
        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        $checkSign = SignHelper::validateSign($params, $config['app_secret_key']);

        if (!$checkSign) {
            EmailHelper::send("route:" . request()->getRequestUri() . "\n" . json_encode($params), 'htmlpay回调验签失败');
            exit('验签错误');
        }
    }

    /**
     * 支付结果重定向页
     */
    public function htmlRedirect()
    {
        $transactionNo = $this->getParam('transaction_no');
        $result = $this->getParam('result');
        $domain = HostHelper::getDomain();

        $tradeLog = TradeLog::getByTransactionNo($transactionNo);

        MerchantHelper::setMerchantId($tradeLog->merchant_id);
        // 使用锁读取
        $tradeLog = TradeLog::query()->where('id', $tradeLog->id)->sharedLock()->first();

        if ($result == 'SUCCESS') {
            // 支付成功重定向中修改订单状态为还款中，防止重复还款
            if (!$tradeLog->isOver() && $tradeLog->order) {
                OrderServer::server()->repaying($tradeLog->order->id);
            }

            header("Location: {$domain}/app/callback/return-success");
            exit();
        }
        if ($result == 'FAILED') {
            header("Location: {$domain}/app/callback/return-fail");
            exit();
        }
        exit();
    }

    public function returnSuccess()
    {
        return 'success';
    }

    public function returnFail()
    {
        return 'fail';
    }
}
