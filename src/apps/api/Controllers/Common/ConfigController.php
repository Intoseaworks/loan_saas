<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Common;

use Api\Models\User\User;
use Api\Services\Config\ConfigServer;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Jobs\FaceComparisonJob;
use Common\Models\Order\Order;
use Common\Response\ApiBaseController;
use Common\Models\Common\AppNotice;
use Common\Utils\MerchantHelper;

class ConfigController extends ApiBaseController {

    public function index() {
        $data = ConfigServer::server()->getConfig($this->clientId);
        return $this->resultSuccess($data, '全局配置获取成功');
    }

    public function loan() {
        $user = $this->identity();
        $data = ConfigServer::server()->getLoan();
        return $this->resultSuccess($data, '贷款配置获取成功');
    }

    public function userInfo() {
        $data = ConfigServer::server()->getUserInfoConfig();
        return $this->resultSuccess($data, '用户详情配置项获取成功');
    }

    public function option() {
        $type = $this->getParam('type');
        return $this->resultSuccess(ConfigServer::server()->getOption($type));
    }

    public function test() {
        $orderId = $this->request->get('order_id');
        $order = Order::model()->getOne($orderId);
        if (!$order) {
            echo 'null order';
            exit();
        }
        var_dump(event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPLENISH)));
        exit();

        $user = User::model()->getOne(209);
        if (!$user) {
            echo 'null';
            exit();
        }
        var_dump(dispatch((new FaceComparisonJob($user))));
        exit();
    }

    public function notice() {
        $mNotice = AppNotice::model()->newQuery()
                        ->where("app_id", MerchantHelper::getAppId())
                        ->where("show", "1")
                        ->where(function($query) {
                            $query->where('off_time', ">", date("Y-m-d H:i:s"))->orWhere('off_time', NULL);
                        })->first();
        if ($mNotice) {
            return $this->resultSuccess(["notice" => $mNotice->notice]);
        } else {
            return $this->resultSuccess(["notice" => ""]);
        }
    }

}
