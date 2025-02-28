<?php

namespace Tests\Admin\Collection;

use Common\Models\Collection\CollectionContact;
use Tests\Admin\TestBase;

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/10
 * Time: 21:25
 */
class CollectionContactTest extends TestBase
{
    /**
     * 添加催收联系人
     */
    public function testCreate()
    {
        $params = [

        ];
        $this->json('POST', '/api/collection_contact/create', $params)
            ->seeJson(['code' => self::ERROR_CODE]);
        $params = [
            'collection_id' => 1,
            'fullname' => '张三',
            'relation' => CollectionContact::RELATION_ONESELF,
            'contact' => '18867895678',
        ];
        $this->json('POST', '/api/collection_contact/create', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

}
