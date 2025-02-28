<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/18
 * Time: 20:07
 */

namespace Common\Services\Third;

use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionThirdLog;
use Common\Models\Merchant\App;
use Common\Models\Trade\TradeLog;
use Common\Services\BaseService;
use Common\Utils\Yixiuyun\YixiuyunApi;
use Common\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Common\Services\Order\OrderPayServer;
use Common\Utils\Data\DateHelper;

class YixiuyunServer extends BaseService {
    /* 分给一休云的AdminIds */

    public $adminIds = [380, 108];#0908撤回341,340,339,721, 471, 305, 304, 539, 723, 379
    private $adminId = "0";
    /* 是否分配，判断adminID是否在adminIds数组内 */
    private $_pass = true;

    public function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    public function checkAdminId($adminId) {
        $this->_pass = in_array($adminId, $this->adminIds);
        if ($this->_pass) {
            $this->adminId = $adminId;
        }
        return $this;
    }

    public function isPass() {
        return $this->_pass;
    }

    /**
     * 数据传输
     * @param Order $order
     * @return mixed|void
     */
    public function push_agent_coll(Order $order) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $userContact = $order->user->userContacts()->get()->toArray();
        $data = [
            "tid" => $order->id,
            "name" => $order->user->fullname,
            "idcard" => $order->user->id_card_no,
            "sex" => isset($order->userInfo->gender) && $order->userInfo->gender == "Male" ? 1 : 0,
            "phone" => $order->user->telephone,
            "amount" => $order->repayAmount(),
            "overdue_day" => $order->getOverdueDays(),
            "sr_principal" => $order->principal,
            // 动态获取平台名称
            "platform" => $order->app_client == "FX" ? $order->app_client . "-" . $order->app_version : App::getDataById(($order->app_id), 'app_name'),
            "languageCode" => "en-IN",
            "other_text" => isset($order->reference_no) ? $order->reference_no : "NULL"
        ];
        $fx = new YixiuyunApi();
        $res = $fx->post_coll($data, $fx->_push_url);
        CollectionThirdLog::model()->createModel([
            "third_name" => "yxy_push",
            "order_id" => $order->id,
            "admin_id" => $this->adminId,
            "apply_data" => json_encode($data) . "===" . json_encode($res)
        ]);
        return $res;
    }

    public function push_agent_coll_list($collections) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        echo "Run sum:" . count($collections) . PHP_EOL;
        $oidList = $dataList = [];
        foreach ($collections as $collection) {
            $order = $collection->order;
            $oidList[] = $order->id;
            $overdueDays = $order->getOverdueDays();
            if(DateHelper::format($order->getAppointmentPaidTime(true), 'Y-m-d') == date("Y-m-d")){
                $overdueDays = 0;
            }
            $addressBook = [];
            $i = 0;
            if (in_array($collection->admin_id, [723, 721, 603])) {
                foreach ($userContacts = $order->user->userContacts as $userContact) {
                    $i++;
                    $addressBook[] = [
                        'name' => $userContact->contact_fullname,
                        'phone' => $userContact->contact_telephone,
                        'relation' => "朋 友",
                        'sort' => $i
                    ];
                }
//                print_r($addressBook);
            }
            $dataList[] = [
                "tid" => $order->id,
                "name" => $order->user->fullname,
                "idcard" => $order->user->id_card_no,
                "sex" => isset($order->userInfo->gender) && $order->userInfo->gender == "Male" ? 1 : 0,
                "phone" => $order->user->telephone,
                "amount" => $order->repayAmount(),
                "overdue_day" => $overdueDays,
                "sr_principal" => $order->principal,
                // 动态获取平台名称
                "platform" => App::getDataById(($order->app_id), 'app_name'),
                "languageCode" => "en-IN",
                "other_text" => isset($order->reference_no) ? $order->reference_no : "NULL",
                "address_book" => json_encode($addressBook),
                "account_info" => $collection->staff->username
            ];
        }
        $fx = new YixiuyunApi();
        $res = $fx->post_coll_list($dataList, $fx->_push_url);
        CollectionThirdLog::model()->createModel([
            "third_name" => "yxy_push",
            "order_ids" => json_encode($oidList),
            "admin_id" => $this->adminId,
            "apply_data" => json_encode($res)
        ]);
        echo "over" . PHP_EOL;
        return $res;
    }

    /**
     * 数据停拨
     * @param Collection $collection
     * @return mixed|void
     */
    public function stop_agent_coll(Collection $collection) {
        //判断是否有分配飞象
        if (!$this->_pass) {
            return;
        }
        $order = $collection->order;
        /* 汇总已还金额 */
        $money = TradeLog::model()
                        ->where("trade_result_time", ">=", $collection->assign_time)
                        ->where("master_related_id", "=", $order->id)
                        ->where("business_type", "=", "repay")
                        ->where("trade_result", "=", "1")
                        ->select(DB::raw("sum(trade_amount) as money"))->first();
        $data = [
            "tid" => $order->id,
            "phone" => $order->user->telephone,
            "money" => isset($money["money"]) ? $money["money"] : "0",
            //动态获取平台名称
            "platform" => $order->app_client == "FX" ? $order->app_client . "-" . $order->app_version : App::getDataById(($order->app_id), 'app_name'),
        ];
        $fx = new YixiuyunApi();
        $res = $fx->post_coll($data, $fx->_stop_url);
        if ("0" == $res['errCode']) {
            CollectionThirdLog::model()->createModel([
                "third_name" => "yxy_stop",
                "order_id" => $order->id,
                "admin_id" => $this->adminId,
                "apply_data" => json_encode($data) . "===" . json_encode($res)
            ]);
        }
        return $res;
    }

}
