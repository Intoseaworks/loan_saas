<?php

namespace Risk\Common\Services\CreditReport;

use Common\Services\BaseService;
use Risk\Common\Helper\Services\ServicesHelper;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\CreditReport\ThirdExperian;
use Risk\Common\Models\Third\RequestPacket;
use Risk\Common\Models\Third\ThirdReport;

class CreditReportServer extends BaseService
{
    const REPORT_EXPIRE_DAYS = 45;

    const HM_STATUS_STAGE1_FAILED = 'STAGE1_FAILED';
    const HM_STATUS_STAGE2_FAILED = 'STAGE2_FAILED';
    const HM_STATUS_STAGE3_FAILED = 'STAGE3_FAILED';
    const HM_STATUS_NOT_HIT = 'NOT_HIT'; // 身份认证未通过

    public function hmCreditReport(Order $order)
    {
        $expireDays = self::REPORT_EXPIRE_DAYS;
        $reuseStartTime = date('Y-m-d H:i:s', strtotime("-{$expireDays}day"));

        if ($thirdReport = ThirdReport::getReport(
            $order->user_id,
            ThirdReport::TYPE_CREDIT_REPORT,
            ThirdReport::CHANNEL_HIGH_MARK,
            $reuseStartTime
        )) {
            return $this->outputSuccess('success', $thirdReport);
        }

        $user = $order->user;
        $userInfo = $user->userInfo;

        $gender = $userInfo->gender == UserInfo::GENDER_FEMALE ? 'F' : ($userInfo->gender == UserInfo::GENDER_MALE ? 'M' : '');
        $dob = date('Y-m-d', strtotime(str_replace('/', '-', $userInfo->birthday)));

        $email = filter_var($userInfo->email, FILTER_VALIDATE_EMAIL) !== false ? $userInfo->email : 'abc@abc.com';

        $params = [
            'name' => $user->fullname,
            'gender' => $gender, // M, F or T
            'dob' => $dob, // Y-m-d
            'marital_status' => $userInfo->marital_status,
            'phone_no' => $user->telephone,
            'email' => $email,
            'pan_card_no' => $userInfo->pan_card_no,
            'father_name' => $userInfo->father_name ?: 'NA',
            'spouse_name' => '',
            'address' => $userInfo->address ?: 'NA',
            'city' => $userInfo->city ?: 'NA',
            'state' => $userInfo->province ?: 'NA',
            'pincode' => $userInfo->pincode ?: 'NA',
            'country' => 'India',
            'user_id' => $user->id,
            'reuse_start_time' => $reuseStartTime,
        ];

        $res = (new ServicesHelper)->hmCreditReport($params);

        RequestPacket::addHighMarkRequestPacket($order->app_id, $order->id, '', json_encode($params), $res->getOriginData(), '', $res->getMsg());

        if ($res->isSuccess()) {
            $data = $res->getData();

            $attribute = [
                'app_id' => $order->app_id,
                'user_id' => $order->user_id,
                'type' => ThirdReport::TYPE_CREDIT_REPORT,
                'channel' => ThirdReport::CHANNEL_HIGH_MARK,
                'status' => ThirdReport::STATUS_NORMAL,
                'request_info' => json_encode($params),
                'report_body' => $data['report'] ?? '',
                'report_id' => $data['report_id'] ?? '',
                'unique_no' => $params['phone_no'] . '|' . $params['pan_card_no'],
            ];
            $thirdReport = ThirdReport::create($attribute);

            return $this->outputSuccess('success', $thirdReport);
        } else {
            return $this->outputError($res->getMsg(), $res->getDataField('status'));
        }
    }

    public function experianCreditReport(Order $order)
    {
        $expireDays = self::REPORT_EXPIRE_DAYS;

        $reuseStartTime = date('Y-m-d H:i:s', strtotime("-{$expireDays}day"));

        if ($thirdReport = ThirdReport::getReport(
            $order->user_id,
            ThirdReport::TYPE_CREDIT_REPORT,
            ThirdReport::CHANNEL_EXPERIAN,
            $reuseStartTime
        )) {
            return $this->outputSuccess('success', $thirdReport);
        }

        $user = $order->user;
        $userInfo = $user->userInfo;

        $gender = $userInfo->gender == UserInfo::GENDER_FEMALE ? 'F' : ($userInfo->gender == UserInfo::GENDER_MALE ? 'M' : '');
        $dob = date('Y-m-d', strtotime(str_replace('/', '-', $userInfo->birthday)));

        $params = [
            'user_id' => $user->id,
            'phone_no' => $user->telephone,
            'name' => $user->fullname,
            'pan_card_no' => $userInfo->pan_card_no,
            'gender' => $gender,
            'dob' => $dob,
            'city' => $userInfo->city ?? '',
            'state' => $userInfo->province ?? '',
            'pincode' => $userInfo->pincode ?? '',
            'address' => $userInfo->address ?? '',
            'reuse_start_time' => $reuseStartTime,
        ];

        $res = (new ServicesHelper())->experianCreditReport($params);

        RequestPacket::addExperianRequestPacket($order->app_id, $order->id, '', json_encode($params), $res->getOriginData(), '', $res->getMsg());

        if ($res->isSuccess()) {
            $data = $res->getData();

            $attribute = [
                'app_id' => $order->app_id,
                'user_id' => $order->user_id,
                'type' => ThirdReport::TYPE_CREDIT_REPORT,
                'channel' => ThirdReport::CHANNEL_EXPERIAN,
                'status' => ThirdReport::STATUS_NORMAL,
                'request_info' => json_encode($params),
                'report_body' => $data['report'] ?? '',
                'report_id' => $data['report_id'] ?? '',
                'unique_no' => $params['phone_no'] . '|' . $params['pan_card_no'],
            ];
            $thirdReport = ThirdReport::create($attribute);

            return $this->outputSuccess('success', $thirdReport);
        } else {
            return $this->outputError($res->getMsg());
        }
    }

    /*
     * $params = [
            'user_id' => 0,
            'phone_no' => '9944933108',
            'name' => 'Velmurugan Dhanapal',
            'pan_card_no' => 'BBHPV1174R',
            'gender' => 'M',
            'dob' => '1987-06-12',
//            'city' => '',
//            'state' => '',
//            'pincode' => '',
//            'address' => '',
        ];

        CreditReportServer::server()->experianCreditReportBatch($params);
     */
    public function experianCreditReportBatch($batchNo, $params)
    {
        $expireDays = self::REPORT_EXPIRE_DAYS;

        $res = (new ServicesHelper())->experianCreditReport($params);

        $data = $res->getData();

        $attribute = [
            'batch_no' => $batchNo,
            'type' => ThirdExperian::TYPE_EXPERIAN,
            'telephone' => $params['phone_no'],
            'request_info' => json_encode($params),
            'report_body' => $data['report'] ?? null,
            'response_info' => $res->getOriginData(),
            'status' => $data['third_status'] ?? 1,
            'extra_info' => $data['report_id'] ?? null,
            'remark' => $res->getMsg(),
        ];
        $thirdReport = ThirdExperian::create($attribute);

        $packetAttribute = [
            'relate_id' => $thirdReport->id,
            'relate_type' => RequestPacket::RELATE_TYPE_REQUEST_EXPERIAN_CREDIT_REPORT_BATCH,
            'url' => '',
            'request_info' => json_encode($params),
            'response_info' => $res->getOriginData(),
            'http_code' => '',
            'remark' => $res->getMsg(),
        ];
        RequestPacket::create($packetAttribute);

        return $this->outputSuccess('success', $thirdReport);
    }
}
