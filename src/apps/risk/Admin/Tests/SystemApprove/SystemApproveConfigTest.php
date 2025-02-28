<?php

namespace Risk\Admin\Tests\SystemApprove;

use Risk\Admin\Tests\TestBase;
use Risk\Common\Models\SystemApprove\SystemApproveRule;

class SystemApproveConfigTest extends TestBase
{
    /**
     * 机审规则设置
     */
    public function testSystemApproveSave()
    {
        $params = [
            'user_type' => SystemApproveRule::USER_QUALITY_OLD,
            'rule' => SystemApproveRule::RULE_BEHAVIOR_LATELY_COLLECTION_TELEPHONE_COUNT,
            'value' => ' 10',
            'status' => SystemApproveRule::STATUS_CLOSE,
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
            'user_quality' => SystemApproveRule::USER_QUALITY_OLD,
            'classify' => SystemApproveRule::BEHAVIOR_RULE,
        ];
        $this->get('/api/config/system-approve-view', $params)
            ->seeJson(['code' => 18000])
            ->getData();
    }
}
