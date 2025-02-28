<?php

namespace Admin\Services\Columbia;

use Admin\Services\BaseService;
use Admin\Services\Coupon\CouponReceiveServer;
use Common\Models\Columbia\ColClockin;
use Common\Models\Columbia\ColClockinApprove;
use Common\Models\Columbia\ColUserCoupon;
use Common\Models\Coupon\Coupon;
use Common\Models\Coupon\CouponTask;
use Common\Models\Order\Order;
use Common\Models\User\User;
use Common\Utils\Data\DateHelper;
use Common\Utils\LoginHelper;
use Common\Utils\Upload\ImageHelper;
use DB;

class ClockinServer extends BaseService {

    public function index($params) {
        $where = " cc.created_at<date(now()) ";

        if ($keyword = array_get($params, "keyword")) {
            $where .= " AND (u.telephone='{$keyword}' OR o.order_no='{$keyword}')";
        }
        if ($punchTime = array_get($params, "punch_time")) {
            if (is_array($punchTime))
                $where .= " AND cc.date>=date('{$punchTime[0]}') AND cc.date<=date('{$punchTime[1]}')";
        }
        if ($paidTime = array_get($params, "paid_time")) {
            if (is_array($paidTime))
                $where .= " AND o.paid_time>='{$paidTime[0]}' AND o.paid_time<='{$paidTime[1]}'";
        }
        $sql = "select cc.order_id,concat(left(u.telephone,3), '****', right(u.telephone,3)) as telephone,u.fullname,o.order_no,o.paid_time,cc.date,count(1) total,case when isnull(cca.approve_status) then 0 else cca.approve_status end as approve_status from col_clockin cc
INNER JOIN `order` o ON cc.order_id=o.id
INNER JOIN `user` u ON o.user_id=u.id
LEFT JOIN col_clockin_approve cca ON cca.order_id=cc.order_id AND cca.date=cc.date
WHERE {$where}
GROUP BY cc.user_id,cc.date,cc.order_id
HAVING total>=3
ORDER BY approve_status,cc.date ASC,cc.created_at ASC";
        $pageSize = 10;
        $currentPage = 1;
        if (isset($params['page_size']) && $params['page_size']) {
            $pageSize = $params['page_size'];
        }
        if (isset($params['page']) && $params['page']) {
            $currentPage = $params['page'];
        }
        $limit = " LIMIT " . (($currentPage - 1) * $pageSize) . ", {$pageSize};";
        $res = \DB::select($sql);
        $data = [];
        $data['total'] = count($res);
        $data['data'] = \DB::select($sql . $limit);
        $data['page'] = $currentPage;
        $data['page_total'] = ceil($data['total'] / $pageSize);
        return $data;
    }

    public function detail($params) {
        $res = [];
        $date = array_get($params, "date");
        $orderId = array_get($params, "order_id");
        $dict = config('dict');
        if ($date && $orderId) {
            $sql = "select u.telephone,ui.gender,ui.province,ui.city,ui.address as user_address,ui.birthday,u.fullname,o.order_no,o.paid_time,case when isnull(cca.approve_status) then 0 else cca.approve_status end as approve_status,
                ui.education_level,
                ui.marital_status,
                uw.industry as industry,
                uw.company,
                uw.employment_type as social_status,
                ui.live_length as residential_time,
                cc.* 
                from col_clockin cc
INNER JOIN `order` o ON cc.order_id=o.id
INNER JOIN `user` u ON o.user_id=u.id
INNER JOIN `user_info` ui ON u.id=ui.user_id
INNER JOIN `user_work` uw ON u.id=uw.user_id
LEFT JOIN col_clockin_approve cca ON cca.order_id=cc.order_id AND cca.date=cc.date
WHERE cc.date = '{$date}' AND cc.order_id='{$orderId}'
ORDER BY cc.created_at";
            $list = DB::select($sql);
            foreach ($list as $item) {
                $res['fullname'] = $item->fullname;
                $res['gender'] = $item->gender;
                $res['user_address'] = $dict[$item->province] . " " . $dict[$item->city] . " " . $item->user_address;
                $res['age'] = DateHelper::getAge($item->birthday);
                $res['approve_status'] = $item->approve_status;
                $res['education_level'] = $item->education_level;
                $res['marital_status'] = $item->marital_status;
                $res['industry'] = $dict[$item->industry] ?? "";
                $res['company'] = $item->company;
                $res['residential_time'] = $dict[$item->residential_time] ?? "";
                $res['social_status'] = $dict[$item->social_status];
                $punchingAddress = is_null(json_decode($item->address)) ? "" : json_decode($item->address, true);
                $res['list'][] = [
                    "id" => $item->id,
                    "pic_approve_status" => $item->pic_approve_status,
                    "pic_approve_reject_reson" => $item->pic_approve_reject_reson,
                    "pic_selfie" => ImageHelper::getPicUrl($item->pic_selfie, 400),
                    "pic_surroundings" => ImageHelper::getPicUrl($item->pic_surroundings, 400),
                    "punching_time" => $item->created_at,
                    "punching_address" => array_get($punchingAddress, "address"),
                ];
            }
        }
        return $res;
    }

