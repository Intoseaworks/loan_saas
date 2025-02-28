<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/14
 * Time: 15:54
 */

namespace Api\Services\Data;

use Api\Models\User\User;
use Api\Services\BaseService;
use Api\Services\User\UserAuthServer;
use Carbon\Carbon;
use Common\Models\User\UserAuth;
use Common\Models\UserData\UserApplication;
use Common\Models\UserData\UserContactsTelephone;
use Common\Utils\Baidu\BaiduApi;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Email\EmailHelper;
use Illuminate\Http\Request;

class DataServer extends BaseService {

    /** risk方法：用户通讯录信息 */
    const METHOD_USER_CONTACTS_TELEPHONE = 'setUserContactsTelephone';

    /** risk方法：用户位置信息 */
    const METHOD_USER_POSITION = 'setUserPosition';

    /** risk方法：用户短信记录 */
    const METHOD_USER_SMS = 'setUserSms';

    /** risk方法：用户APP列表 */
    const METHOD_USER_APP_LIST = 'setUserApplication';

    /** risk方法：用户硬件信息 */
    const METHOD_USER_PHONE_HARDWARE = 'setPhoneHardware';

    /** risk方法 */
    const METHOD = [
        self::METHOD_USER_CONTACTS_TELEPHONE => '通讯录数据',
        self::METHOD_USER_POSITION => '位置信息',
        self::METHOD_USER_SMS => '短信记录',
        self::METHOD_USER_APP_LIST => '应用数据',
        self::METHOD_USER_PHONE_HARDWARE => '硬件信息',
    ];

    /**
     * 构造联系人有效数据
     * @param $userId
     * @param $contacts
     * @param $applyId
     * @return array
     */
    public function getValidContactData($userId, $contacts, $applyId)
    {
        $contactTelephone = [];
        foreach ($contacts as $contact) {
            if (!isset($contact['contactTelephone'])) {
                continue;
            }
//            $telephone = StringHelper::formatTelephone($contact['contactTelephone']); //格式化手机号码
            $telephone = $contact['contactTelephone'];
            //手机号码格式错误 跳过 TODO 暂时停止格式校验
//            if (!Validation::validateMobile('', $telephone)) {
//                continue;
//            }
            $fullname = isset($contact['contactFullname']) ? addslashes($contact['contactFullname']) : '--';

            $userContactTelephone['user_id'] = $userId;
            $userContactTelephone['order_id'] = $applyId;
            $userContactTelephone['contact_fullname'] = $fullname;
            $userContactTelephone['contact_telephone'] = $telephone;
            $userContactTelephone['times_contacted'] = array_get($contact, 'timesContacted', 0);
            $userContactTelephone['last_time_contacted'] = array_get($contact, 'lastTimeContacted', 0) >0 ? date("Y-m-d H:i:s", array_get($contact, 'lastTimeContacted')/1000):0;
            $userContactTelephone['has_phone_number'] = array_get($contact, 'hasPhoneNumber');
            $userContactTelephone['starred'] = array_get($contact, 'starred');
            $userContactTelephone['contact_last_updated_timestamp'] = array_get($contact, 'contactLastUpdatedTimestamp')>0 ? date("Y-m-d H:i:s", array_get($contact, 'contactLastUpdatedTimestamp')/1000): 0;
            $userContactTelephone['contact_created_at'] = array_get($contact, 'contactCreatedAt')>0 ? date("Y-m-d H:i:s", array_get($contact, 'contactCreatedAt')/1000):0;
            $userContactTelephone['created_time'] = $this->getTimeMs();

            $userContactTelephone['relation_level'] = 0; //TODO 暂无关系判定
            $contactTelephone[$telephone] = $userContactTelephone;
        }
        return $contactTelephone;
    }

