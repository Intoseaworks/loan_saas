<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\User;

use Api\Models\User\UserAuthBlack;
use Admin\Services\BaseService;
use Api\Models\User\User;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;

class UserAuthBlackServer extends BaseService
{

    public function addAadharrBlack($aadhaarNo, $userId, $remark)
    {
        $data['type'] = UserAuthBlack::TPYE_AADHAAR;
        $data['black_time'] = DateHelper::dateTime();
        $data['user_id'] = $userId;
        $data['remark'] = $remark;
        $this->add($aadhaarNo, $data);
    }

    public function add($aadhaarNo, $data)
    {
        $data['receive'] = $aadhaarNo;
        $data['merchant_id'] = MerchantHelper::getMerchantId();
        return UserAuthBlack::model()->add($data);
    }

}
