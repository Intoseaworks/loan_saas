<?php

namespace Api\Services\Columbia;

use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Models\Columbia\ColClockin;
use Common\Models\Columbia\ColClockinApprove;

class ClockinServer extends BaseService {

    public function index(User $user, $orderId = '') {
        if (!$orderId) {
            $order = $user->order;
        }else{
            $order = \Common\Models\Order\Order::model()->getOne($orderId);
        }
        $startDate = strtotime($order->paid_time) + 86400;
        $endDate = strtotime($order->paid_time) + 86400 * 5;
        $list = ColClockin::model()->where("user_id", $user->id)->where("order_id", $order->id)->orderByDesc("id")->get();
        $listApprove = ColClockinApprove::model()
                ->where("order_id", $order->id)
                ->where("approve_status", ColClockinApprove::STATUS_APPROVE_PASS)
                ->get();
        $res = ["clockin" => []];

        for ($i = $startDate; $i <= $endDate; $i += 86400) {
            $date = date("Y-m-d", $i);
            $res["clockin"][$date]["item"]["days"] = $date;
            $res["clockin"][$date]["item"]["coupon"] = 0;
            if (!isset($res["clockin"][$date])) {
                $res["clockin"][$date] = [];
            }
            foreach (ColClockin::TIME_FRAME_TITLE as $key => $val) {
                $res["clockin"][$date]["item"]["times"][$key - 1] = ["time" => $val, "status" => 0];
            }
        }
        foreach ($list as $item) {
            $timeFrame = ColClockin::TIME_FRAME_TITLE[$item->time_frame];
            if ($item->date) {
                $res["clockin"][$item->date]["item"]["times"][$item->time_frame - 1]['status'] = 1;
            }
        }
        foreach ($listApprove as $approve) {
            if ($approve->coupon == 1) {
                $res["clockin"][$approve->date]["item"]["coupon"] = 1;
            }
        }
        sort($res['clockin']);
        return $res;
    }

    public function do(User $user, $params) {
        $lastOrder = $user->order;
        if ($lastOrder) {
            $maxDate = date("Ymd", strtotime($lastOrder->paid_time) + 86400 * 5);
            if (date("Ymd") > $maxDate) {
                return $this->outputError("Not within the clocking time");
            }
            if (date("Ymd", strtotime($lastOrder->paid_time)) > date("Ymd")) {
                return $this->outputError("The activity has not started, please come back on " . date("Y/m/d", strtotime($lastOrder->paid_time) + 86400) . " 7:00 to participate");
            }
            $lastClockin = ColClockin::model()->where("user_id", $user->id)->where("order_id", $lastOrder->id)->orderByDesc("id")->first();
            $params['running_days'] = 1;
            $params['time_frame'] = [];
            $params['date'] = date("Y-m-d");

            # 时段索引
            $frameIndex = function() {
                foreach (ColClockin::TIME_FRAME as $frameId => $hours) {
                    if (in_array(date("G"), $hours)) {
                        return $frameId;
                    }
                }
                return false;
            };
            $currentTimeFrame = $frameIndex();
            if ($currentTimeFrame === false) {
                return $this->outputError("Not within the clocking time");
            }

            $params['time_frame'] = $currentTimeFrame;
            if ($lastClockin) {
                if (date("Y-m-d", strtotime($lastClockin->date) + 86400) == date("Y-m-d")) {
                    $params['running_days'] = $lastClockin->running_days + 1;
                }
                if ($lastClockin->time_frame == $currentTimeFrame && date("Y-m-d") == $lastClockin->date) {
                    return $this->outputError("It's already punched in");
                }
                $params['time_frame'] = $currentTimeFrame;
            }
            $params['merchant_id'] = $user->merchant_id;
            $params['user_id'] = $user->id;
            $params['order_id'] = $lastOrder->id;
            $res = ColClockin::model()->createModel($params);
            if ($res) {
                return $this->outputSuccess();
            } else {
                return $this->outputError("Fail to save");
            }
        } else {
            $this->outputError("Fail to save");
        }
    }

    public function couponInfo(User $user) {
        $userConpon = \Common\Models\Columbia\ColUserCoupon::model()->where("user_id", $user->id)->first();
        if ($userConpon) {
            $coupon = \Common\Models\Coupon\Coupon::model()->where("id", $userConpon->coupon_id)->first();
            if ($coupon) {
                return $coupon->used_amount;
            }
        }
        return 0;
    }

}
