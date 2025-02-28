<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Order;

use Tests\Api\TestBase;

class InboxTest extends TestBase
{
    /**
     * 通知私信列表
     */
    public function testIndex()
    {
        $params = [
            'token' => $this->getToken(),
        ];
        $this->get('app/inbox/index', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 通知私信详情
     */
    public function testDetail()
    {
        $params = [
            'token' => $this->getToken(),
        ];
        $this->get('app/inbox/get', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params['id'] = 1;
        $this->get('app/inbox/get', $params)->seeJson([
            'code' => self::ERROR_CODE,
        ]);
        $params['type'] = 'inbox';
        $this->get('app/inbox/get', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
        $params['id'] = 1;
        $params['type'] = 'notice';
        $this->get('app/inbox/get', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

}
