<?php

namespace Api\Tests\Order;

use Api\Tests\TestBase;

class ContractTest extends TestBase
{
    /**
     * 生成合同
     */
    public function testGenerateContract()
    {
        $params = [
            'orderId' => '140',
        ];
        $this->seePostRequest('/app/contract/generate-contract', $params);
    }

    /**
     * 获取合同html
     */
    public function testContractData()
    {
        $params = [
            // 'orderId' => '140',
        ];
        $this->seeGetRequest('/app/contract/contract-data', $params);
    }

    /**
     * 获取合同数据
     */
    public function testGetContract()
    {
        $params = [
            // 'orderId' => '140',
        ];
        $this->seeGetRequest('/app/contract/get-contract', $params);
    }

    public function testSendContract()
    {
        $params = [
            'orderId' => '140',
//            'email' => 'wangshaliang@jiumiaodai.com',
            'email' => '297210725@qq.com',
        ];
        $this->seePostRequest('/app/contract/send-contract', $params);
    }

    /**
     * 获取签名认证方digio的链接
     */
    public function testGetSignUrl()
    {
        $params = [
            'orderId' => '140',
        ];
        $this->seeGetRequest('/app/contract/get-sign-url', $params);
    }
}
