<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/18
 * Time: 10:53
 */

namespace Admin\Services\User;

use Api\Models\User\UserAuth;
use Api\Models\User\UserContact;
use Admin\Models\Order\Order;
use Api\Services\BaseService;
use DB;

class UserInfoServer extends BaseService {

    public function createUserContact($data) {
        //Get phone number exists
        $order = Order::model()->getOne(array_get($data, 'orderId'));
        if(null === $order){
            return $this->output(self::OUTPUT_ERROR, "The order already exists!");
        }
        $userId = $order->user_id;
        if (UserContact::model()->isExist($userId, array_get($data, 'contactTelephone'))) {
            return $this->output(self::OUTPUT_ERROR, "The " . array_get($data, 'contactTelephone') . " number already exists!");
        }
        
        DB::transaction(function () use ($data, $userId) {
            UserContact::model()->setScenario(UserContact::SCENARIO_CREATE)->saveModels([
                [
                    'user_id' => $userId,
                    'contact_fullname' => array_get($data, 'contactFullname'),
                    'contact_telephone' => array_get($data, 'contactTelephone'),
                    'relation' => array_get($data, 'relation'),
                ]
                    ], true, true);
            UserAuthServer::server()->setAuth($userId, UserAuth::TYPE_CONTACTS);
        });
        return $this->outputSuccess();
    }

}