    /**
     * 构造位置信息有效数据
     * @param $userId
     * @param $position
     * @param $applyId
     * @return array
     */
    public function getValidPositionData($userId, $position, $applyId)
    {
        $longitude = $position['longitude'] ?? '0';
        $latitude = $position['latitude'] ?? '0';
        if (!array_get($position, 'address')) {
            $position = array_merge($position, BaiduApi::getAddress($latitude, $longitude));
        }
        $positionData = [];
        $positionData['user_id'] = $userId;
        $positionData['order_id'] = $applyId;
        $positionData['longitude'] = $longitude;
        $positionData['latitude'] = $latitude;
        $positionData['province'] = $position['province'] ?? '';
        $positionData['city'] = $position['city'] ?? '';
        $positionData['district'] = $position['district'] ?? '';
        $positionData['street'] = $position['street'] ?? '';
        $positionData['address'] = $position['address'] ?? '';
        $positionData['type'] = $position['type'] ?? '';

        return $positionData;
    }

    /**
     * 构造短信记录有效数据
     * @param $userId
     * @param $data
     * @return array
     */
    public function getValidSmsData($userId, $data, $orderId=0) {
        /**
         * n -> number
         * b -> smsContent
         * d -> date
         */
        $dataArr = [];
        foreach ($data as $dataInfo) {
            $telephone = StringHelper::formatTelephone($dataInfo['n']);
            $smsData = [];
            $smsData['user_id'] = intval($userId);
            $smsData['type'] = 1;
            $smsData['order_id'] = $orderId;
            $smsData['sms_telephone'] = $telephone;
            $smsData['sms_centent'] = isset($dataInfo['b']) ? addslashes($dataInfo['b']) : '';
            $smsData['sms_date'] = $dataInfo['d'];
            $dataArr[] = $smsData;
        }
        return $dataArr;
    }

    /**
     * 构造app列表有效数据
     * @param $userId
     * @param $data
     * @param $applyId
     * @return array
     */
    public function getValidAppData($userId, $data, $applyId)
    {
        $dataArr = [];
        foreach ($data as $applyInfo) {
            $appData = [];
            $pkgName = array_get($applyInfo, 'pkgName', '');
            $appName = array_get($applyInfo, 'appName', $pkgName);
            if (empty($appName)) continue;
            $appData['user_id'] = $userId;
            $appData['order_id'] = $applyId;
            $appData['app_name'] = $appName;
            $appData['pkg_name'] = array_get($applyInfo, 'pkgName', '');
            $appData['version_name'] = array_get($applyInfo, 'versionName', '');
            $appData['is_system'] = intval(array_get($applyInfo, 'isSystem', 0));
            $appData['is_app_active'] = intval(array_get($applyInfo, 'isAppActive', 0));
            $appData['installed_time'] = date("Y-m-d H:i:s", floatval(array_get($applyInfo, 'firstInstallTime', 0))/1000);
            $appData['updated_time'] = date("Y-m-d H:i:s", floatval(array_get($applyInfo, 'lastUpdateTime', 0))/1000);
            $appData['created_time'] = $this->getTimeMs();
            $dataArr[] = $appData;
        }
        return $dataArr;
    }

