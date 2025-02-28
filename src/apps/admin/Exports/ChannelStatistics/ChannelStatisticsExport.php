<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\ChannelStatistics;

use Admin\Models\Channel\Channel;
use Common\Utils\Data\StatisticsHelper;
use Common\Utils\Export\AbstractExport;

class ChannelStatisticsExport extends AbstractExport
{
    /**
     * 渠道分析列表
     */
    const SCENE_CHANNEL_NAME_LIST = 'SCENE_CHANNEL_NAME_LIST';

    /**
     * 渠道分析详情
     */
    const SCENE_CHANNEL_CODE_LIST = 'SCENE_CHANNEL_CODE_LIST';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_CHANNEL_NAME_LIST => [
                'app_version' => '版本',
                'date' => '时间',
                'channel_code' => '渠道',
                'user_count' => '注册用户数',
                'order_count' => '借款申请笔数',
                'loan_rate' => '申请转化率',
                'base_info_count' => '个人信息完成数',
                'base_info_rate' => '个人信息完成率',
                'contacts_count' => '紧急联系人完成数',
                'contacts_rate' => '紧急联系人完成率',
                'faces_count' => '人脸识别完成数',
                'faces_rate' => '人脸识别完成率',
                'aadhaar_card_kyc_count' => 'AadhaarKyc认证完成数',
                'aadhaar_card_kyc_rate' => 'AadhaarKyc认证完成率',
                'aadhaar_card_count' => 'Aadhaar认证完成数',
                'aadhaar_card_rate' => 'Aadhaar认证完成率',
                'pan_card_count' => 'pancard认证完成数',
                'pan_card_rate' => 'pancard认证完成率',
                'address_voter_count' => '选民认证完成数',
                'address_voter_rate' => '选民认证完成率',
                'address_passport_count' => '护照认证完成数',
                'address_passport_rate' => '护照认证完成率',
                'user_extra_info_count' => '扩展信息完成数',
                'user_extra_info_rate' => '扩展信息完成率',
                'bankcard_count' => '银行卡绑定完成数',
                'bankcard_rate' => '银行卡绑定完成率',
                'system_pass_count' => '机审通过数',
                'system_pass_rate' => '机审通过率',
                'manual_pass_count' => '初审通过数',
                'manual_pass_rate' => '初审通过率',
                'call_pass_count' => '电审通过数',
                'call_pass_rate' => '电审通过率',
                'order_pass_count' => '申请通过笔数',
                'pass_rate' => '通过转化率',
                'order_remit_count' => '放款笔数',
                'order_remit_rate' => '放款转化率',
                'order_repay_count' => '还款笔数',
                'order_repay_rate' => '回款率',
                'overdue_count' => '逾期笔数',
                'overdue_rate' => '逾期率',
                'bad_count' => '坏账数',
                'bad_rate' => '坏账率',
            ],
            static::SCENE_CHANNEL_CODE_LIST => [
                'channel_code' => '合作标识',
                'url' => '推广链接',
                'user_count' => '注册用户数',
                'order_count' => '借款申请笔数',
                'loan_rate' => '申请转化率',
                'order_pass_count' => '申请通过笔数',
                'pass_rate' => '通过转化率',
                'order_remit_count' => '放款笔数',
                'order_repay_count' => '还款笔数',
                'order_repay_rate' => '回款率',
                'overdue_count' => '逾期笔数',
                'overdue_rate' => '逾期率',
                'bad_count' => '坏账数',
                'bad_rate' => '坏账率',
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
        if ($this->sence == static::SCENE_CHANNEL_NAME_LIST) {

        }

        if ($this->sence == static::SCENE_CHANNEL_CODE_LIST) {
            $data->setScenario(Channel::SCENARIO_STATISTICS)->getText();
        }
    }
}
