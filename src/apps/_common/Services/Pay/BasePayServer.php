<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/3/8
 * Time: 16:48
 */

namespace Common\Services\Pay;

use Api\Models\BankCard\BankCardPeso;
use Common\Models\BankCard\BankCard;
use Common\Models\Config\Config;
use Common\Models\Trade\AdminTradeAccount;
use Common\Models\Trade\TradeLog;
use Common\Services\BaseService;
use JMD\JMD;
use JMD\Libs\Services\DataFormat;
use JMD\Libs\Services\PayService;
use Common\Utils\MerchantHelper;
use Admin\Services\TradeManage\RemitServer;

class BasePayServer extends BaseService {

    const RESULT_SUCCESS = 'SUCCESS';
    const RESULT_FAILED = 'FAILED';
    const RESULT_PROCESSING = 'PROCESSING';
    const RESULT_UNUSED = 'UNUSED';

    /** 代付路由地址 */
    public $routeDaifuPay = 'api/trade/single_pay';

    /** 代扣路由地址 */
    public $routeDaikouPay = 'api/trade/single_repay_collect';

    /** 交易结果查询路由 */
    public $routeQueryOrder = 'api/trade/query_order';

    /** htmlpay路由地址 */
    public $routeHtmlPay = 'api/trade/third_pay/ready_pay';

    /** apppay路由地址 */
    public $routeAppPay = 'api/trade/third_pay/app_ready_pay';

    /** 代付通知路由 */
    public $routeDaifuPayNotice = 'app/callback/daifu-notice';

    /** 代扣通知路由 */
    public $routeDaikouPayNotice = 'app/callback/daikou-notice';

    /** htmlpay通知路由 */
    public $routeHtmlPayNotice = 'app/callback/html-pay';

    /** htmlpay支付结果重定向地址(部分支付渠道需要) */
    public $routeHtmlPayRedirect = 'app/callback/html-redirect';

    /**
     * 构造代付请求参数
     * @param TradeLog $tradeLog
     * @return array
     */
    public function buildDaifuPayParams(TradeLog $tradeLog) {
        $apiDomain = config('config.api_client_domain');

        $bankCard = BankCard::model()->getByUserIdAndNo($tradeLog->user_id, $tradeLog->trade_account_no);
        return [
            'batch_no' => $tradeLog->batch_no, //批次号
            'transaction_name' => $tradeLog->trade_desc, //交易名称
            'transaction_no' => $tradeLog->transaction_no, //业务交易编号
            'amount' => $tradeLog->business_amount, //交易金额
            'bank_card_no' => $tradeLog->trade_account_no, //银行卡号
            'bank_card_account' => $tradeLog->trade_account_name, //银行卡户名
            'bank_card_telephone' => $tradeLog->trade_account_telephone, //银行卡手机号
            'id_card_no' => $tradeLog->user->id_card_no, //用户身份证号
            'user_identifying' => $tradeLog->trade_account_telephone, //用户标识(暂用手机号标识)
            'identity_id' => $tradeLog->user_id, //应用用户唯一标识
            'transaction_info' => $tradeLog->trade_desc, //交易信息
            'business_type' => $tradeLog->business_type, //业务类型
            'trade_platform' => $tradeLog->trade_platform, //交易平台
            'notice_url' => str_finish($apiDomain, '/') . $this->routeDaifuPayNotice, //通知地址
            //'bank_name' => optional($bankCard)->bank_name,//收款银行名
            'ifsc' => optional($bankCard)->ifsc,
        ];
    }

