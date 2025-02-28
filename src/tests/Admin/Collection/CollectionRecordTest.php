<?php

namespace Tests\Admin\Collection;

use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class CollectionRecordTest extends TestBase
{
    /**
     * 添加
     */
    public function testCreate($params = [])
    {
        if (!$params) {
            $params = [
                'collection_id' => 13,
                'contact_id' => 19,
                'fullname' => 'abc1235',
                'relation' => '朋友',
                'dial' => '正常联系',
                'progress' => '本人无意还款',
                //'remark' => '不还',
                'promise_paid_time' => \Common\Utils\Data\DateHelper::date(),
            ];
        }
        $this->json('POST', '/api/collection_record/create', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}