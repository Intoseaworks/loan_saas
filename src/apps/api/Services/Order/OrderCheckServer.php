<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Order;

use Api\Models\Order\Order;
use Api\Models\User\User;
use Common\Models\User\UserBlack;

class OrderCheckServer extends \Common\Services\Order\OrderCheckServer
{
    /**
     * 判断该身份证是否已有订单
     * @param $idCardNo
     * @return bool
     */
    public function checkIdCardNo($idCardNo)
    {
        //没有pan卡号的取消判断
        if(!$idCardNo){
            return false;
        }
        // 测试服不做此判断
        if (\Yunhan\Utils\Env::isDevOrTest()) {
            return false;
        }
        return Order::model()
            ->leftJoin('user', 'order.user_id', '=', 'user.id')
            ->where('user.id_card_no', $idCardNo)
            ->whereNotIn('order.status', Order::STATUS_COMPLETE)
            ->exists();
    }

    public function checkAadhaarCardNo($aadhaarCardNo)
    {
        //没有A卡号的取消判断
        if(!$aadhaarCardNo){
            return false;
        }
        // 测试服不做此判断
        if (\Yunhan\Utils\Env::isDevOrTest()) {
            return false;
        }
        return Order::model()
            ->leftJoin('user_info', 'order.user_id', '=', 'user_info.user_id')
            ->where('user_info.aadhaar_card_no', $aadhaarCardNo)
            ->whereNotIn('order.status', Order::STATUS_COMPLETE)
            ->exists();
    }

    /**
     * 删除用户所有订单
     * @param $userId
     * @return bool|int
     */
    public function deleteHasExists($userId)
    {
        \DB::table('order')->where('user_id', '=', $userId)->update(['status' => \Common\Models\Order\Order::STATUS_MANUAL_CANCEL]);
    }

    /**
     * 有进行中订单
     * @param Order $order
     * @return bool
     */
    public function hasGoingOrder(Order $order)
    {
        return !in_array($order->status, Order::STATUS_COMPLETE);
    }

    /**
     * 判断上一笔订单状态，是否可再创建订单
     * @suppress PhanUndeclaredProperty
     * @throws \Common\Exceptions\ApiException
     */
    public function canCreateOrder()
    {
        /** @var User $user */
        $user = \Auth::user();

        /*
        if ($this->reachMaxCreate($user->getRealQuality())) {
            return $this->outputException('今天放款名额已满，请明天再来');
        }
        if (!$user->bankCard) {
            return $this->outputException('请先绑定银行卡');
        }
*/
        /** 检查黑名单 */
        /*
        if (!UserBlack::model()->canLoan($user->telephone)) {
            return $this->outputException('暂无借款资格');
        }
*.
        /** 检查身份证 */
/*
        if ($this->checkIdCardNo($user->id_card_no)) {
            return $this->outputException('您当前有正在进行中的借款单');
        }

        if ($this->checkAadhaarCardNo($user->userInfo->aadhaar_card_no)) {
            return $this->outputException('您当前有正在进行中的借款单');
        }
*/
        if ($user->order) {
            $order = $user->order;
            if ($order->isRejected()) {
                return $this->outputException('您有拒绝中的订单');
            }
            # c1 只允许放一次款
            if (($order->isFinished() || (in_array($order->status, [Order::STATUS_MANUAL_CANCEL, Order::STATUS_SYSTEM_CANCEL]) && $user->getRepeatLoanCnt()>=1)) && \Common\Models\Merchant\Merchant::getId('c1') == \Common\Utils\MerchantHelper::getMerchantId()) {
                return $this->outputException('Create order failure, this project only allows one loan');
            }
            /** 检查进行中订单 */
            /*
            if ($this->hasGoingOrder($order)) {
                return $this->outputException('您当前有一笔进行中的借款');
            }
             * 
             */
        }
    }

    /**
     *
     * @param Order $order
     * @suppress PhanUndeclaredProperty
     * @throws \Common\Exceptions\ApiException
     */
    public function canSignOrder(\Common\Models\Order\Order $order, \Common\Models\User\User $user)
    {
        if (!UserBlack::model()->canLoan($user->telephone)) {
            return $this->outputException('暂无借款资格');
        }
        if (!$order->canSign()) {
            return $this->outputException('当前借款不可签约');
        }
        if (!$user->bankCard) {
            return $this->outputException('未绑定银行卡');
        }
    }

    /**
     * 判断是否能用户取消
     * @param Order $order
     * @return bool
     */
    public function canCancelOrder(Order $order)
    {
        if (in_array($order->status, Order::CAN_USER_CANCEL)) {
            return true;
        }
        return false;
    }
}
