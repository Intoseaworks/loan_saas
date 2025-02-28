<?php

/**
 * Created by PhpStorm.
 * User: wz
 * Date: 20200813
 * Time: 11:24
 */

namespace Api\Services\Third;

use Common\Services\BaseService;
use Risk\Common\Models\Third\ThirdDataAirudder;
use Common\Utils\Whatsapp\WhatsappHelper;
use Common\Utils\Lock\LockRedisHelper;
use Common\Utils\DingDing\DingHelper;
use Yunhan\Utils\Env;
use Api\Models\User\UserContact;
use Api\Models\User\User;

class AirudderServer extends BaseService {

    /**
     * @param $telephone
     * @return booleam
     */
    public function check($telephone) {
        return ThirdDataAirudder::model()->check($telephone);
    }
    
    public function checkTime($telephone){
        return ThirdDataAirudder::model()->checkTime($telephone);
    }

    public function checkUserAndContact($user) {
        //$contacts = UserContact::model()->newQuery()->where("user_id", '=', $user->id)->orderByDesc('id')->limit(2)->get();
        $phones = [];
        $phones[] = $user->telephone;
        //foreach ($contacts as $contact) {
            //$phones[] = $contact->contact_telephone;
        //}
        foreach ($phones as $phone) {
            if ($this->check($phone)) {
                return $phone;
            }
        }
        return false;
    }

}
