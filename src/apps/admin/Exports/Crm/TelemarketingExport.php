<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Crm;

use Admin\Models\Channel\Channel;
use Admin\Models\Channel\ChannelCount;
use Admin\Models\User\User;
use Admin\Services\Channel\ChannelServer;
use Admin\Services\Order\OrderServer;
use Admin\Services\User\UserServer;
use Common\Utils\Export\AbstractExport;

class TelemarketingExport extends AbstractExport {

    /**
     * 平台列表
     */
    const SCENE_EXPORT = 'SCENE_EXPORT';

    /**
     * ChannelExport constructor.
     * @param array $params
     */
    public function __construct(array $params = []) {
        parent::__construct($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null) {
        $columns = [
            static::SCENE_EXPORT => [
                'report_date' => '日期',
                'saler_id' => '电销员id',
                'saler_name' => '电销员姓名',
                'total' => '总数',
                'agree_number' => '同意数',
                'call_reject_number' => '拒绝数',
                'miss_call_number' => '未接听数',
                'wrong_number' => '号码错误数',
                'apply_number' => '进件人数',
                'pass_number' => '审批通过数',
                'reject_number' => '审批拒绝数',
                'agree_number_rate' => '同意率',
                'call_reject_rate' => '拒绝率',
                'miss_call_rate' => '未接听率',
                'wrong_number_rate' => '号码错误率',
                'apply_number_rate' => '进件率',
                'pass_number_rate' => '通过率',
                'conv_rate' => '转化率',
            ]
        ];

        return $columns[$scene] ?? [];
    }

    public function csvArray($array, $columns, $fileName = null) {
        set_time_limit(0);
        $columns = $this->getColumns($columns);
        //header
        $fileName = date('YmdHis') . '_' . $this->formatGbk($fileName ? $fileName : 'export');
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
        header('Cache-Control: max-age=0');
        if (ob_get_contents()) {
            ob_clean();
        }
        $fp = fopen('php://output', 'a');
//        $columns = $this->formatColumns($columns);

        $content = [];
        foreach ($columns as $column) {
            $content[] = $this->formatGbk($column);
        }
        fputcsv($fp, $content);
        foreach ($array as $item) {
            $content = [];
            foreach ($columns as $key => $column) {
                $content[] = $this->formatGbk($item->$key);
            }
            fputcsv($fp, $content);
        }

        $this->obFlush();
        fclose($fp);
        die;
    }

}