    /**
     * 构造硬件信息有效数据
     * @param $userId
     * @param $data
     * @param $applyId
     * @return array
     */
    public function getValidPhoneHardwareData($userId, $dataInfo, $applyId)
    {
        $extArr = array('uuid', 'addressMac', 'addressIP', 'androidId');
        $data = array();
        $data['user_id'] = intval($userId);
        $data['order_id'] = intval($applyId);
        $data['native_phone'] = array_get($dataInfo, 'nativePhone');
        $data['os'] = array_get($dataInfo, 'os');
        $data['os_version'] = array_get($dataInfo, 'osVersion');
        $data['total_rom'] = $dataInfo['totalRom'] ?? '';
        $data['used_rom'] = $dataInfo['usedRom'] ?? '';
        $data['free_rom'] = $dataInfo['freeRom'] ?? '';
        $data['total_ram'] = $dataInfo['totalRam'] ?? '';
        $data['used_ram'] = $dataInfo['usedRam'] ?? '';
        $data['free_ram'] = $dataInfo['freeRam'] ?? '';
        $data['cookie_id'] = $dataInfo['cookieId'] ?? '';
        $data['imei'] = array_get($dataInfo, 'imei');
        $data['phone_brand'] = array_get($dataInfo, 'phoneBrand');
        $data['device_name'] = array_get($dataInfo, 'deviceName');
        $data['model'] = array_get($dataInfo, 'model');
        $data['product'] = array_get($dataInfo, 'product');
        $data['advertising_id'] = array_get($dataInfo, 'advertisingId');
        $data['intranet_ip'] = array_get($dataInfo, 'intranetIp');
        $data['is_agent'] = array_get($dataInfo, 'isAgent'); //'是否代理登录 0 否 1是',
        $data['is_root'] = array_get($dataInfo, 'isRoot');
        $data['is_simulator'] = array_get($dataInfo, 'isSimulator');
        $data['system_language'] = array_get($dataInfo, 'systemLanguage');
        $data['time_zone'] = array_get($dataInfo, 'timeZone'); //'时区',
        $data['system_time'] = array_get($dataInfo, 'systemTime'); //'系统时间',
        $data['screen_brightness'] = array_get($dataInfo, 'screenBrightness'); //'屏幕亮度',
        $data['nfc_function'] = array_get($dataInfo, 'nfcFunction'); //'是否有NFC功能',
        $data['number_of_photos'] = array_get($dataInfo, 'numberOfPhotos'); //'照片数量',
        $data['number_of_messages'] = array_get($dataInfo, 'numberOfMessages'); //'短信数量',
        $data['number_of_call_records'] = array_get($dataInfo, 'numberOfCallRecords'); //'通话记录数量',
        $data['number_of_videos'] = array_get($dataInfo, 'numberOfVideos'); //'视频数量',
        $data['number_of_applications'] = array_get($dataInfo, 'numberOfApplications'); //'应用程序数量',
        $data['number_of_songs'] = array_get($dataInfo, 'numberOfSongs'); //'歌曲数量',
        $data['system_build_time'] = array_get($dataInfo, 'systemBuildTime'); //'系统构建时间',
        $data['iccid'] = array_get($dataInfo, 'iccid'); //'ICCID(IOS)',
        $data['persistent_device_id'] = array_get($dataInfo, 'persistentDeviceId'); //'ICCID(IOS)',
        $data['request_ip'] = array_get($dataInfo, 'requestIp'); //'请求IP',
        $data['resolution'] = array_get($dataInfo, 'resolution'); //'屏幕分辨率',
        $data['resolution_high'] = array_get($dataInfo, 'resolutionHigh'); //'分辨率高',
        $data['resolution_width'] = array_get($dataInfo, 'resolutionWidth'); //'分辨率宽',
        $data['boot_time'] = array_get($dataInfo, 'bootTime'); //开机时的时间戳(毫秒级)
        $data['up_time'] = array_get($dataInfo, 'upTime'); //从开机到当前的时长(包含休眠时间)
        $data['wifi_mac'] = array_get($dataInfo, 'wifiMac'); //'wifi mac地址(wifibssid)',
        $data['ssid'] = array_get($dataInfo, 'ssid'); //'wifi名(wifissid)',
        $data['wifi_ip'] = array_get($dataInfo, 'wifiIp'); //wifi连接的IP
        $data['cell_ip'] = array_get($dataInfo, 'cellIp'); //移动网络Ip
        $data['meid'] = array_get($dataInfo, 'meid'); //'MEID',
        $data['sid'] = array_get($dataInfo, 'sid'); //'序列号',
        $data['baseband_version'] = array_get($dataInfo, 'basebandVersion'); //'基带版本',
        $data['battery_status'] = array_get($dataInfo, 'batteryStatus'); //'电池状态',
        $data['battery_power'] = array_get($dataInfo, 'batteryPower'); //'电池电量',
        $data['network_operators'] = array_get($dataInfo, 'networkOperators'); //'网络运营商',
        $data['signal_strength'] = array_get($dataInfo, 'signalStrength'); //'信号强度',
        $data['mobile_network_type'] = array_get($dataInfo, 'mobileNetworkType'); //'移动网络类型',
        $data['mcc'] = array_get($dataInfo, 'mcc'); //'MCC',
        $data['mnc'] = array_get($dataInfo, 'mnc'); //'MNC',
        $data['carrier'] = array_get($dataInfo, 'carrier');
        $data['dns'] = array_get($dataInfo, 'dns');
        $data['canvas'] = array_get($dataInfo, 'canvas');
        $data['radio_type'] = array_get($dataInfo, 'radioType');
        $extData = [];
        foreach ($extArr as $value) {
            $extData[$value] = isset($dataInfo[$value]) ? $dataInfo[$value] : '';
        }
        //@phan-suppress-next-line PhanTypeMismatchArgumentInternal
        $data['ext_info'] = json_encode($extData, true);
        $data['created_at'] = Carbon::now()->toDateTimeString();
        if ($userAgent = app(Request::class)->header('User-Agent')) {
            $data['user_agent'] = $userAgent;
        }
        return $data;
    }

