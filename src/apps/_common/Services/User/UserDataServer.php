<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/12
 * Time: 16:23
 */

namespace Common\Services\User;

use Common\Models\UserData\UserApplication;
use Common\Models\UserData\UserContactsTelephone;
use Common\Models\UserData\UserPosition;
use Common\Services\BaseService;
use Common\Utils\Data\DateHelper;
use Illuminate\Support\Facades\DB;
use Common\Models\User\User;

class UserDataServer extends BaseService
{
    const DATA_APPLICATION_VIEW = 'user_application_view';
    const DATA_CONTACTS_VIEW = 'user_contacts_view';
    const DATA_POSITION_VIEW = 'user_position_view';

    public function userInfo($userId, $method, $params)
    {
        // 通讯录、LBS Details（位置）、APP列表、运营商
        // userOperatorReport、userPosition、contacts、userApp
        $map = [
            'user_position_view' => [$this, 'userPositionView'],
            'user_application_view' => [$this, 'userApplicationView'],
//            'user_application_count' => [$this, 'userApplicationCount'],
//            'user_application_name' => [$this, 'userApplicationName'],
//            'user_sms_view' => [$this, 'userSmsView'],
//            'user_sms_keyword' => [$this, 'userSmsKeyword'],
//            'user_contacts_telephone_count' => [$this, 'userContactsTelephoneCount'],
//            'user_contacts_frequent' => [$this, 'userContactsFrequent'],
            'user_contacts_view' => [$this, 'userContactsView'],
//            'user_phone_hardware_view' => [$this, 'userPhoneHardwareView'],
//            'user_mno_data' => [$this, 'userMnoReport'],
//            'duo_tou_data' => [$this, 'duoTouData'],
//            'manual_risk' => [$this, 'manualRisk'],
//            'sys_check_detail' => [$this, 'sysCheckDetail'],
//            'zfb_zm_black' => [$this, 'zfbZmBlack'],
//            'user_xy_carrier_data' => [$this, 'xinyanUserCarrierReport'],
        ];

        if (empty($userId)) {
            return $this->output(self::OUTPUT_ERROR, 'user_id不能为空');
        }
        if (!in_array($method, array_keys($map))) {
            return $this->output(self::OUTPUT_ERROR, 'method参数不正确');
        }
        $data = call_user_func($map[$method], $userId, $params);

        return $data;
    }

    // 获取用户位置列表
    public function userPositionView($userId, $params = [])
    {
        return UserPosition::where(['user_id' => $userId])
            ->orderBy('created_at', 'desc')->get();
    }

    // 获取用户应用信息
    public function userApplicationView($userId, $params = [])
    {

        $model = new UserApplication();
        $order = User::model()->getOne($userId)->order;
        $table = date("_ym");
        if ($order) {
            $table = date("_ym", strtotime($order->created_at));
        }
        $model->setTable($model->getTable() . $table);
        $datas = $model->where(['user_id' => $userId])
            ->select("app_name", DB::raw("max(installed_time) as installed_time"),
                    DB::raw("max(installed_time) as installed_time"),
                    DB::raw("max(updated_time) as updated_time"),
                    DB::raw("max(created_at) as created_at"))
            ->orderBy('created_at', 'desc')->groupBy('app_name')->get();
        foreach ($datas as &$data) {
            $data['installed_time'] = is_numeric($data['installed_time']) ? DateHelper::msToDateTime($data['installed_time']) : $data['installed_time'];
            $data['updated_time'] = is_numeric($data['updated_time']) ? DateHelper::msToDateTime($data['updated_time']) : $data['updated_time'];
        }
        return $datas;
    }

    // 获取用户通讯录列表
    public function userContactsView($userId, $params = [])
    {
        $model = new UserContactsTelephone();
        $order = User::model()->getOne($userId)->order;
        $table = date("_ym");
        if ($order) {
            $table = date("_ym", strtotime($order->created_at));
        }
        $model->setTable($model->getTable() . $table);
        $query = $model->select(['contact_fullname', 'contact_telephone', 'created_at'])
            ->where(['user_id' => $userId]);

        if (!empty($params['contact_fullname']) && is_string($params['contact_fullname'])) {
            $query->where('contact_fullname', 'like', '%' . $params['contact_fullname'] . '%');
        }
        if (!empty($params['contact_telephone']) && is_string($params['contact_telephone'])) {
            $query->where('contact_telephone', 'like', '%' . $params['contact_telephone'] . '%');
        }
        if (!empty($params['contact_telephone_array']) && is_array($params['contact_telephone_array'])) {
            $query->whereIn('contact_telephone', $params['contact_telephone_array']);
        }

        $contacts = $query->orderBy('created_at', 'desc')->get();

        $phones = [];
        $result = [];
        foreach ($contacts as $contact) {
            if (!isset($contact['contact_telephone']) || in_array($contact['contact_telephone'], $phones)) {
                continue;
            } else {
                $phones[] = $contact['contact_telephone'];
                $result[] = $contact;
            }
        }

        return $result;
    }

}
