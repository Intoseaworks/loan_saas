<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/12
 * Time: 16:23
 */

namespace Common\Services\User;

use Common\Models\UserData\UserBehavior;
use Common\Services\BaseService;

class UserBehaviorServer extends BaseService
{
    /**
     * 统计控件操作时间Mapping
     */
    const DURATION_MAPPING = [
        UserBehavior::P02_I_Name => 'focus_name_duration',
        UserBehavior::P02_I_Mail => 'focus_email_duration',
        UserBehavior::P05_I_IDNumber => 'focus_idno_duration',
        UserBehavior::P07_S_LoanAmount => 'focus_amount_duration',
        UserBehavior::P07_S_LoanTerm => 'focus_tenor_duration',
        UserBehavior::P06_I_CardNumber => 'focus_bankcct_duration',
        UserBehavior::P03_I_Street => 'focus_address_duration',
    ];

    /**
     * 流程页面耗时Mapping
     */
    const GROSS_TIME_MAPPING = [
        'gross_baseinfo_time',
        'gross_jobinfo_time',
        'gross_contact_time',
        'gross_idauth_time',
        'gross_receive_time',
        'gross_sign_time',
    ];

    /**
     * A basic test example.
     *
     * @param $orderId
     * @return array
     */
    public function getBehaviorStatisticsData($orderId)
    {
        $cntData = $this->getControlCntByOrder($orderId);
        $timeData = $this->getControlCostTimeByOrder($orderId);
        $grossData = $this->getControlProcessTimeByOrder($orderId);
        array_merge($cntData, $timeData, $grossData);
        return array_merge($cntData, $timeData, $grossData);
    }

    /**
     * 统计申请中控件埋点触发次数
     * @param $orderId
     * @return mixed
     */
    private function getControlCntByOrder($orderId)
    {
        $cntData = UserBehavior::whereOrderId($orderId)->groupBy('control_no')->selectRaw('count(*) as total, control_no')->pluck('total', 'control_no');
        $data['focus_name_freq'] = array_get($cntData, UserBehavior::P02_I_Name, 0);
        $data['focus_email_freq'] = array_get($cntData, UserBehavior::P02_I_Mail, 0);
        $data['focus_idno_freq'] = array_get($cntData, UserBehavior::P05_I_IDNumber, 0);
        $data['focus_amount_freq'] = array_get($cntData, UserBehavior::P07_S_LoanAmount, 0);
        $data['focus_tenor_freq'] = array_get($cntData, UserBehavior::P07_S_LoanTerm, 0);
        $data['focus_bankcct_freq'] = array_get($cntData, UserBehavior::P06_I_CardNumber, 0);
        $data['focus_address_freq'] = array_get($cntData, UserBehavior::P03_I_Street, 0);
        if($data['focus_name_freq'] == 0){
            $data['focus_name_freq'] = array_get($cntData, UserBehavior::P02_I_FirstName, 0);
        }
        return $data;
    }

    /**
     * 统计申请中控件操作时间(累计)
     * @param $orderId
     * @return array
     */
    private function getControlCostTimeByOrder($orderId)
    {
        $keys = array_values(self::DURATION_MAPPING);
        $data = array_fill_keys($keys, 0);
        $cntData = UserBehavior::whereOrderId($orderId)->get();
        foreach ($cntData as $item) {
            if (!$item->start_time || !$item->end_time || $item->start_time > $item->end_time) continue;
            /** 累计每个控件操作时间 */
            if (array_key_exists($item->control_no, self::DURATION_MAPPING)) {
                $data[self::DURATION_MAPPING[$item->control_no]] += $item->end_time - $item->start_time;
            }
        }
        return $data;
    }

    private function getControlProcessTimeByOrder($orderId)
    {
        $data = array_fill_keys(self::GROSS_TIME_MAPPING, 0);
        $controlNoArr = [
            UserBehavior::P02_Enter, UserBehavior::P02_Leave, //完成个人信息页总计耗时
            UserBehavior::P03_Enter, UserBehavior::P03_Leave, //完成工作信息页总计耗时
            UserBehavior::P04_Enter, UserBehavior::P04_Leave, //完成联系信息页总计耗时
            UserBehavior::P05_Enter, UserBehavior::P05_Leave, //完成证照页总计耗时
            UserBehavior::P06_Enter, UserBehavior::P06_Leave, //完成收款也总计耗时
            UserBehavior::P07_Enter, UserBehavior::P07_Leave, //完成签约页页总计耗时
        ];
        $cntData = UserBehavior::whereOrderId($orderId)->whereIn('control_no', $controlNoArr)->pluck('start_time', 'control_no');
        $data['gross_baseinfo_time'] = array_get($cntData, UserBehavior::P02_Leave, 0) - array_get($cntData, UserBehavior::P02_Enter, 0);
        $data['gross_jobinfo_time'] = array_get($cntData, UserBehavior::P03_Leave, 0) - array_get($cntData, UserBehavior::P03_Enter, 0);
        $data['gross_contact_time'] = array_get($cntData, UserBehavior::P04_Leave, 0) - array_get($cntData, UserBehavior::P04_Enter, 0);
        $data['gross_idauth_time'] = array_get($cntData, UserBehavior::P05_Leave, 0) - array_get($cntData, UserBehavior::P05_Enter, 0);
        $data['gross_receive_time'] = array_get($cntData, UserBehavior::P06_Leave, 0) - array_get($cntData, UserBehavior::P06_Enter, 0);
        $data['gross_sign_time'] = array_get($cntData, UserBehavior::P07_Leave, 0) - array_get($cntData, UserBehavior::P07_Enter, 0);
        return $data;
    }
}
