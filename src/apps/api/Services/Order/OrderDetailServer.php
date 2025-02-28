<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Order;

use Api\Models\Order\Order;
use Api\Models\Order\OrderDetail;
use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Models\Config\Config;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\NewClm\ClmServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Host\HostHelper;
use Illuminate\Support\Facades\Cache;

class OrderDetailServer extends BaseService
{
    /**
     * @param Order $order
     * @param $param
     * @return bool
     */
    public function saveByCreateOrder(Order $order, $param)
    {
        $order->refresh();
        $position = ArrayHelper::jsonToArray($param['position']);
        //详细地址
        $longitude = array_get($position, 'longitude', 0);
        $latitude = array_get($position, 'latitude', 0);
        $orderDetails = [
            OrderDetail::KEY_LOAN_LOCATION => array_get($position, 'address', 0),
            OrderDetail::KEY_LOAN_POSITION => implode(',', [$longitude, $latitude]), // 不可随意变更格式，影响到风控规则判断
            OrderDetail::KEY_LOAN_IP => HostHelper::getIp(),
            # gst配置
            OrderDetail::KEY_PROCESSING_RATE => LoanMultipleConfigServer::server()->getServiceChargeRate($order->user, $order->loan_days),
            //OrderDetail::KEY_GST_PROCESSING_RATE => Config::getLoanGstRate(),
            //OrderDetail::KEY_GST_PENALTY_RATE => Config::getLoanGstRate(),
            OrderDetail::KEY_IMEI => array_get($param, 'imei', ''),
            OrderDetail::KEY_CAN_CONTACT_TIME => array_get($param, 'can_contact_time', ''),//方便联系时间段

            //OrderDetail::KEY_HIGH_OVERDUE_FEE => json_encode(Order::OVERDUE_FEE),
            //根据后台配置定制分期
            //OrderDetail::KEY_INSTALLMENT => json_encode(Config::model()->getInstallment($order->user->quality), 256),
            OrderDetail::KEY_INSTALLMENT => OrderDetailServer::server()->getInstallment($order),
            //OrderDetail::KEY_LAST_INSTALLMENT_FREE_ON => Config::model()->getInstallmentFreeOn($order->user->quality),
            OrderDetail::KEY_LAST_INSTALLMENT_FREE_ON => Config::model()->getLastLoanRepaymentDeductionOn(),
            //CLM手续费折扣费率
            OrderDetail::KEY_SERVICE_CHARGE_DISCOUNT => ClmServer::server()->getInterestDiscount($order->user),
        ];
        if ($loanReason = array_get($param, 'loan_reason')) {
            $orderDetails[OrderDetail::KEY_LOAN_REASON] = $loanReason;
        } elseif ($loanReason = optional($order->userInfo)->loan_reason) {
            $orderDetails[OrderDetail::KEY_LOAN_REASON] = $loanReason;
        }
        if ($productId = array_get($param, 'product_id')) {
            $orderDetails[OrderDetail::KEY_PRODUCT_ID] = $productId;
        }
//        if ($intentionPrincipal = array_get($param, 'intention_principal')) {
//            $orderDetails[OrderDetail::KEY_INTENTION_PRINCIPAL] = $intentionPrincipal;
//        }
//        if ($intentionLoanDays = array_get($param, 'intention_loan_days')) {
//            $orderDetails[OrderDetail::KEY_INTENTION_LOAN_DAYS] = $intentionLoanDays;
//        }
        return $this->saveKeyVals($order->id, $orderDetails);
    }

    public function saveByOrderUpdate(Order $order)
    {
        $orderDetails = [
            OrderDetail::KEY_INSTALLMENT => OrderDetailServer::server()->getInstallment($order),
        ];
        return $this->saveKeyVals($order->id, $orderDetails);
    }

    public function getInstallment($order)
    {
        $loanDays = $order->loan_days;
        $firstLoanRepaymentRate = Config::model()->getFirstLoanRepaymentRate();
        $lastLoanDays = Config::VALUE_MAX_LOAN_DAY - $loanDays;
        $lastLoanRepaymentRate = MoneyHelper::round2point(100 - $firstLoanRepaymentRate);
        $installment = [
            ['repay_days' => $loanDays, 'repay_proportion' => $firstLoanRepaymentRate],
            ['repay_days' => $lastLoanDays, 'repay_proportion' => $lastLoanRepaymentRate]
        ];
        return json_encode($installment, 256);
    }

    /**
     *
     * @param \Common\Models\Order\Order $order
     * @suppress PhanUndeclaredProperty
     * @return bool
     */
    public function saveOrderBank($order)
    {
        /** @var User $user */
        $user = $order->user;
        $orderDetails = [
            OrderDetail::KEY_BANK_CARD_NO => $user->bankCard->account_no,
            OrderDetail::KEY_BANK_NAME => $user->bankCard->bank_name,
            OrderDetail::KEY_BANK_PAY_TYPE => $user->bankCard->payment_type,
        ];
        return $this->saveKeyVals($order->id, $orderDetails);
    }

    public function saveRejectedDays($order, $rejectedDays = null)
    {
        /** @var $order Order */
        if (is_null($rejectedDays)) {
            $rejectedDays = LoanMultipleConfigServer::server()->getLoanAgainDays($order->user);
        }
        $orderDetails = [
            OrderDetail::KEY_REJECTED_DAYS => $rejectedDays,
        ];
        return $this->saveKeyVals($order->id, $orderDetails);
    }

    public function saveKeyVal($orderId, $key, $value) {
        $data = [
            'order_id' => $orderId,
            'key' => $key,
            'value' => $value,
        ];
        $condition = [
            'order_id' => $orderId,
            'key' => $key,
        ];
        $keyCache = "OrderDeail::getValueByKey::Order[{$orderId}]::key[{$key}]";
        Cache::delete($keyCache);
        return OrderDetail::model()->updateOrCreateModel(OrderDetail::SCENARIO_CREATE, $condition, $data);
    }

    /**
     * @param $orderId
     * @param array $orderDetails
     * @return bool
     */
    private function saveKeyVals($orderId, array $orderDetails)
    {
        foreach ($orderDetails as $orderDetailKey => $orderDetailVal) {
            $data = [
                'order_id' => $orderId,
                'key' => $orderDetailKey,
                'value' => $orderDetailVal,
            ];
            $condition = [
                'order_id' => $orderId,
                'key' => $orderDetailKey,
            ];

            $keyCache = "OrderDeail::getValueByKey::Order[{$orderId}]::key[{$orderDetailKey}]";
            Cache::delete($keyCache);
            if (!OrderDetail::model()->updateOrCreateModel(OrderDetail::SCENARIO_CREATE, $condition, $data)) {
                return false;
            }
        }
        return true;
    }
}
