<?php

namespace Api\Services\Common;

use Api\Services\BaseService;
use Common\Models\Common\BankInfo;
use Common\Models\Common\Dict;
use Common\Utils\CityHelper;

class CityServer extends BaseService {

    public function getCityList($parentCode = 'DISTRICT') {
        return Dict::model()->getKeyVal($parentCode);
    }

    public function getBankCityList($parentCode = '') {
        return Dict::model()->getList(["parent_code" => $parentCode]);
    }

    public function checkCity($city)
    {
        return CityHelper::helper()->getCity($city);
    }

}
