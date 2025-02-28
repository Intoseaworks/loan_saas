<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\OperateData;


use Common\Utils\Data\StatisticsHelper;
use Common\Utils\Export\AbstractExport;

class PostLoanExport extends AbstractExport
{
    /**
     * 每日贷后分析
     */
    const SCENE_LIST = 'SCENE_LIST';

    /**
     * @param $scene
     * @return array
     */
    public function getColumns($scene = null)
    {

        $columns = [
            static::SCENE_LIST => [
                'date' => '放款日期',
                'paid_count' => '放款笔数',
                'paid_amount' => '放款金额',
                'wait_repay_count' => '未还款笔数',
                'wait_repay_amount' => '未还款金额',
                'repay_count' => '还款笔数',
                'repay_amount' => '还款金额',
                'overdue_repay_count' => '逾期还款笔数',
                'overdue_repay_rate' => '逾期还款占比',
                'overdue_1days_count' => '1天+逾期单',
                'overdue_1days_rate' => '1天+逾期率',
                'overdue_3days_count' => '3天+逾期单',
                'overdue_3days_rate' => '3天+逾期率',
                'overdue_7days_count' => '7天+逾期单',
                'overdue_7days_rate' => '7天+逾期率',
                'bad_count' => '坏账数',
                'bad_rate' => '坏账率',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $item
     * @return mixed|void
     */
    protected function beforePutCsv($item)
    {
        if ($this->sence == static::SCENE_LIST) {
            /** 1天+逾期率 */
            $item->overdue_1days_rate = StatisticsHelper::percent($item->overdue_1days_count, $item->paid_count);
            /** 3天+逾期率 */
            $item->overdue_3days_rate = StatisticsHelper::percent($item->overdue_3days_count, $item->paid_count);
            /** 7天+逾期率 */
            $item->overdue_7days_rate = StatisticsHelper::percent($item->overdue_7days_count, $item->paid_count);
            /** 坏账率 */
            $item->bad_rate = StatisticsHelper::percent($item->bad_count, $item->paid_count);
            /** 逾期还款占比  */
            $item->overdue_repay_rate = StatisticsHelper::percent($item->overdue_repay_count, $item->paid_count);
        }
    }
}
