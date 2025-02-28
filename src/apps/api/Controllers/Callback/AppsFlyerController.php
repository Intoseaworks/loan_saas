<?php

namespace Api\Controllers\Callback;

use Common\Response\ServicesApiBaseController;
use Common\Models\Third\ThirdDataAppsflyer;
use Api\Models\User\User;
use Common\Models\Channel\Channel;
use Common\Utils\MerchantHelper;
use Admin\Models\Order\Order;
use Common\Models\User\UserAfInfo;

class AppsFlyerController extends ServicesApiBaseController {

    public function webhook() {
        MerchantHelper::setMerchantId(1);
        //$res = json_decode('{"bundle_id":"com.SurityCash.hxov","event_source":"SDK","app_version":"1.3.6","af_c_id":"23847508417800664","app_name":"SurityCash","wifi":false,"install_time":"2021-09-17 01:29:11.653","attributed_touch_type":"click","af_adset_id":"23847508440470664","api_version":"2.0","attributed_touch_time":"2021-09-17 01:23:17.000","is_retargeting":false,"af_prt":"adtiger","event_revenue_currency":"USD","media_source":"Facebook Ads","campaign":"Adtiger_SurityCash_Android_ph_wmy_0511","event_value":"{\"01_loan_apply\":\"9310291023##null\"}","ip":"175.176.8.1","event_time":"2021-09-17 07:47:42.092","af_adset":"2","af_ad":"2","device_type":"realme::RMX2103","af_channel":"AudienceNetwork","language":"English","app_id":"com.SurityCash.hxov","event_name":"01_loan_apply","advertising_id":"370c27ac-daf6-45b4-9ddd-5751ded00b82","os_version":"11","af_ad_id":"23847508440480664"}', true);
//        \Illuminate\Support\Facades\Log::info("appsflyer");
//        \Illuminate\Support\Facades\Log::info(json_encode($res));
        $this->webhookSave($this->getParams());
        echo "SUCCESS";
    }

    public function webhook2() {
        MerchantHelper::setMerchantId(2);
        $this->webhookSave($this->getParams());
        echo "SUCCESS";
    }

    private function webhookSave($res) {
        if ($res) {
            if(!isset($res['af_prt']) || (isset($res['af_prt']) && $res['af_prt'] =='')){
                $res['af_prt'] = $res['partner'] ?? "";
            }
            $res['all_data'] = json_encode($res);
            if(!isset($res['media_source'])){
                $res['media_source'] = $res['af_prt'] ?? "NO MediaSource";
            }
            if (isset($res['event_value']) && $res['event_value']) {
                $eventValue = json_decode($res['event_value'], true);
                if (isset($eventValue['01_loan_apply']) && $eventValue['01_loan_apply']) {
                    $sep = "##";
                    $evenParam = explode($sep, $eventValue['01_loan_apply']);
                    if ($evenParam && is_array($evenParam)) {
                        $telephone = $evenParam[0];
                        $orderId = $evenParam[1] ?? "";
                        $userModel = User::model()->getByTelephone($telephone);
                        // 存储 af info
                        if($userModel && isset($res['advertising_id'])){
                            if(!UserAfInfo::model()->where("user_id", $userModel->id)->exists()){
                                UserAfInfo::model()->createModel([
                                    "user_id" => $userModel->id,
                                    "advertising_id" => $res['advertising_id']
                                ]);
                            }
                        }
                        $orderModel = Order::model()->getOne($orderId);
                        if ($userModel && !$userModel->channel_id) {
                            //$channel = Channel::model()->getByCode($res['media_source']);
                            $channel = Channel::model()->updateOrCreateModel([
                                "channel_code" => $res['media_source'], 
                                "channel_name" => $res['media_source'],
                                "merchant_id" => MerchantHelper::helper()->getMerchantId()], [
                                    "channel_code" => $res['media_source'],
                                    "merchant_id" => MerchantHelper::helper()->getMerchantId()]);
                            if ($channel) {
                                $userModel->channel_id = $channel->id;
                                $userModel->save();
                            }
                        }
                        if($orderModel && !$orderModel->channel_id){
                            $channel = Channel::model()->updateOrCreateModel([
                                "channel_code" => $res['media_source'], 
                                "channel_name" => $res['media_source'],
                                "merchant_id" => MerchantHelper::helper()->getMerchantId()], ["channel_code" => $res['media_source'],
                                "merchant_id" => MerchantHelper::helper()->getMerchantId()]);
                            if ($channel) {
                                $orderModel->channel_id = $channel->id;
                                $orderModel->save();
                            }
                        }
                    }
                }
            }
            $tpl = ['idfv', 'device_category', 'af_sub1', 'customer_user_id', 'is_lat', 'contributor_2_af_prt', 'bundle_id', 'gp_broadcast_referrer', 'contributor_2_touch_time', 'contributor_3_touch_type', 'event_source', 'af_cost_value', 'contributor_1_match_type', 'app_version', 'contributor_3_af_prt', 'custom_data', 'contributor_2_touch_type', 'gp_install_begin', 'city', 'amazon_aid', 'gp_referrer', 'af_cost_model', 'af_c_id', 'attributed_touch_time_selected_timezone', 'selected_currency', 'app_name', 'install_time_selected_timezone', 'postal_code', 'wifi', 'install_time', 'operator', 'attributed_touch_type', 'af_attribution_lookback', 'keyword_match_type', 'af_adset_id', 'device_download_time_selected_timezone', 'contributor_2_media_source', 'contributor_2_match_type', 'api_version', 'attributed_touch_time', 'revenue_in_selected_currency', 'is_retargeting', 'country_code', 'gp_click_time', 'contributor_1_af_prt', 'match_type', 'appsflyer_id', 'dma', 'http_referrer', 'af_sub5', 'af_prt', 'event_revenue_currency', 'store_reinstall', 'install_app_store', 'media_source', 'deeplink_url', 'campaign', 'af_keywords', 'region', 'cost_in_selected_currency', 'event_value', 'ip', 'oaid', 'event_time', 'is_receipt_validated', 'contributor_1_campaign', 'af_sub4', 'imei', 'contributor_3_campaign', 'event_revenue_usd', 'af_sub2', 'original_url', 'contributor_2_campaign', 'android_id', 'contributor_3_media_source', 'af_adset', 'af_ad', 'state', 'network_account_id', 'device_type', 'idfa', 'retargeting_conversion_type', 'af_channel', 'af_cost_currency', 'contributor_1_media_source', 'keyword_id', 'device_download_time', 'contributor_1_touch_type', 'af_reengagement_window', 'af_siteid', 'language', 'app_id', 'contributor_1_touch_time', 'event_revenue', 'af_ad_type', 'carrier', 'event_name', 'af_sub_siteid', 'advertising_id', 'os_version', 'platform', 'af_sub3', 'contributor_3_match_type', 'selected_timezone', 'af_ad_id', 'contributor_3_touch_time', 'user_agent', 'is_primary_attribution', 'sdk_version', 'event_time_selected_timezone', 'all_data', 'compaign_type'];
            foreach ($res as $k => $v) {
                if (!in_array($k, $tpl)) {
                    unset($res[$k]);
                }
            }
            $res['merchant_id'] = MerchantHelper::helper()->getMerchantId();
            ThirdDataAppsflyer::createModel($res);
        }
    }

}
