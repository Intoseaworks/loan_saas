<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/5
 * Time: 14:25
 */

namespace Tests\Admin\Approve;

use Tests\Admin\TestBase;

class RiskConfigTest extends TestBase
{
    /**
     * 机审规则设置
     */
    public function testSystemApproveSave()
    {
        $params = [
            'new_user_age' => [
                '20', '55',
            ],
            'new_rejected_days' => '2',
            'new_ass_rejected_days' => '16',
            'new_user_ass_account' => '2',
            'new_user_sms_cnt' => '10',
            'new_user_none_h5_contacts_cnt' => '40',
            'old_user_age' => [
                '20', '56',
            ],
            'old_user_max_overdue_days' => '1',
            'old_rejected_days' => '2',
            'old_ass_rejected_days' => '12',
            'old_user_ass_account' => '3',
        ];
        $this->post('/api/config/system-approve-save', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }

    /**
     * 查看机审规则设置
     */
    public function testSystemApproveView()
    {
        $params = [

        ];
        $this->get('/api/config/system-approve-view', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }
}