    /**
     * 构造陀螺仪有效数据
     * @param $userId
     * @param $data
     * @param $applyId
     * @param $step
     * @return array
     */
    public function getValidGyroscopeData($userId, $data, $applyId, $step)
    {
        $appData = [];
        $appData['user_id'] = $userId;
        $appData['order_id'] = $applyId;
        $appData['step'] = $step;
        $appData['anglex'] = array_get($data, 'anglex');
        $appData['angley'] = array_get($data, 'angley');
        $appData['anglez'] = array_get($data, 'anglez');
        return $appData;
    }

    /**
     * 用户行为有效数据
     * @param $userId
     * @param $data
     * @param $applyId
     * @return array
     */
    public function getValidBehaviorData($userId, $data, $applyId)
    {
        $dataArr = [];
        foreach ($data as $item) {
            $appData = [];
            $controlNo = array_get($item, 'controlNo');
            if (empty($controlNo)) continue;
            $appData['user_id'] = $userId;
            $appData['order_id'] = $applyId;
            $appData['control_no'] = $controlNo;
            $appData['start_time'] = array_get($item, 'startTime');
            $appData['end_time'] = array_get($item, 'endTime');
            $appData['old_value'] = array_get($item, 'oldValue');
            $appData['new_value'] = array_get($item, 'newValue');
            $dataArr[] = $appData;
        }
        return $dataArr;
    }

    /**
     * 发送报错邮件
     * @param $userId
     * @param $result
     * @param $originalData
     *
     */
    protected function sendErrorEmail($userId, $result, $originalData)
    {
        EmailHelper::send(
                json_encode([
            'userId' => $userId,
            'result_msg' => $result->getMsg(),
            'result_data' => $result->getData(),
            'contactsTelephoneData' => $originalData
                        ], 256),
                '用户通讯录信息存储风控失败'
        );
    }

