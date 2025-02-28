<?php

namespace Api\Controllers\Common;

use Api\Services\Common\CityServer;
use Common\Response\ServicesApiBaseController;
use Common\Utils\Baidu\BaiduApi;
use Common\Utils\CityHelper;
use Common\Utils\Data\ArrayHelper;

class CityController extends ServicesApiBaseController {

    public function getCity() {
        $parentCode = $this->getParam('pcode', 'DISTRICT');
        $cityList = CityServer::server()->getCityList($parentCode);

        return $this->resultSuccess($cityList, '');
    }

    public function positionAddress() {
        $user = $this->identity();
        $position = $this->getParam('position');
        $position = ArrayHelper::jsonToArray($position);
        $longitude = array_get($position, 'longitude');
        $latitude = array_get($position, 'latitude');

        $mapsData = BaiduApi::getAddress($latitude, $longitude);
        if (!$mapsData || !($city = array_get($mapsData, 'city'))) {
            return $this->resultFail('Auto fill failed, please input the address');
        }

        return $this->resultSuccess([
                    'state' => array_get($mapsData, 'province', '') ? array_get($mapsData, 'province', ''): array_get($mapsData, 'state', ''),
                    'city' => array_get($mapsData, 'city', ''),
                    'address' => array_get($mapsData, 'address'),
                    'detail' => $mapsData,
        ]);
    }

}