    /**
     * 构造代扣请求参数
     * @param TradeLog $tradeLog
     * @param BankCard $bankCard
     * @return array
     */
    public function buildDaikouPayParams(TradeLog $tradeLog, BankCard $bankCard) {
        $apiDomain = config('config.api_client_domain');
        return [
            'batch_no' => $tradeLog->batch_no, //批次号
            'transaction_name' => $tradeLog->trade_desc, //交易名称
            'transaction_no' => $tradeLog->transaction_no, //业务交易编号
            'amount' => $tradeLog->business_amount, //交易金额
            'bank_card_no' => $tradeLog->trade_account_no, //银行卡号
            'bank_card_account' => $tradeLog->trade_account_name, //银行卡户名
            'bank_card_telephone' => $tradeLog->trade_account_telephone, //银行卡手机号
            'id_card_no' => $tradeLog->user->id_card_no, //用户身份证号
            'user_identifying' => $bankCard->encodeUserIdentifying(), //用户标识(特定生成格式，与鉴权绑卡用户标识一致)
            'identity_id' => $tradeLog->user_id, //应用用户唯一标识
            'transaction_info' => $tradeLog->trade_desc, //交易信息
            'business_type' => $tradeLog->business_type, //业务类型
            'trade_platform' => $tradeLog->trade_platform, //交易平台
            'notice_url' => str_finish($apiDomain, '/') . $this->routeDaikouPayNotice, //通知地址
        ];
    }

    /**
     * 执行代付请求
     * @param TradeLog $tradeLog
     * @return \JMD\Libs\Services\DataFormat
     */
    public function executeDaifuPay(TradeLog $tradeLog) {
        echo "处理订单：".$tradeLog->master_related_id.PHP_EOL;
        //$result = $this->execute($this->routeDaifuPay, $params);
        switch ($tradeLog->trade_platform) {
            case TradeLog::TRADE_PLATFORM_MANUAL:
                return $this->exeManual($tradeLog);
            case TradeLog::TRADE_PLATFORM_PAYMOB:
                return \Common\Libraries\PayChannel\Paymob\PayoutHelper::helper()->run($tradeLog);
        }
    }

    public function hasDaikouOpen() {
        return false;
    }

    public function envAutoRemit() {
        return config('config.auto_remit');
    }

    /**
     * 是否开启自动代付
     * @return bool
     * @throws \Exception
     */
    public function hasDaifuOpen() {
        $envConfig = $this->envAutoRemit();
        $configAutoRemitIsOpen = Config::model()->sysAutoRemitIsOpen();

        return $envConfig && $configAutoRemitIsOpen;

        /** 商户可用自动代付渠道 */
        $autoPayChannel = $this->getAutoPayChannel();
        /** 后台商户设置代付渠道 */
        $condition = [
            'type' => AdminTradeAccount::TYPE_OUT,
            'status' => AdminTradeAccount::STATUS_ACTIVE
        ];
        $adminAccounts = AdminTradeAccount::model()->search($condition)->value('payment_method');
        return in_array(array_get(AdminTradeAccount::PAYMENT_METHOD_RELATE_PLATFORM, $adminAccounts), $autoPayChannel);
    }

    /**
     * 获取代付账号
     * @return AdminTradeAccount|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getDaifuAccount() {
        $condition = [
            'payment_method' => AdminTradeAccount::PAYMENT_DAIFU
        ];
        return AdminTradeAccount::model()->search($condition)->first();
    }

    /**
     * 获取代扣账号
     * @return AdminTradeAccount|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getDaikouAccount() {
        $condition = [
            'payment_method' => AdminTradeAccount::PAYMENT_DAIKOU
        ];
        return AdminTradeAccount::model()->search($condition)->first();
    }

    /**
     * 商户可用自动代付渠道
     * @return array
     */
    public function getAutoPayChannel() {
        return (array) config('config.auto_pay_channel');
    }

    /**
     * 执行代扣请求
     * @param TradeLog $tradeLog
     * @param $bankCard
     * @return \JMD\Libs\Services\DataFormat
     */
    public function executeDaikouPay(TradeLog $tradeLog, BankCard $bankCard) {
        $params = $this->buildDaikouPayParams($tradeLog, $bankCard);

        $result = $this->execute($this->routeDaikouPay, $params);

        return $result;
    }

    /**
     * 查询交易结果
     * @param $transactionNo
     * @return \JMD\Libs\Services\DataFormat
     */
    public function executeQueryOrder($transactionNo) {
        $params = [
            'transaction_no' => $transactionNo,
        ];

        $result = $this->execute($this->routeQueryOrder, $params);

        return $result;
    }

