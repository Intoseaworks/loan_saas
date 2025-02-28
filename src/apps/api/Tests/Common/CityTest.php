<?php

namespace Api\Tests\Common;

use Api\Tests\TestBase;

class CityTest extends TestBase
{
    public function testGetCity()
    {
        $params = [

        ];
        $this->seeGetRequest('/app/city/get-city', $params);
    }

    public function testResidentialCity()
    {
        $this->seeGetRequest('app/city/residential-city');
    }

    public function testT()
    {
        $data = $this->getOldDbConnect('city')->get();

        $res = [];
        foreach ($data as $item) {
            $res[$item->state][] = $item->city;
        }

        dd(json_encode($res));
    }
}
