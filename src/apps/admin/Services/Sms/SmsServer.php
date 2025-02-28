<?php

/**
 * Created by PhpStorm.
 * User: Nio
 * Date: 2021/02/24
 * Time: 10:02
 */

namespace Admin\Services\Sms;

use Admin\Services\BaseService;
use Common\Models\Sms\SmsTpl;
use Maatwebsite\Excel\Facades\Excel;
use Common\Utils\LoginHelper;
use Admin\Imports\Sms\SmsTplImport;
use Common\Utils\MerchantHelper;

class SmsServer extends BaseService {

    const EVENT_MAP = [
        '4560A8' => '注册验证码',
        '0C9706' => '修改密码',
        '096DA8' => '审批通过',
        '219851' => '线下取款提醒',
        'B64AAD' => '线上放款成功',
        'B64DDD' => '线下取款成功',
        'B64ADD' => '线下未取款再次提醒',
        '65A8AF' => '到期T-2提醒',
        '65A8AD' => '到期T0提醒',
        'B49B8B' => '逾期T1提醒',
        'E84A90' => '逾期T+3提醒',
        'ACCFCD' => '结清营销',
        'ACCFCE' => '展期预约',
        'B64AAA' => '补件',
    ];

    public function import($params) {
        $res = Excel::toArray(new SmsTplImport, $params['file']);
        $success = 0;
        foreach ($res[0] as $i => $item) {
            if ($i > 0) {
                $res = SmsTpl::model()->updateOrCreateModel([
                    "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                    "tpl_type" => $item[0],
                    "tpl_content" => $item[1],
                    "admin_id" => LoginHelper::getAdminId(),
                    "updated_at" => \Common\Utils\Data\DateHelper::dateTime(),
                        ],
                        [
                            "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                            "tpl_type" => $item[0],
                ]);
                if ($res) {
                    $success++;
                }
            }
        }
        return "Success total {$success}.";
    }

    public function load($tplType) {
        return SmsTpl::model()->newQuery()->where("tpl_type", $tplType)->get()->first();
    }

    public function save($params) {
        $res = SmsTpl::model()->updateOrCreateModel([
            "merchant_id" => MerchantHelper::helper()->getMerchantId(),
            "tpl_type" => $params['tpl_type'],
            "tpl_content" => $params['tpl_content'],
            "admin_id" => LoginHelper::getAdminId(),
            "updated_at" => \Common\Utils\Data\DateHelper::dateTime(),
                ],
                [
                    "merchant_id" => MerchantHelper::helper()->getMerchantId(),
                    "tpl_type" => $params['tpl_type'],
        ]);
        return $res;
    }

    public function typeList() {
        return SmsTpl::model()->newQuery()->pluck("tpl_type")->unique();
    }

    public function list() {
        return SmsTpl::model()->newQuery()->select("id", "tpl_type", "tpl_content")->get()->toArray();
    }

    public function getTplByEventId($eventId) {
        if (isset(self::EVENT_MAP[$eventId])) {
            $res = SmsTpl::model()->where("tpl_type", self::EVENT_MAP[$eventId])->first();
            if($res && $res->tpl_content){
                $res->tpl_content = str_replace("\xc2\xa0", ' ', $res->tpl_content);
                return $res->tpl_content;
            }
        }
        return false;
    }

}
