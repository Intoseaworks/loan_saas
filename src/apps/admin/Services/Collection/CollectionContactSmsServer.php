<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Models\Collection\Collection;
use Admin\Models\Collection\CollectionContact;
use Admin\Models\Collection\CollectionContactSms;
use Admin\Models\Order\Order;
use Admin\Services\BaseService;
use Admin\Services\Risk\RiskServer;
use Common\Jobs\Crm\CollectionContactSmsJob;
use Common\Models\Common\Config;
use Common\Models\Merchant\App;
use Common\Models\UserData\UserContactsTelephone;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\LoginHelper;
use Common\Utils\Sms\SmsHelper;

class CollectionContactSmsServer extends BaseService
{
    /**
     * @param $data
     * @return CollectionContact|bool|void
     * @throws \Common\Exceptions\ApiException
     */
    public function create($data)
    {
        //按催收设置看发短信数到上限则不能发
        $data['user_id'] = LoginHelper::getAdminId();
        $data['type'] = CollectionContactSms::TYPE_SEND;
        $collectionContact = CollectionContact::model()->getOne($data['contact_id']);
        $order = Order::model()->getOne($collectionContact->order_id);
        if (!$this->checkSmsLimits($order->id,$data['user_id'])){
            return false;
        };
        $data['order_id'] = $order->id;
        //防止用户多次点击按钮或调用接口
        if (!LockRedisHelper::helper()->addLock('collection-contact-sms'.$data['user_id'].$data['type'].$data['contact_id'].$data['order_id'],2)){
            return "The order is sending message now ,please wait!";
        }
        $collectionContactSms = CollectionContactSms::model()->create($data);
        if ($collectionContactSms) {
            $appId = $order->app_id;
            $appList = App::model()->getNormal();
            $app = $appList->where('id', $appId)->first();
            $appName = array_get($app, 'send_id', '');
            $url = array_get($app, 'app_url', '');
            $subject = CalcRepaymentSubjectServer::server($order->lastRepaymentPlan)->getSubject();
            $repaymentAmount = $subject->repaymentPaidAmount;
            dispatch(new CollectionContactSmsJob($collectionContactSms, [
                'fullname' => $collectionContactSms->fullname, 'principal' => $order->principal, 'day' => $order->getAppointmentPaidTime(), 'url' => $url, 'appName' => $appName, 'amount' => $repaymentAmount,
                'reference_no' => $order->reference_no,'dragonpay_lifetimeID' => $order->user->userInfo->dg_pay_lifetime_id
            ]));
            return $collectionContactSms;
        }else {
            return false;
        }
    }

    /**
     * @param $collection
     * @param int $num
     * @return mixed
     */
    public function getMessageContact($collection, $num = 50)
    {
        if ($num > CollectionContact::model()->getCollectionContactsCount($collection->order_id, CollectionContact::TYPE_MESSAGE_CONTACT)) {
            $concatTelephoneCallDatas = (new UserContactsTelephone())->getContacts($collection->user_id, $num);
            CollectionContactServer::server()->saveTelephoneCall($collection, $concatTelephoneCallDatas->toArray());
        }
        return CollectionContact::model()->getCollectionContacts($collection->id, $num, CollectionContact::TYPE_MESSAGE_CONTACT);
    }

    /**
     * @param $collection
     * @param $datas
     */
    public function saveTelephoneCall($collection, $datas)
    {
        if (!is_array($datas)) {
            return false;
        }
        $oldTelephones = CollectionContactServer::server()->cleckTelephones($collection->order_id, array_column($datas, 'receive_phone'))->toArray();
        $oldTelephonesCount = count($oldTelephones);
        foreach ($datas as $data) {
            if ($oldTelephonesCount && in_array($data['receive_phone'], $oldTelephones)) {
                continue;
            }
            $userContactData = [
                'order_id' => $collection->order_id,
                'user_id' => $collection->user_id,
                'collection_id' => $collection->id,
                'type' => CollectionContact::TYPE_MESSAGE_CONTACT,
                'fullname' => $data['contact_fullname'],
                'contact' => $data['contact_telephone'],
                'content' => json_encode($data, 256),
            ];
            CollectionContact::model()->create($userContactData);
        }
    }


    public function checkSmsLimits($orderId, $adminId)
    {
        $collection_max_deduction_sms_messages_per_order = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_SMS_MESSAGES_PER_ORDER);
        $collection_max_deduction_staff_sms_amount_per_day = Config::getValueByKey(Config::KEY_COLLECTION_MAX_DEDUCTION_STAFF_SMS_AMOUNT_PER_DAY);
        //单笔订单最多可发催收短信数每天并且单个催收员每日最多可发催收短信数
        if (CollectionContactSms::model()->newQueryWithoutScopes()
                ->where('order_id', $orderId)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                ->count() < $collection_max_deduction_sms_messages_per_order && CollectionContactSms::model()->newQueryWithoutScopes()
                ->where('order_id', $orderId)
                ->whereBetween('created_at',[date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
                ->count() < $collection_max_deduction_sms_messages_per_order) {
            return true;
        }
//        //单笔订单最多可发催收短信数每天
//        if (CollectionContactSms::model()->newQueryWithoutScopes()
//                ->where('order_id', $orderId)
//                ->whereBetween('created_at',[date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
//                ->count() < $collection_max_deduction_sms_messages_per_order) {
//            return true;
//        }
//        //单个催收员每日最多可发催收短信数
//        if (CollectionContactSms::model()->newQueryWithoutScopes()
//                ->where('user_id', $adminId)
//                ->whereBetween('created_at',[date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
//                ->count() < $collection_max_deduction_staff_sms_amount_per_day){
//            return true;
//        }
        return false;
    }

}
