<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 11:30
 */

namespace Common\Services\Push;

use Common\Helper\Push\EmailHelper;
use Common\Models\Collection\Collection;
use Common\Models\Collection\CollectionSetting;
use Common\Models\Email\EmailLog;
use Common\Models\Email\EmailTpl;
use Common\Models\Email\EmailUser;
use Common\Models\Order\Order;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class EmailServer extends BaseService {

    public function collectionSend($collectionId) {
        try {
            $collection = Collection::model()->getOne($collectionId);
            if (!$modelTpl) {
                return $this->outputError("催收案件不存在");
            }
            $order = Order::model()->getOne($collection->order_id);
            $user = $collection->user;
            MerchantHelper::setMerchantId($collection->merchant_id);
            $ruleData = json_decode(CollectionSetting::model()->getSettingVal(CollectionSetting::KEY_RULE), true);
            $sendEmail = "";
            $replaceItem = [];
            $replaceItem['customer_name'] = $user->fullname;
            $replaceItem['order_no'] = $order->order_no;
            $replaceItem['receivable_amount'] = $order->getPaidPrincipal();
            $replaceItem['appointment_paid_time'] = DateHelper::format($order->getAppointmentPaidTime(true), 'd-m-Y');
            $replaceItem['overdue_days'] = strval($order->getOverdueDays());
            $replaceItem['reference_no'] = $user->userInfo->dg_pay_lifetime_id ?: $order->reference_no;
            foreach ($ruleData as $item) {
                if ($item['overdue_level'] == $collection->level) {
                    $sendEmail = $item['email_user'];
                    break;
                }
            }
            $modelTpl = EmailTpl::model()->newQuery()->where("collection_level", $collection->level)->first();
            if (!$modelTpl) {
                return $this->outputError("模板不存在");
            }
            DB::beginTransaction();
            $emailUser = EmailUser::model()->newQuery()->lockForUpdate()->where("max_send", ">", "use_count")->whereIn("email", $sendEmail)->first();
            if (!$modelTpl) {
                return $this->outputError("没有可发送邮箱");
            }
            $content = $modelTpl->content;
            foreach ($replaceItem as $k => $v) {
                $content = str_replace("{{" . $k . "}}", $v, $content);
            }
            $res = EmailHelper::send($emailUser->email, $user->userInfo->email, $modelTpl->subject, $content);

            if ($res) {
                if ($emailUser->use_date != date("Y-m-d")) {
                    $emailUser->use_date = date("Y-m-d");
                    $emailUser->use_count = 0;
                }
                $emailUser->use_count += 1;
            }
            $log = [
                "merchant_id" => $collection->merchant_id,
                "email_user_id" => $emailUser->id,
                "user_id" => $collection->user_id,
                "order_id" => $collection->order_id,
                "tpl_id" => $modelTpl->id,
                "subject" => $modelTpl->subject,
                "content" => $content,
                "result" => $res ? 1 : 0
            ];
            EmailLog::model()->create($log);
            $emailUser->save();
            DB::commit();
            return $this->outputSuccess("Success");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->outputError($e->getMessage());
        }
    }

}