    public function buildHtmlPayParams(TradeLog $tradeLog) {
        $apiDomain = config('config.api_client_domain');
        $user = $tradeLog->user;
        return [
            'batch_no' => $tradeLog->batch_no, //批次号
            'transaction_name' => $tradeLog->trade_desc, //交易名称
            'transaction_no' => $tradeLog->transaction_no, //业务交易编号
            'amount' => $tradeLog->business_amount, //交易金额
            'bank_card_no' => $tradeLog->trade_account_no, //银行卡号
            //'bank_card_account' => $tradeLog->trade_account_name,//银行卡户名
            'bank_card_telephone' => $tradeLog->trade_account_telephone, //银行卡手机号
            'id_card_no' => $tradeLog->user->id_card_no, //用户身份证号
            'user_identifying' => $tradeLog->user->telephone, //用户标识(htmlpay可使用手机号标识)
            'identity_id' => $tradeLog->user_id, //应用用户唯一标识
            'transaction_info' => $tradeLog->trade_desc, //交易信息
            'business_type' => $tradeLog->business_type, //业务类型
            'trade_platform' => $tradeLog->trade_platform, //交易平台
            'notice_url' => str_finish($apiDomain, '/') . $this->routeHtmlPayNotice, //通知地址
            'redirect_url' => str_finish($apiDomain, '/') . $this->routeHtmlPayRedirect, //通知地址
            'user_email' => '', // 非必填 用户email。部分第三方用来默认填充表单。如：razorPay
            'user_telephone' => $tradeLog->user->telephone, // 非必填 用户手机号。部分第三方用来默认填充表单。如：razorPay
            'payer_account' => $tradeLog->trade_account_no, // 非必填 用户支付账号。部分第三方需要。如Mpurse填写UPI账号
        ];
    }

    public function executePay(TradeLog $tradeLog, $method) {
        $params = $this->buildHtmlPayParams($tradeLog);
        $result = [];
        switch ($params['trade_platform']) {
            case TradeLog::TRADE_PLATFORM_FAWRY:
                MerchantHelper::helper()->setMerchantId($tradeLog->merchant_id);
                $result = \Common\Libraries\PayChannel\Fawry\RepayHelper::helper()->repay($tradeLog, $method);
                break;
        }
        return $result;
    }

    public function buildAppPayParams(TradeLog $tradeLog) {
        $apiDomain = config('config.api_client_domain');
        $user = $tradeLog->user;

        return [
            'transaction_name' => $tradeLog->trade_desc, // 交易名称
            'transaction_no' => $tradeLog->transaction_no, // 交易编号
            'amount' => $tradeLog->business_amount, // 金额
            'id_card_no' => $tradeLog->user->id_card_no, // 身份证号
            'user_identifying' => $tradeLog->user->telephone, // 用户标识
            'identity_id' => $tradeLog->user_id, // 用户唯一标识 mobikwik传email
            'transaction_info' => $tradeLog->trade_desc, // 交易信息，会展示在支付页面 最多100字符
            'business_type' => $tradeLog->business_type, // 业务类型
            'trade_platform' => $tradeLog->trade_platform, // 交易平台
            'notice_url' => str_finish($apiDomain, '/') . $this->routeHtmlPayNotice, // 异步通知地址
            'redirect_url' => str_finish($apiDomain, '/') . $this->routeHtmlPayRedirect, // 支付成功重定向地址
            'user_telephone' => $tradeLog->user->telephone, // 非必填，部分平台用来填充默认表单信息
            'user_email' => optional($user->userInfo)->email, // 非必填，部分平台用来默认填充表单信息
            'user_name' => $user->fullname, // 非必填，部分平台用来默认填充表单信息
        ];
    }

    public function executeAppPay(TradeLog $tradeLog) {
        $params = $this->buildAppPayParams($tradeLog);

        $result = $this->execute($this->routeAppPay, $params);

        return $result;
    }

    protected function execute($url, $params) {
        JMD::init(['projectType' => 'lumen']);
        $payServer = new PayService();
        $payServer->request->setUrl($url);
        $payServer->request->setData($params);
        $result = $payServer->request->execute();
        return $result;
    }

    public function exeManual(TradeLog $tradeLog){
        $res['requestNo'] = date("YmdHis");
        $res['status'] = BasePayServer::RESULT_SUCCESS;
        RemitServer::server()->flowRemitSuccess($tradeLog, $tradeLog->trade_amount, date("Y-m-d H:i:s"));
        return $this->outputSuccess("Success", $res);
    }
}
