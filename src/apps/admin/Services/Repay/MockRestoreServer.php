<?php
/**
 * 模拟还原
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-15
 * Time: 下午4:00
 */

namespace Admin\Services\Repay;

use Admin\Models\Order\RepaymentPlan;
use Common\Models\Repay\RepayDetail;
use Common\Services\BaseService;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;

class MockRestoreServer extends BaseService
{
    /**
     * 还款数据
     */
    public $repay;

    /**
     * 还款计划
     */
    public $repaymentPlan;

    /**
     * 模拟类型
     */
    public $mockType;

    /**
     * 定义模拟类型
     */
    //新增
    const MOCK_TYPE_IS_NEW_ADD = 1;
    //移除
    const MOCK_TYPE_IS_REMOVE = 2;

    /**
     * MockRestoreServer constructor.
     * @param RepaymentPlan $repaymentPlan 还款计划
     * @param array $repay 还款明细
     * @param int $mockType 模拟类型 新增|移除
     */
    public function __construct($repaymentPlan, $repay = [], $mockType = 1)
    {
        $this->repaymentPlan = $repaymentPlan;
        $this->repay         = $repay;
        $this->mockType      = $mockType;

    }

    public function test()
    {
        $this->repaymentPlan = RepaymentPlan::model()->getOne(6);
        $this->repay         = RepayDetail::model()->whereIn('id', [27, 32])->get()->toArray();
        $this->mockType      = self::MOCK_TYPE_IS_NEW_ADD;

        $this->mock();
    }

    /**
     * 模拟
     */
    public function mock()
    {
        $repayData = $this->_getRealRepayData();

        $sourceData = [];
        foreach ($repayData as $repayDetail) {
            $sourceData[] = $repayDetail;
            $beforeRepaymentPlan = (object)json_decode($repayDetail['origin_data'], true)['repayment_plan'];
            // $mock =

        }

    }

    /**
     * 获取原始数据
     */
    private function _getRealRepayData()
    {
        $repay = RepayDetail::model()->where([
            ['status', '=', RepayDetail::STATUS_IS_VALID],
            ['repayment_plan_id', '=', $this->repaymentPlan->id]
        ])->orderBy('actual_paid_time')->get()->toArray();

        $repayData = array_combine(array_column($repay, 'id'), $repay);

        //需要调整的数据
        $needAdjustmentRepay = array_combine(array_column($this->repay, 'id'), $this->repay);

        foreach ($needAdjustmentRepay as $repayDetailId => $repayDetail) {
            if ($this->mockType == self::MOCK_TYPE_IS_NEW_ADD) {
                $repayData = array_add($repayData, $repayDetailId, $repayDetail);
            } else {
                unset($repayData[$repayDetailId]);
            }
        }

        return $repayData;

    }

}