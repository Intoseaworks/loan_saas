<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\User;

use Admin\Models\User\UserBlack;
use Admin\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;

class UserBlackServer extends BaseService
{

    public function addCannotAuth($telephone, $remark, $expireTime = null)
    {
        $data['type'] = UserBlack::TPYE_CANNOT_AUTH;
        $data['remark'] = $remark;
        $data['expire_time'] = $expireTime;
        $this->add($telephone, $data);
    }

    public function addCannotRegister($telephone, $remark, $expireTime = null)
    {
        $data['type'] = UserBlack::TPYE_CANNOT_REGISTER;
        $data['remark'] = $remark;
        $data['telephone'] = $telephone;
        $data['merchant_id'] = 0;
        if (UserBlack::model()->getByData($data)) {
            return true;
        }
        $data['black_time'] = DateHelper::dateTime();
        $data['expire_time'] = $expireTime;
        return UserBlack::model()->add($data);
    }

    public function add($telephone, $data)
    {
        $data['telephone'] = $telephone;
        $data['merchant_id'] = MerchantHelper::getMerchantId();
        $data['black_time'] = DateHelper::dateTime();
        return UserBlack::model()->add($data);
    }

}
