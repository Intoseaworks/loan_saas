<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\SaasMaster;

use Common\Models\Merchant\App;
use Tests\Api\TestBase;

class MerchantTest extends TestBase
{
    public function testGenerateAppKey()
    {
        $appKey = App::generateAppKey();

        dd($appKey);
    }

    /**
     * 初始化商户
     */
    public function testAuthTime()
    {
        $params = [
            'merchantId' => '2',
            'adminUsername' => 'admin' . rand(9, 99),
            'adminPassword' => '123456',
        ];

        $this->sign($params);

        $this->post('app/saas-master/init-merchant', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 修改商户超管密码
     */
    public function testUpdPassword()
    {
        $params = [
            'merchantId' => 2,
            'adminPassword' => '654321',
        ];

        $this->sign($params);
        $this->post('app/saas-master/upd-password', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 获取商户超管信息
     */
    public function testGetSuperAdmin()
    {
        $params = [
            'merchantId' => 2,
        ];

        $this->sign($params);
        $this->get('app/saas-master/get-super-admin', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }
}