    public function apprive($params) {
        $date = array_get($params, "date");
        $orderId = array_get($params, "order_id");
        $action = array_get($params, "action");
        $approveDetail = array_get($params, "approve_list");
        if (!in_array($action, ["PASS", "FAILED"])) {
            return $this->outputError("Action parameter exception (PASS or FAILED)");
        }
        if ($date && $orderId && $action) {
            if (ColClockinApprove::model()->where("order_id", $orderId)->where("date", $date)->exists()) {
                return $this->outputError("Approved");
            }
            $clockInCount = ColClockin::model()->where("order_id", $orderId)->where("date", $date)->count();
            if ($clockInCount == 0) {
                return $this->outputError("No clocking record");
            }
            if ($approveDetail) {
                foreach ($approveDetail as $id => $value) {
                    $update = [];
                    if (strtolower($value) == 'yes') {
                        $update['pic_approve_status'] = 'yes';
                    } else {
                        $update['pic_approve_status'] = 'no';
                        $update['pic_approve_reject_reson'] = $value;
                    }
                    ColClockin::model()->where("id", $id)->update($update);
                }
            }
            $res = ColClockinApprove::model()->createModel([
                "order_id" => $orderId,
                "date" => $date,
                "approve_status" => $action == "PASS" ? ColClockinApprove::STATUS_APPROVE_PASS : ColClockinApprove::STATUS_APPROVE_FAILED,
                "admin_id" => LoginHelper::getAdminId(),
                "remark" => array_get($params, "remark", ""),
            ]);
            if ($res) {
                # 每天完成4次打卡中的3次既可获得优惠券奖励，
                if ($clockInCount >= 3 && $res->approve_status == ColClockinApprove::STATUS_APPROVE_PASS) {
                    $order = Order::model()->getOne($orderId);
                    if ($order) {
                        $resCoupon = $this->coupon($order->user);
                        if ($resCoupon) {
                            $res->coupon = 1; # 发券成功
                        } else {
                            $res->coupon = 2; # 发券失败
                        }
                        $res->save();
                    }
                }
                return $this->outputSuccess();
            } else {
                return $this->outputError("Failure");
            }
        }
        return $this->outputError("Parameter exception");
    }

    public function coupon(User $user) {
        $colUserCoupon = ColUserCoupon::model()->where("user_id", $user->id)->first();
        if ($colUserCoupon) {
            $coupon = Coupon::model()->getOne($colUserCoupon->coupon_id);
            if ($coupon) {
                $params['coupon_id'] = $colUserCoupon->coupon_id;
                $params['user_id'] = $user->id;
                $params['coupon_task_id'] = 0;
                $res = CouponReceiveServer::server()->addCouponReceive($params);
                if ($res->isSuccess()) {
                    return true;
                }
            }
        }
        return false;
    }

}
