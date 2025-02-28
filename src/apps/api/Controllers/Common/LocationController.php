<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Common;

use Common\Response\ApiBaseController;
use Common\Utils\Data\DistrictCodeHelper;

class LocationController extends ApiBaseController
{
    public function districtCode()
    {
        $provinceId = $this->getParam('province_id');
        if ($provinceId) {
            $cityCode = DistrictCodeHelper::cityCode();

            if (isset($cityCode[$provinceId])) {
                return $this->resultSuccess('', $cityCode[$provinceId]);
            } else {
                //港澳台等省份没二级市返回空
                return $this->resultSuccess([]);
            }
        }

        return $this->resultSuccess('', DistrictCodeHelper::shengCode());
    }
}