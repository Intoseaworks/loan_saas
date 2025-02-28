<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\CollectionStatistics;

use Common\Utils\Export\AbstractExport;

class CollectionStatisticsExport extends AbstractExport
{
    /**
     * 催收率统计
     */
    const SCENE_RATE_LIST = 'SCENE_RATE_LIST';

    /**
     * 催收订单统计
     */
    const SCENE_ORDER_LIST = 'SCENE_ORDER_LIST';

    /**
     * 催收员每日统计
     */
    const SCENE_WORKER_LIST = 'SCENE_WORKER_LIST';

    /**
     * 催收效率
     */
    const SCENE_EFFICIENCY_LIST = 'SCENE_EFFICIENCY_LIST';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_RATE_LIST => [
                'date' => '日期',
                'history_overdue_count' => '逾期总订单数',
                'present_overdue_count' => '当前逾期订单数',
                'today_overdue_finish' => '当日回款成功数',
                'present_overdue_finish_count' => '截止当前回款成功数',
                'today_collection_bad_count' => '坏账数',
                'today_collection_rate_text' => '当日回款率（%）',
                'history_collection_rate_text' => '截止当前回款率（%）',
                'collection_count' => '催收记录次数',
            ],
            static::SCENE_ORDER_LIST => [
                'date' => '日期',
                'overdue_count' => '累计订单数',
                'overdue_finish_count' => '累计成功数',
                'collection_bad_count' => '累计坏账数',
                'collection_success_count_rate_text' => '累计催回率（%）',
                'today_overdue' => '今日新增订单数',
                'today_promise_paid' => '今日承诺还款订单数',
                'today_overdue_finish' => '今日成功订单数',
                'today_collection_bad' => '今日坏账订单数',
                'collection_success_rate_text' => '今日催回率（%）',
            ],
            static::SCENE_WORKER_LIST => [
                'date' => '自然日期',
                'level' => '催收阶段',
                'username' => '催收员',
                'need_collection_count' => '当日需处理订单总数',
                'today_assign_count' => '当日新增订单数',
                'wait_collection_count' => '当日待处理订单数',
                'collectioning_count' => '当日处理中订单数',
                'today_again_assign_count' => '当日重分配订单数',
                'today_assign_finish_count' => '当日新增成功订单数',
                'finish_count' => '当日成功订单数',
                'bad_count' => '当日坏账订单数',
                'today_assign_finish_rate' => '当日新增催还率',
                'finish_rate' => '当日催还率',
                'bad_rate' => '当日坏账率',
            ],
            static::SCENE_EFFICIENCY_LIST => [
                'assign_data' => '分案日期',
                'level' => '催收阶段',
                'username' => '催收员',
                'assign_cnt' => '当日进件案件数',
                'finish_cnt' => '催回案件数',
                'finish_rate' => '催回率',
                'collection_cnt' => '催记案件数',
                'collection_rate' => '催记覆盖率',
                'promise_repay_cnt' => '承诺还款案件数',
                'cumulative_collection_cnt' => '累计催记总数',
                'transfer_cnt' => '转移案件数',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        if ($this->sence == static::SCENE_RATE_LIST) {
            //$data->getText();
        }

        if ($this->sence == static::SCENE_WORKER_LIST) {
            //$data->getText();
        }

        if ($this->sence == static::SCENE_ORDER_LIST) {
            //$data->getText();
        }
    }
}
