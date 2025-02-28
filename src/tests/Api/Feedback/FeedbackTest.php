<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/24
 * Time: 15:36
 */

namespace Tests\Api\Order;

use Api\Models\Feedback\Feedback;
use Api\Models\Feedback\FeedbackFaq;
use Tests\Api\TestBase;

class FeedbackTest extends TestBase
{
    /**
     * 添加反馈
     */
    public function testAdd()
    {
        $params = [
            'type' => Feedback::TYPE_PRODUCT_PROPOSAL,
            'content' => 'TEST',
        ];
        $this->post('app/feedback/add', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
        $params['token'] = $this->getToken();
        $this->post('app/feedback/add', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 常见问题列表
     */
    public function testFaqIndex()
    {
        $params = [
            'type' => FeedbackFaq::TYPE_LOAN,
        ];
        $this->get('app/feedback/faq/index', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

    /**
     * 常见问题详情
     */
    public function testFaqDetail()
    {
        $params = [
            'id' => 1,
        ];
        $this->get('app/feedback/faq/detail', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }

}
