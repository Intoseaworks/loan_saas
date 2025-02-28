<?php

namespace Tests\Admin\Collection;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class CollectionSettingTest extends TestBase
{
    /**
     * 获取设置
     */
    public function testIndex()
    {
        $params = [
            'key' => 'rule',
        ];
        $this->json('GET', '/api/collection_setting/index', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 添加规则设置
     */
    public function testRule()
    {
        $params = [
            'rule' => [
                [
                    'overdue_days' => 5,
                    'overdue_level' => 'S1',
                    'contact_num' => 20,
                    'admin_ids' => [1, 2],
                    'reduction_setting' => 'cannot',
                ],
                [
                    'overdue_days' => 10,
                    'overdue_level' => 'S2',
                    'contact_num' => 20,
                    'admin_ids' => [1, 2],
                    'reduction_setting' => 'cannot',
                ],
            ],
            'collection_bad_days' => 199,
        ];
        /*$this->json('POST', '/api/collection_setting/create_rule', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);*/
        $this->json('POST', '/api/collection_setting/rule', $params);
        var_dump($this->getData());
    }


}