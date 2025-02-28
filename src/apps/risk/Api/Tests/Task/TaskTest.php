<?php

namespace Risk\Api\Tests\Task;

use Risk\Api\Tests\TestBase;
use Risk\Common\Models\Task\Task;
use Risk\Common\Services\SystemApprove\SystemApproveServer;

class TaskTest extends TestBase
{
    /**
     * 创建任务
     * @throws \Exception
     */
    public function testStartTask()
    {
        $noticeUrl = str_finish(config('config.api_client_domain'), '/') . 'app/callback/risk/task_notice';
        $params = [
            'user_id' => '726',
            'order_id' => '1072',
            'notice_url' => $noticeUrl,
        ];

        $this->post('/api/risk/task/start_task', $this->sign($params))
            ->getData();
    }

    /**
     * 执行机审
     * @throws \Exception
     */
    public function testExecTask()
    {
        $params = [
            'task_no' => 'TASK_383D03E9BCBA8882',
        ];

        $this->post('/api/risk/task/exec_task', $this->sign($params))
            ->getData();
    }

    public function testApprove()
    {
        $task = Task::query()->where('id', 156)->first();

        $res = SystemApproveServer::server()->approve($task);
        dd($res);
    }
}
/*

UPDATE `common_risk_db`.`user_black` SET app_id = 4 WHERE `merchant_id` = '1';

UPDATE `common_risk_db`.`user_black` SET app_id = 5 WHERE `merchant_id` = '2';

UPDATE `common_risk_db`.`user_black` SET app_id = 6 WHERE `merchant_id` = '3';

UPDATE `common_risk_db`.`user_black` SET app_id = 7 WHERE `merchant_id` = '4';

UPDATE `common_risk_db`.`user_black` SET app_id = 8 WHERE `merchant_id` = '5';

UPDATE `common_risk_db`.`user_black` SET app_id = 9 WHERE `merchant_id` = '6';


SELECT merchant_id, count(*) FROM `data_user` GROUP BY merchant_id;




SELECT
	merchant_id,
	count(*)
FROM
	`data_user_application`
	LEFT JOIN data_user ON data_user_application.user_id = data_user.id
GROUP BY
	merchant_id;



UPDATE `data_order_detail`
SET `data_order_detail`.`app_id` = ( SELECT app_id FROM data_order WHERE data_order_detail.order_id = data_order.id )
WHERE
	`data_order_detail`.`order_id` IN (444, 463)



 */
