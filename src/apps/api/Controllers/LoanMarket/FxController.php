<?php

namespace Api\Controllers\LoanMarket;

use Common\Response\ApiBaseController;
use Api\Services\Order\LoanMarketServer;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Models\Config\LoanMultipleConfig;
use Api\Models\Order\Order;
use Common\Utils\MerchantHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\Feixiang\FeixiangApi;
use Common\Redis\CommonRedis;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class FxController extends ApiBaseController {

    const APP_KEY = "APP5E7DE05652DB8517";
    const CLIENT_ID = "FX";
    const WHITE_LIST = ["15.207.56.205", '13.234.227.114', '127.0.0.1'];

    public function __construct() {
        parent::__construct();
        MerchantHelper::helper()->safeSetAppIdByKey(self::APP_KEY);

        $ip = HostHelper::getIp();
        if (!in_array($ip, self::WHITE_LIST)) {
            $this->retFailed([], "In train You IP :{$ip} is incorrect");
            exit;
        }
    }

    public function acceptUser() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("acceptUser", $params);
        $data = [
            "panMd5" => $params['panMd5'],
            "isAccept" => 1,
            "reason" => ""
        ];
        /* 黑名单检查项 */
        $checkBlacklist = [
            "telephone" => $params['phoneMd5'],
            "panMd5" => $params['panMd5'],
            "adaahaarNumber" => $params['aadNo'],
        ];
        /* 黑名单检查 */
        foreach ($checkBlacklist as $type => $value) {
            $isBlack = $server->checkBlacklist($value, $type);
            if ($isBlack) {
                $data['isAccept'] = false;
                return $this->retSuccess($data);
            }
        }
        $user = $server->getUserByMd5Telephone($params['phoneMd5']);
        $data = [
            "panMd5" => $params['panMd5'],
            "isAccept" => true,
            "reason" => ""
        ];
        if ($user) {
            //判断有未完成订单
            if (isset($user->order->status) && in_array($user->order->status, Order::STATUS_NOT_COMPLETE)) {
                $data['isAccept'] = false;
            }
            //判断订单是否是飞象的营销成功并下单了
            if ($user->client_id == 'FX' && $user->order->app_client != "FX") {
                $data['isAccept'] = false;
            }
            $loanAmoutRang = LoanMultipleConfig::getConfigByCnt(1, 0);
            $data["creditLimitMin"] = 1000 * 100;
            if ($user->quality) {
                $data["creditLimitMax"] = 5000 * 100;
            } else {
                $data["creditLimitMax"] = 3000 * 100;
            }
            $log->response_data = json_encode($data);
            $log->save();
            return $this->retSuccess($data);
        } else {
            $loanAmoutRang = LoanMultipleConfig::getConfigByCnt(1, 0);
            $data["creditLimitMin"] = 1000 * 100;
            $data["creditLimitMax"] = 3000 * 100;
            $log->response_data = json_encode($data);
            $log->save();
            return $this->retSuccess($data);
        }
    }

    public function acceptOrder() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("acceptOrder", $params);
        $oid = $params['orderID'];
        if ($this->verifyCount($oid) >= 1) {
            $this->retFailed(5);
        }
        /* 注册登录用户 */
        $userModel = $server->loginOrReg($params);
        if (!$userModel) {
            $this->payoutEnd($params);
            $this->retFailed(1);
        }
        if (isset($userModel->order->status) && in_array($userModel->order->status, Order::STATUS_NOT_COMPLETE)) {
            $this->payoutEnd($params);
            $this->retFailed(2);
        }
        /* 绑定银行卡 */
        $resBank = $server->bindBank($userModel, $params);
        if (!$resBank) {
            $this->payoutEnd($params);
            $this->retFailed(3);
        }
        /* 创建订单 */
        $order = $server->createOrder($userModel, $params);
        if (!$order) {
            $this->payoutEnd($params);
            $this->retFailed(4);
        }
        /* 放款 */
        $server->daifu($order);
        /* 放款返回结果 */
        $resData = [
            "orderID" => $params['orderID'],
            "panMd5" => $params['panMd5']
        ];
        $log->response_data = json_encode($resData);
        $log->save();
        $this->retSuccess($resData);
    }

    public function pushUser() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("pushUser", $params);
        $res = $server->updateUserInfo($params);

        if ($res) {
            $data = [
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5'],
                "reason" => "",
            ];

            $log->response_data = json_encode($data);
            $log->save();
            $this->retSuccess($data);
        } else {
            $this->retFailed();
        }
    }

    public function pushDetail() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("pushDetail", $params);
        $res = $server->updateOrder($params);
        if ($res) {
            $data = [
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5']
            ];
            $log->response_data = json_encode($data);
            $log->save();
            $this->retSuccess($data);
        } else {
            $this->retFailed();
        }
    }

    public function pushContact() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("pushContact", $params);
        $res = $server->saveContact($params);
        if ($res) {
            $data = [
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5']
            ];
            $log->response_data = json_encode($data);
            $log->save();
            $this->retSuccess($data);
        } else {
            $this->retFailed();
        }
    }

    public function pushOverdue() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("pushOverdue", $params);
        if ($ret = $server->checkOverdue($params) === true) {
            $log->response_data = json_encode($ret);
            $log->save();
            $this->retSuccess([
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5'],
            ]);
        } else {
            $log->response_data = json_encode($ret);
            $log->save();
            $this->retSuccess([
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5'],
            ]);
        }
    }

    public function createRepayment() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("createRepayment", $params);

        $res = $server->repayLink($params);
        if (strpos($res, "https") === 0) {
            $data = [
                "orderID" => $params['orderID'],
                "panMd5" => $params['panMd5'],
                "repaymentURL" => $res
            ];
            $log->response_data = json_encode($data);
            $log->save();
            $this->retSuccess($data);
        } else {
            $this->retFailed();
        }
    }

    public function ret($code, $data = null, $msg = "") {
        $result = [
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        ];
        echo json_encode($result);
        exit;
    }

    public function retFailed($data = null, $msg = "FAILED") {
        return $this->ret(-1, $data, $msg);
    }

    public function retSuccess($data = null, $msg = "SUCCESS") {
        return $this->ret(0, $data, $msg);
    }

    public function payoutEnd($data) {
        $data['orderID'] = $data['orderID'];
        $data['panMd5'] = $data['panMd5'];
        $data['disbursalAmount'] = $data['disbursalAmount'];
        $data['loanTime'] = time();
        $data['isPayment'] = false;


        $fx = new FeixiangApi();
        $res = $fx->lmPost($data, "/merchant/payout-notify");
        $server = LoanMarketServer::server();
        $log = $server->log("payout-notify", $data);
        $log->response_data = json_encode($res);
        $log->save();
    }

    public function queryPayoutStatus() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("queryPayoutStatus", $params);
        $order = $server->getOrderByProductId($params['orderID']);
        $status = "pending";

        if (in_array($order->status, Order::CONTRACT_STATUS)) {
            $status = "success";
        }

        if (in_array($order->status, Order::PAY_FAIL_STATUS)) {
            $status = "failure";
        }
        $data = [
            "orderID" => $params['orderID'],
            "panMd5" => $params['panMd5'],
            "status" => $status
        ];
        $log->response_data = json_encode($data);
        $log->save();
        $this->retSuccess($data);
    }

    public function queryRepaymentStatus() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("queryRepaymentStatus", $params);
        $order = $server->getOrderByProductId($params['orderID']);
        $status = "pending";

        if (in_array($order->status, Order::FINISH_STATUS)) {
            $status = "closed";
        }
        $data = [
            "orderID" => $params['orderID'],
            "panMd5" => $params['panMd5'],
            "status" => $status
        ];
        $log->response_data = json_encode($data);
        $log->save();
        $this->retSuccess($data);
    }

    public function queryOrder() {
        $server = LoanMarketServer::server();
        $params = $this->getParams();
        $log = $server->log("queryOrder", $params);
        $order = $server->getOrderByProductId($params['orderID']);
        $data = [
            "orderID" => $params['orderID'],
            "panMd5" => $params['panMd5'],
            "loanTime" => strtotime($order->paid_time),
            'repayTime' => strtotime($order->getActualRepayTime())
        ];
        $log->response_data = json_encode($data);
        $log->save();
        $this->retSuccess($data);
    }

    /**
     * @param $userId
     * @param $key
     * @param bool $incr
     * @return int
     */
    public function verifyCount($key, $incr = true)
    {

        $redisKey = "FXORDER{$key}";

        $count = CommonRedis::redis()->get($redisKey);

        if ($incr) {
            CommonRedis::redis()->incr($redisKey);
        }

        if (is_null($count)) {
            CommonRedis::redis()->expire($redisKey, 3600);
        }

        return $count ?: 0;
    }
}
