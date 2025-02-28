<?php

namespace Risk\Common\Services\Risk;

use Risk\Common\Models\Business\Order\Order;
use Common\Services\BaseService;
use Risk\Common\Models\Business\RiskData\RiskDataIndex;
use Common\Utils\Nbfc\kudos\Kudos21;
use Common\Models\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserApplication;
use Common\Utils\Third\KreditoneHelper;

class RiskThirdDataServer extends BaseService {

    public function getKudosLoanByOrder(Order $order) {
        $config = [
            "Authorization" => "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55X2lkIjozNCwiY29tcGFueV9jb2RlIjoiRElHMDAxNyIsInR5cGUiOiJzZXJ2aWNlIiwidG9rZW5faWQiOiIzNC1ESUcwMDE3LTE1OTkxMjk1NjUyNzMiLCJlbnZpcm9ubWVudCI6InByb2QiLCJpYXQiOjE1OTkxMjk1NjV9.gAXFivhnOQGByJ7zTkNPb4Zrgqf0_08C73iu36R7w4U",
            "company_code" => "DIG0017",
            "domain" => "http://elaap.kudosfinance.in:5000",
        ];
        $kudos = new Kudos21($config);
        $data['pan'] = $order->user->userInfo->pan_card_no;
        $data['sub_company_code'] = $config['company_code'];
        $res = $kudos->post($data, "/api/riskcoverpan");
        $res = json_decode($res, true);
        $insertData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => "kudos_app_count",
            "index_value" => $res['app_count'] ?? "",
            "comment" => $data['pan']
        ];
        $relateData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => "kudos_app_count",
        ];
        RiskDataIndex::updateOrCreate($relateData, $insertData);
        $insertData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => "kudos_loan_count",
            "index_value" => $res['loan_count'] ?? "",
            "comment" => $data['pan']
        ];
        $relateData = [
            "user_id" => $order->user_id,
            "order_id" => $order->id,
            "index_name" => "kudos_loan_count",
        ];
        RiskDataIndex::updateOrCreate($relateData, $insertData);
        return ["app_count" => $res['app_count'] ?? "", "loan_count" => $res['loan_count'] ?? ""];
    }

    public function getKreditOneScore(Order $order) {
        $dataPost = [
            "queryId" => date("YmdHis"),
            "uid" => $order->user_id,
            "applyTime" => date("Y-m-d H:i:s"),
            "birthday" => date("Y-m-d", strtotime(str_replace("/", "-", $order->user->userInfo->birthday))),
            "appDatas" => [],
            "contactCount" => UserContactsTelephone::model()->getContactsCount($order->user_id, $order->created_at),
        ];
        $appList = UserApplication::model()->getLastVersionData($order->user_id, $order->created_at);
        foreach ($appList as $item) {
            $dataPost['appDatas'][] = [
                "firstTime" => date("Y-m-d H:i:s", $item['installed_time'] / 1000),
                "lastTime" => date("Y-m-d H:i:s", $item['updated_time'] / 1000),
                "packageName" => $item['pkg_name'],
            ];
        }
        $res = KreditoneHelper::helper()->post($dataPost, "/api/modelscore/task");
        $data = [];
        if ("0" == $res["code"]) {
            $insertData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => "kreditone_score",
                "index_value" => $res['data']['score'],
            ];
            $relateData = [
                "user_id" => $order->user_id,
                "order_id" => $order->id,
                "index_name" => "kreditone_score",
            ];
            RiskDataIndex::updateOrCreate($relateData, $insertData);
            return $res['data']['score'];
        } else {
            return false;
        }
    }

}
