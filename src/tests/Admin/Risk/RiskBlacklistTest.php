<?php

namespace Tests\Admin\Risk;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class RiskBlacklistTest extends TestBase
{
    /**
     * 风控黑名单列表
     */
    public function testRiskBlacklistIndex()
    {
        $params = [
            'keyword' => 'TELEPHONE',
            'value' => '',
            'is_global' => 'N',
            'apply_id' => '1619',
            'black_reason' => '贷后逾期入黑',
        ];
        $this->json('GET', '/api/risk-blacklist/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData(true, true);
    }

    /**
     * 风控黑名单详情
     */
    public function testRiskBlacklistDetail()
    {
        $params = [
            'id' => 652,
        ];
        $this->json('GET', '/api/risk-blacklist/detail', $params)
            ->seeJson(['code' => self::SUCCESS_CODE])
            ->getData(true, true);
    }
}