    public function setIdentityStatus($userId) {
        $user = User::model()->getOne($userId);
        $user->fullname = 'SHARAT CHANDRA KONATHAM';
        $user->id_card_no = 'BUZPP9166Q';
        $user->save();
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_PAN_CARD);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACEBOOK);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ADDRESS_AADHAAR);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);
    }

    public function setFaceAndBaseInfo($userId) {
        $user = User::model()->getOne($userId);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ID_HANDHELD);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_FACES);

        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BASE_INFO);
    }

    public function clearFaceAndBaseInfo($userId) {
        $user = User::model()->getOne($userId);
        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_ID_HANDHELD);
        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_FACES);

        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_BASE_INFO);
    }

    public function setIdCardStatus($userId) {
        $user = User::model()->getOne($userId);
        $user->fullname = '谭梦斌';
        $user->id_card_no = '783452632747526934';
        $user->save();
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ID_FRONT);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_ID_BACK);
    }

    public function clearIdCardStatus($userId) {
        $user = User::model()->getOne($userId);
        $user->fullname = '';
        $user->id_card_no = '';
        $user->save();
        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_ID_FRONT);
        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_ID_BACK);
    }

    public function setBaseInfoStatus($userId) {
        $user = User::model()->getOne($userId);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BASE_INFO);
    }

    public function setTelephoneStatus($userId) {
        $user = User::model()->getOne($userId);
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_TELEPHONE);
    }

    public function clearTelephoneStatus($userId) {
        $user = User::model()->getOne($userId);
        UserAuthServer::server()->clearAuth($user->id, UserAuth::TYPE_TELEPHONE);
    }

    public function getValidPhotoData($userId, $photo, $applyId)
    {
        $longitude = array_get($photo, 'longitude', '0');
        $latitude = array_get($photo, 'latitude', '0');
        $photoCreatedTime = array_get($photo, 'datetime');

        # 针对终端多种时间格式的转化 待优化
        try {
            if (!$photoCreatedTime || $photoCreatedTime == 'null') {
                $photoCreatedTime = null;
            } elseif (is_numeric($photoCreatedTime) && ((strlen($photoCreatedTime) == 13) || (strlen($photoCreatedTime) == 10))) {
                if (strlen($photoCreatedTime) == 13) {
                    $photoCreatedTime = $photoCreatedTime / 1000;
                }
                $photoCreatedTime = date('Y-m-d H:i:s', $photoCreatedTime);
            } elseif (strtotime($photoCreatedTime) > 0) {
                $photoCreatedTime = DateHelper::formatToDateTime($photoCreatedTime, 'Y-m-d H:i:s');
            } else {
                $photoCreatedTime = null;
            }
        } catch (\Exception $e) {
            DingHelper::notice([
                'user_id' => $userId,
                'data' => $photo,
                'e' => EmailHelper::warpException($e)
            ], '相册异常时间');
            $photoCreatedTime = null;
        }

        $district = array_get($photo, 'district', '');
        if (empty($district) && (round($longitude) || round($latitude))) {
            $photo = array_merge($photo, BaiduApi::getAddress($latitude, $longitude));
        }
        $photoData = [];
        $photoData['user_id'] = $userId;
        $photoData['order_id'] = $applyId;
        $photoData['image_type'] = array_get($photo, 'image_type', '');
        $photoData['image_name'] = array_get($photo, 'image_name', '');
        $photoData['model'] = array_get($photo, 'model', '');
        $photoData['IMAGEWIDTH'] = array_get($photo, 'IMAGEWIDTH', '');
        $photoData['IMAGEHEIGHT'] = array_get($photo, 'IMAGEHEIGHT', '');
        $photoData['PIXELYDIMENSION'] = array_get($photo, 'PIXELYDIMENSION', '');
        $photoData['PIXELXDIMENSION'] = array_get($photo, 'PIXELXDIMENSION', '');
        $photoData['make'] = array_get($photo, 'make', '');
        $photoData['sofeware'] = array_get($photo, 'sofeware', '');
        $photoData['datetimeoriginal'] = array_get($photo, 'datetimeoriginal', '');
        $photoData['datetime'] = $photoCreatedTime;
        $photoData['longitude'] = $longitude;
        $photoData['latitude'] = $latitude;
        $photoData['province'] = array_get($photo, 'province', '');
        $photoData['city'] = array_get($photo, 'city', '');
        $photoData['district'] = $district;
        $photoData['street'] = array_get($photo, 'street', '');
        $photoData['address'] = array_get($photo, 'address', '');

        return $photoData;
    }

    public function isCanUploadContact($userId, $second = 600) {
        $tableName = "user_contacts_telephone_" . date("ym");
        $userContacts = new UserContactsTelephone();
        $userContacts->setTable($tableName);
        return $userContacts->where("user_id", $userId)->where("created_at", ">", date("Y-m-d H:i:s", time() - $second))->count();
    }

    public function isCanUploadAppList($userId, $second = 600) {
        $tableName = "user_application_" . date("ym");
        $userApps = new UserApplication();
        $userApps->setTable($tableName);
        return $userApps->where("user_id", $userId)->where("created_at", ">", date("Y-m-d H:i:s", time() - $second))->count();
    }

}
