<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Coupon;

use Common\Response\ApiBaseController;
use Api\Services\Coupon\CouponServer;

class CouponController extends ApiBaseController {

    public function checking() {
        $user = $this->identity();
        $params = $this->getParams();
        $params['order_id'] = isset($params['order_id'])?$params['order_id']:null;
        if(is_array($params['coupon_id'])){
            $lockKey = "checking-coupon-usage";
            $lockKey .= implode("|", $params['coupon_id']);
            foreach($params['coupon_id'] as $cid){
                $lockKey .= "|".$cid;
                $res = CouponServer::server()->checking($user->id, $cid, $params['order_id'], $lockKey);
            }
        }else{
            $res = CouponServer::server()->checking($user->id, $params['coupon_id'], $params['order_id']);
        }
        \Log::info('app使用优惠券消息------'.$res.'---'.json_encode($params));
        if (true === $res) {
            return $this->resultSuccess($res);
        }
        return $this->resultFail($res);
    }

    public function checkingOrderSign() {
        $user = $this->identity();
        $params = $this->getParams();
        $params['order_id'] = isset($params['order_id'])?$params['order_id']:null;
        $res = CouponServer::server()->checkingOrderSign($user->id, $params['coupon_id'], $params['order_id']);
        \Log::info('app签约页使用优惠券消息------'.$res.'---'.json_encode($params));
        if (true === $res) {
            return $this->resultSuccess($res);
        }
        return $this->resultFail($res);
    }

    public function myCoupon() {
        $user = $this->identity();
        $res = CouponServer::server()->myCoupon($user->id);
        return $this->resultSuccess($res);
    }

    public function myEffectiveCoupon() {
        $user = $this->identity();
        $params = $this->getParams();
        $res = CouponServer::server()->myEffectiveCoupon($user->id,$params);
        return $this->resultSuccess($res);
    }

}
