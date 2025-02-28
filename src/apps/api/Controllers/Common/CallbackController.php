<?php

namespace Api\Controllers\Common;

use Admin\Services\TradeManage\RemitServer;
use Api\Models\BankCard\BankCard;
use Api\Models\Order\Order;
use Api\Models\User\UserAuth;
use Api\Services\Common\CallbackServer;
use Api\Services\Order\OrderDetailServer;
use Api\Services\Pay\SkyPayServices;
use Api\Services\User\UserAuthServer;
use Common\Models\Trade\TradeLog;
use Common\Response\ApiBaseController;
use Common\Services\Order\OrderServer;
use Common\Utils\Email\EmailHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;
use JMD\App\lumen\Utils;
use JMD\Libs\Services\BaseRequest;
use JMD\Utils\SignHelper;
use Common\Models\Common\Config;

class CallbackController extends ApiBaseController {

    /**
     * 印牛服务鉴权绑卡回调
     */
    public function bankCardAuthBindNotice() {
        $params = $this->request->post();

//        $params = '{"app_key":"KmV72310RbWFcDWc","bank_card_account":"\u738b\u6c99\u4eae","bank_card_no":"6217002920131968100","bank_card_telephone":"15700767943","id_card_no":"430381199701085015","msg":"\u7ed1\u5361\u72b6\u6001\u9519\u8bef\uff0c\u8bf7\u91cd\u8bd5","random_str":"gWHgOrJVpnWTdwNkI0PgpDTI7Yh5rbxz","remark":"saas\u9274\u6743\u7ed1\u5361","request_no":"F07A97C6F1E63182B3136A989C010EDC","request_time":1548381551,"status":"SUCCESS","time":1548381661,"user_id":"10","sign":"f29714e91b26fdbea3aef84c99fe0094"}';
//        $params = json_decode($params, true);

        $userId = BankCard::model()->decodeUserIdentifying($params['user_identifying'])['user_id'];
        $bankcard = BankCard::getWaitAuthBankcard($userId, $params['bank_card_no']);

        MerchantHelper::setMerchantId($bankcard->merchant_id);

        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        $checkSign = SignHelper::validateSign($params, $config['app_secret_key']);

        if (!$checkSign) {
            EmailHelper::send(json_encode($params), '鉴权绑卡-验签失败');
            exit('验签错误');
        }

        if (!$bankcard) {
            EmailHelper::send(json_encode($params), '鉴权绑卡-回调记录不存在');
            exit('银行卡记录不存在');
        }

        if ($params['status'] == 'SUCCESS') {
            $bankcard->updateToAuthSuccess();
            UserAuthServer::server()->setAuth($bankcard->user_id, UserAuth::TYPE_BANKCARD);
            //若用户最后一笔订单状态为放款失败 则更改状态为待放款
            $order = optional(optional($bankcard)->user)->order;
            if ($order && in_array($order->status, Order::PAY_FAIL_STATUS)) {
                OrderServer::server()->manualPayFailToManualPass($order->id);
                //更新最后一笔订单 对应卡号
                OrderDetailServer::server()->saveOrderBank($order);
            }
        }

        exit('SUCCESS');
    }

    /**
     * 代付回调
     */
    public function daifuNotice() {
        try {
            $params = $this->request->post();

            $tradeLog = TradeLog::getByTransactionNo($params['transactionNo']);

            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            $this->validateSign();

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代付出款，交易记录未找到');
                exit('交易记录未获取到');
            }

            $tradeTime = date('Y-m-d H:i:s', $params['tradeTime'] ?: time());
            if ($params['status'] == 'SUCCESS') {
                if ($tradeLog->isSuccess()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
                RemitServer::server()->flowRemitSuccess($tradeLog, $params['amount'], $tradeTime, true, array_only($params, ['requestNo', 'tradeNo', 'tradeAmount']));
            } else {
                if ($tradeLog->isFailed()) {
                    // 回调已处理
                    exit('SUCCESS');
                }
                RemitServer::server()->flowRemitFailed($tradeLog, $tradeTime, true, array_only($params, ['requestNo', 'tradeNo', 'tradeResultCode', 'msg']));
            }
            exit('SUCCESS');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            EmailHelper::send(var_export($params, true) . "\n e:{$msg}", '代付出款报错');
            exit($e->getMessage());
        }
    }

    /**
     * 代扣回调
     */
    public function daikouNotice() {
        $params = $this->request->post();
        try {
            $tradeLog = TradeLog::getByTransactionNo($params['transactionNo']);

            MerchantHelper::setMerchantId($tradeLog->merchant_id);

            $this->validateSign();

            if (!$tradeLog || !$tradeLog->order) {
                EmailHelper::send(var_export($params, true), '代扣回款，交易记录未找到');
                exit('交易记录未获取到');
            }

            DB::beginTransaction();
            CallbackServer::server()->finishTrade($tradeLog, $params);
            DB::commit();
            exit('SUCCESS');
        } catch (\Exception $e) {
            DB::rollBack();
            EmailHelper::send(var_export($params, true) . "\n" . $e->getMessage(), '代扣回款报错');
            exit($e->getMessage());
        }
    }

    /**
     * 验签
     */
    protected function validateSign() {
        $params = $this->request->post();
        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        $checkSign = SignHelper::validateSign($params, $config['app_secret_key']);

        if (!$checkSign) {
            EmailHelper::send("route:" . request()->getRequestUri() . "\n" . json_encode($params), '回调验签失败');
            exit('验签错误');
        }
    }

    /**
     * skyPay 出款成功通知 - GCash, bank
     */
    public function skyPayPayoutQueuePayout() {
        MerchantHelper::setMerchantId(1);
        return SkyPayServices::server()->payoutQueuePayout();
    }

    /**
     * skyPay 还款成功通知
     */
    public function skyPayCollectionCollect() {
        MerchantHelper::setMerchantId(1);
        return SkyPayServices::server()->collectionCollect();
    }

    /**
     * skyPay 出款验证
     */
    public function skyPayPayoutInquiry() {
        MerchantHelper::setMerchantId(1);
        return SkyPayServices::server()->payoutInquiry();
    }

    /**
     * skyPay 出款成功 线下
     */
    public function skyPayPayoutPayout() {
        MerchantHelper::setMerchantId(1);
        return SkyPayServices::server()->payoutPayout();
    }

    /**
     * skyPay 还款验证
     */
    public function skyPayCollectionInquiry() {
        MerchantHelper::setMerchantId(1);
        return SkyPayServices::server()->collectionInquiry();
    }

    public function globeSurityCashRedirect() {
        $params = $this->getParams();
        $merchantId = 2;
        if (isset($params['code'])) {
            MerchantHelper::setMerchantId($merchantId);
            $res = \Common\Utils\Sms\SmsPesoGlobeHelper::helper()->getTokenByCode("SurityCash", $params['code']);
            if(isset($res['access_token'])){
                dd($res);
                Config::set($merchantId, Config::KEY_SMS_GLOBE_TOKEN, $res['access_token']);
                echo 'success';
                exit;
            }
            echo 'failed';
        }
    }

}
