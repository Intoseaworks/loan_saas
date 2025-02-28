<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/30
 * Time: 14:16
 */

namespace Tests\Admin\Notice;

use Tests\Admin\TestBase;

class NoticeTest extends TestBase
{
    /**
     * 推送列表
     */
    public function testInboxList()
    {
        $params = [
            'sort' => '-created_at',
        ];
        $this->json('GET', '/api/notice/inbox-list', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    /**
     * 公告列表
     */
    public function testNoticeList()
    {
        $this->json('GET', '/api/notice/notice-list')
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testNoticeCreate()
    {
        //测试默认不包含type:save_and_send 不发送公告
        $params = $this->decode('{"title":"zzzz","content":"zzzzzzzzz","tags":"normal","status":"1","pushed_at":"2020-01-01 12:11:58"}');
        $this->json('POST', '/api/notice/notice-create', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testNoticeEdit()
    {
        //测试默认不包含type:save_and_send 不发送公告
        $params = $this->decode('{"id":1,"title":"zzzz","content":"zzzzzzzzz","tags":"normal","status":"1","pushed_at":"2020-01-01 12:11:58"}');
        $this->json('POST', '/api/notice/notice-edit', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testNoticeDelete()
    {
        //测试默认不包含type:save_and_send 不发送公告
        $params = $this->decode('{"id":1}');
        $this->json('POST', '/api/notice/notice-delete', $params)
            ->seeJson(['code' => self::SUCCESS_CODE]);
    }

    public function testNoticeDeleteBySend()
    {
        $params = [
            'id' => 1
        ];
        $this->post('api/notice/notice-delete-by-send', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }


    /**
     * 短信列表
     */
    public function testSmsList()
    {
        $this->json('GET', '/api/notice/sms-list')
            ->seeJson(['code' => self::SUCCESS_CODE])->getData();
    }
}