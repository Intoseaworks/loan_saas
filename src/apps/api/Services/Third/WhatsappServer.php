<?php

/**
 * Created by PhpStorm.
 * User: wz
 * Date: 20200813
 * Time: 11:24
 */

namespace Api\Services\Third;

use Api\Models\User\User;
use Api\Models\User\UserContact;
use Common\Services\BaseService;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\Third\AirudderHelper;
use Common\Utils\Whatsapp\WhatsappHelper;
use Risk\Common\Models\Third\ThirdDataWhatsapp;
use Yunhan\Utils\Env;


class WhatsappServer extends BaseService
{

    /**
     * @param $telephone
     * @return booleam
     */
    public function check($telephone)
    {
        $res = ThirdDataWhatsapp::model()->check($telephone);
        if (ThirdDataWhatsapp::STATUS_SKIP == $res) {
            if (LockRedisHelper::helper()->addLock("lock:whatsapp:{$telephone}", 120)) {
                if (Env::isProd()) {
                    //$this->send($telephone);
                }
            }
        }
        return $res;
    }

    public function send($telephone)
    {
        return true;
        $query = ThirdDataWhatsapp::model()->findByTelephone($telephone);
        if ($query) {
            return true;
        }
        $telephone = ThirdDataWhatsapp::model()->areaCode . $telephone;
        $telephone = [$telephone];
        $res = (new WhatsappHelper())->post($telephone);
        $res = json_decode($res, true);
        if ($res['code'] != 200) {
            //停止充值提醒
            //DingHelper::notice(json_encode($res), "Whatsapp error");
        }
        return true;
    }

    /**
     * 确认用户本人以及紧急联系人号码是否wa，否则用Airudder
     * @param User $user
     * @param int $limit
     * @return boolean
     */
    public function checkUserAndContact(User $user, $limit = 2)
    {
        $contacts = UserContact::model()->newQuery()->where("user_id", '=', $user->id)->orderByDesc('id')->limit($limit)->get();
        $phones = [];
        $phones[] = $user->telephone;
        foreach ($contacts as $contact) {
            $phones[] = $contact->contact_telephone;
        }
        $res = true;
        foreach ($phones as $phone) {
            $res = ThirdDataWhatsapp::model()->simpleCheck($phone);
            if (!$res) {
                $res = false;
                break;
            }
        }
        if (!$res) {
            foreach ($phones as $phone) {
                //AirudderHelper::helper()->query($phone);
            }
        }
        return $res;
    }

}
