<?php

namespace Common\Services\User;

use Common\Models\User\UserThirdData;
use Common\Services\BaseService;

class UserThirdDataServer extends BaseService
{
    protected function getDataByType($userId, $type)
    {
        return (new UserThirdData())->getByType($userId, $type);
    }

    public function getAadhaarCardKycResult($userId)
    {
        $model = $this->getDataByType($userId, UserThirdData::TYPE_AADHAAR_CARD_KYC);

        return optional($model)->result;
    }
}
