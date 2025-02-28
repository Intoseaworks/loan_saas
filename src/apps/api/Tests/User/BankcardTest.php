<?php

namespace Api\Tests\User;

use Api\Tests\TestBase;

class BankcardTest extends TestBase
{
    public function testLookUpIfsc()
    {
        $params = [
            'bank' => 'IDBI BANK',
            'state' => 'KERALA',
            'district' => 'MALAPPURAM',
            'city' => 'MALAPPURAM',
            'size' => '5',
        ];
        $this->seeGetRequest('/app/bankcard/look-up-ifsc', $params);
    }

    public function testGetBranch()
    {
        $params = [
            'state' => 'MAHARASHTRA',
            'city' => 'MUMBAI',
        ];
        $this->seeGetRequest('/app/bankcard/get-branch', $params);
    }

    public function testCreate()
    {
        $params = [
            'card_no' => '6217002920131968101',
            'ifsc' => 'ABHY0065006',
        ];
        $this->seePostRequest('/app/bankcard/create', $params);
    }
}
