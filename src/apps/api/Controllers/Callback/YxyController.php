<?php

namespace Api\Controllers\Callback;

use Common\Response\ServicesApiBaseController;
use Common\Services\Collection\CollectionServer;
use Admin\Models\Order\Order;
use Common\Models\Collection\CollectionAiYxy;
use Common\Services\Third\YixiuyunServer;
use Common\Models\Collection\CollectionSetting;
use Common\Utils\MerchantHelper;

class YxyController extends ServicesApiBaseController {

    /**
     * 品牌返回 => 指定催收员
     */
    const MAP = [
        "3" => 406,#u1
        "2" => 407,#u4
        "1" => 550,#u5
        '4' => 485,#s2
    ];
    /**
     * 来自催收员 => 指定催收员
     */
    const USER_MAP = [
        "340" => [513],#u4
        "339" => [304],#u4
        "580" => [550],#u5
        '583' => [485],#s2
        "597" => [485],#s2
        "603" => [0],#s2
        "539" => [540],#u5
        "379" => [542],#S2
    ];

    public function webhook() {
        $params = $this->getParams();
        if ($params['tid'] && $params['status']) {
            CollectionAiYxy::model()->createModel(["tid" => $params['tid'], "status" => $params['status']]);
            $order = Order::model()->getOne($params['tid']);
            if ($collection = $order->collectionUnfinished) {
                if (isset(self::MAP[$order->merchant_id]) && $params['status'] == '1') {
                    if ($collection->admin_id && in_array($collection->admin_id, YixiuyunServer::server()->adminIds)) {
                        # 设置品牌
                        MerchantHelper::helper()->setMerchantId($collection->merchant_id);
                        $rules = CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE);
                        $rules = json_decode($rules, true);
                        $rule = [
                            "overdue_level" => $collection->level,
                            "contact_num" => 20,
                            "reduction_setting" => "cannot",
                        ];
                        foreach($rules as $item){
                            if($item['overdue_level'] == $collection->level){
                                $rule = $item;
                                break;
                            }
                        }
                        $toAdminId = self::MAP[$order->merchant_id];
                        if (isset(self::USER_MAP[$collection->admin_id])) {
                            $toAdminId = self::USER_MAP[$collection->admin_id][array_rand(self::USER_MAP[$collection->admin_id])];
                        }
                        if($toAdminId > 0){
                            $collection = CollectionServer::server()->againCollection($collection, $toAdminId, $rule);
                        }
                    }
                }
            }
            echo 'success';
        }
    }

}
