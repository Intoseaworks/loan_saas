<?php

namespace Tests\Api\Data;

use Common\Models\Upload\Upload;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Api\TestBase;

//use Illuminate\Http\Testing\File;


class DataTest extends TestBase
{
    //通讯录
    protected $testContactData = '[{"contactTelephone":"1069163690048646303","contactFullname":"天猫","timesContacted":"100","lastTimeContacted":"1606370771632","hasPhoneNumber":"1","starred":"1","contactLastUpdatedTimestamp":"1606370771632","contactCreatedAt":"1606370771632"},{"contactTelephone":"950800","contactFullname":"华为消费者服务热线","timesContacted":"0","lastTimeContacted":"0","hasPhoneNumber":"1","starred":"0","contactLastUpdatedTimestamp":"1605835724758","contactCreatedAt":"1605835724758"},{"contactTelephone":"987 6543211","contactFullname":"test1","timesContacted":"0","lastTimeContacted":"0","hasPhoneNumber":"1","starred":"0","contactLastUpdatedTimestamp":"1605694057575","contactCreatedAt":"1605694057575"},{"contactTelephone":"987 6543212","contactFullname":"test2","timesContacted":"0","lastTimeContacted":"0","hasPhoneNumber":"1","starred":"0","contactLastUpdatedTimestamp":"1605694071126","contactCreatedAt":"1605694071126"},{"contactTelephone":"987 6543213","contactFullname":"test3","timesContacted":"0","lastTimeContacted":"0","hasPhoneNumber":"1","starred":"0","contactLastUpdatedTimestamp":"1605694085222","contactCreatedAt":"1605694085222"}]';

    //GPS位置信息
    protected $testPositionData = '{"latitude":"22.533834","longitude":"113.945958","province":"\u5e7f\u4e1c\u7701","city":"\u6df1\u5733\u5e02","district":"\u5357\u5c71\u533a","address":"\u5e7f\u4e1c\u7701\u6df1\u5733\u5e02\u5357\u5c71\u533a\u79d1\u82d1\u5357\u8def\u9760\u8fd1\u534e\u6210\u5cf0","road":"\u79d1\u82d1\u5357\u8def","street":"\u79d1\u82d1\u5357\u8def","poiName":"\u534e\u6210\u5cf0","streetNum":"37\u53f7","aoiName":"\u5fb7\u7ef4\u68ee\u5927\u53a6","type":"login"}';

    //短信列表
    protected $testSmsData = '[{"date":"1483590555000","number":"10658365","name":"未备注联系人","smsContent":"细细的关心火苗舔舐锅底，甜甜的思念蜜枣裹满相依，黏黏稠稠的糯米缠绵不离，熬一碗醇香的腊八粥给你，祝愿你腊八节快乐。不再接收请回复0000。【中国移动 优选短信】","type":"5"},{"date":"1483513679000","number":"10690885033800","name":"未备注联系人","smsContent":"【酷传科技】您的验证码是：874061，3分钟内有效，如有问题请拨打400-0054-520咨询。","type":"5"},{"date":"1482736387000","number":"1069051517003600009","name":"未备注联系人","smsContent":"【腾讯科技】此验证码只用于登录你的微信，验证码提供给他人将导致微信被盗。581273（微信验证码）再次提醒，请勿转发。","type":"5"},{"date":"1482479463000","number":"10690270663600017","name":"未备注联系人","smsContent":"此验证码只用于登录你的微信或更换绑定，验证码提供给他人将导致微信被盗。258318（微信验证码）。再次提醒，请勿转发【腾讯科技】","type":"5"},{"date":"1482464562000","number":"1069051517003600001","name":"未备注联系人","smsContent":"【腾讯科技】你正在注册微信帐号，验证码559090。请勿转发。","type":"5"},{"date":"1481088656000","number":"10658365","name":"未备注联系人","smsContent":"一句问候，像是暖流，穿透心扉，温暖全身。一片思念，像是飞箭，飞越千山，两地相牵。一份祝福，像是鸿雁，短信传送，厚厚深情。大雪节气，祝你有问候，心暖流，有思念，心相牵，有祝福，深情厚。不再接收请回复0000。【陕西移动优选短信】","type":"5"},{"date":"1480139799000","number":"10086","name":"未备注联系人","smsContent":"尊敬的全球通客户您好，我公司已将（门店版）220元营销包活动分月返还的15元话费充入您手机账号，剩余返还金额0元;此营销活动截止2017年04月30日到期。编辑CXLSJF发送至10086，查询话费返还到账记录。选择中国移动，享受优质服务。","type":"5"},{"date":"1479736564000","number":"10086","name":"未备注联系人","smsContent":"您本月累计话费12.76元，可用余额0元，专用余额102.22元，待返还余额75.00元，\n您本月已使用数据流量101.15M，剩余100.00M。\n查询流量套餐详细使用情况，请回复3056。","type":"5"},{"date":"1479735974000","number":"10086","name":"未备注联系人","smsContent":"【余量提醒】截至11月21日21时43分,您本月移动数据流量已使用101.14MB。其中套餐内包含国内2G/3G/4G网络的流量100.00MB,剩余0.00MB;\r\n为节省您的流量费用，推荐您使用流量套餐，回复3054了解资费，本月订购立即生效。\r\n您本月专有网络或特殊时段流量套餐使用情况如下:\r\n1.4G飞享18元套餐(新版)升级优惠包：包含流量100.00MB,剩余流量100.00MB；\r\n具体以月结账单为准，回复QXYLTX可取消接收该提醒短信，发送CXLL至10086或拨打1008611随时查询流量使用。中国移动","type":"5"},{"date":"1479708050000","number":"10658365","name":"未备注联系人","smsContent":"^o^点播云的歌曲给天空，希望你听到晴朗，点播海的旋律给宽广，希望你听到敞亮，点播花的调调给美丽，希望你听到幸福，世界问候日，希望你听到我的祝福。【陕西移动优选短信】","type":"5"},{"date":"1479482477000","number":"10086","name":"未备注联系人","smsContent":"【余量提醒】截至11月18日23时22分,您本月移动数据流量已使用95.34MB。其中套餐内包含国内2G/3G/4G网络的流量100.00MB,剩余4.66MB;\r\n为节省您的流量费用，推荐您使用流量套餐，回复3054了解资费，本月订购立即生效。\r\n您本月专有网络或特殊时段流量套餐使用情况如下:\r\n1.4G飞享18元套餐(新版)升级优惠包：包含流量100.00MB,剩余流量100.00MB；\r\n具体以月结账单为准，回复QXYLTX可取消接收该提醒短信，发送CXLL至10086或拨打1008611随时查询流量使用。中国移动","type":"5"},{"date":"1479115902163","number":"4007883333","name":"客服助手","smsContent":"您好，我是客服助手边小溪。我能帮您解答手机使用中的疑问，还可以陪你聊天解闷哦。而且，我们的聊天只会消耗移动数据，并不会收取正常信息的费用。","type":"5"}]';

    //应用列表
    protected $testAppData = '[{"appName":"旅行助手","lastUpdateTime":"1533657660000","firstInstallTime":"1533657660000","pkgName":"com.huawei.scenepack","versionName":"10.0.1.318","isSystem":"1","isAppActive":"0"},{"appName":"精品推荐","lastUpdateTime":"1533657660000","firstInstallTime":"1533657660000","pkgName":"com.huawei.hifolder","versionName":"10.5.0.302","isSystem":"1","isAppActive":"0"},{"appName":"com.android.cts.priv.ctsshim","lastUpdateTime":"1533657660000","firstInstallTime":"1533657660000","pkgName":"com.android.cts.priv.ctsshim","versionName":"9-5374186","isSystem":"1","isAppActive":"0"},{"appName":"相机","lastUpdateTime":"1533657660000","firstInstallTime":"1533657660000","pkgName":"com.huawei.camera","versionName":"10.1.1.355","isSystem":"1","isAppActive":"0"}]';

    //硬件信息
    protected $testHardwareData = '{"nativePhone":"+8615517747859","os":"android","osVersion":"10","totalRom":"7.97 GB","usedRom":"4.04 GB","freeRom":"99.80 GB","totalRam":"119 GB","usedRam":"4.04 GB","freeRam":"99.80 GB","cookieId":"","imei":"a2d56b5dde9a8959","phoneBrand":"HUAWEI","deviceName":"HUAWEI","model":"VOG-AL00","product":"VOG-AL00","advertisingId":"","intranetIP":"192.168.31.99","isAgent":"1","isRoot":"0","isSimulator":"0","systemLanguage":"zh","timeZone":"GMT+08:00","systemTime":"1606328873316","screenBrightness":"54","nfcFunction":"Y","numberOfPhotos":0,"numberOfMessages":0,"numberOfCallRecords":0,"numberOfVideos":0,"numberOfApplications":"273","numberOfSongs":0,"systemBuildTime":"1606328873316","iccid":"","persistentDeviceId":"","requestIp":"216.164.29.10","resolution":"1080_2265","resolutionHigh":"2265","resolutionWidth":"1080","bootTime":"1606328873316","upTime":1606328873316,"wifiMac":"4C:50:77:78:42:44","ssid":"Xiaomi_04FD_5G","wifiIp":"54.129.126.122","cellIp":"164.76.46.57","meid":"","sid":"2fb63dcf-1b1c-4b13-a0af-94b6e09d9335","basebandVersion":"21C20B379S000C000,21C20B379S000C000","batteryStatus":"usbCharge","batteryPower":100,"networkOperators":"China Unicom","signalStrength":"-125","mobileNetworkType":"NETWORK_WIFI","mcc":"","mnc":"","carrier":"China Unicom","dns":"130.116.58.191","canvas":"null","radioType":"CMCC","anglex":"0.0036128315,0.36812288","angley":"-0.00577704,0.44986093","anglez":"0.0025481808,9.818219","availableMemory":"119 GB","residualMemory":"99.80 GB","runMemory":"7.97 GB","memoryUsed":"4.04 GB","howLongBootTime":"2655","totalCapacity":"119 GB","availableCapacity":"99.80 GB","device":"HUAWEI","deviceVersion":"10","BuildDate":"2020-11-27 22:43:13","googleAdvertisingId":"","gpsInfo":"39.7704,116.58429","isCommunication":"Y"}';

    //相片exif
    protected $testPhotoExifData = '{"image_type":"id_front","image_name":"1606488545941.jpg","model":"VOG-AL00","IMAGEWIDTH":"480","IMAGEHEIGHT":"640","PIXELYDIMENSION":"72","PIXELXDIMENSION":"72","make":"HUAWEI","sofeware":"VOG-AL00 10.1.0.168(C00E168R2P8)","datetimeoriginal":"2020:11:27 22:49:06","datetime":"2020:11:27 22:49:06","latitude":"39.7704","longitude":"116.58429","province":"北京市","city":"北京市","district":"通州区","street":"马驹桥镇北京博羽教育富力尚悦居C区","address":"北京市通州区马驹桥镇北京博羽教育富力尚悦居C区"}';

    //陀螺仪
    protected $testGyroscopeData = '[{"anglex":"5.5850536E-4","angley":"-0.0012740904","anglez":"-0.003036873"},{"anglex":"5.5850536E-4","angley":"-0.0012740904","anglez":"-0.003036873"}]';

    //行为埋点
    protected $testBehaviorData = '[{"controlNo":"P05_Leave","startTime":"1606559621871"},{"controlNo":"P05_Enter","startTime":"1606559611824"},{"controlNo":"P05_S_DocumentType","oldValue":""},{"controlNo":"P05_I_IDNumber","oldValue":""},{"controlNo":"P05_I_FrontIDPhoto","oldValue":""},{"controlNo":"P05_I_Selfie","oldValue":""},{"controlNo":"P05_I_RealPersonVerify","oldValue":""},{"controlNo":"P05_C_Back"}]';

//    public function setUp($user)
//    {
//        parent::setUp();
//        $this->user = $user;
//    }

    public function testUploadFront()
    {
        $path = 'test-front.jpeg';
        $file = [
            'file' => new UploadedFile(
                $path,
                basename($path),
                'image/jpeg',
                UPLOAD_ERR_OK,
                true
            ),
        ];
        $params = ['type' => Upload::TYPE_ID_FRONT, 'token' => $this->getToken()];
        $this->call('POST', 'app/data/upload-id-card', $params, [], $file);
        $this->seeJson([
            'code' => self::ERROR_CODE
        ])->getData();
    }

//    public function testBankcardCreate()
//    {
//        $json = '{"no": "112341234123111455","bank_name": "卡斯看到44444","name": "测试","reserved_telephone": "13242930933","bank": "bbbb","bank_branch_name": "sdfaskdjfk","province_code": "345432523","city_code": "23424"}';
//        $params = json_decode($json, true);
//        $params['token'] = $this->getToken();
//
//        $this->json('POST', '/data/bankcard-create', $params)->seeJson([
//            'code' => self::SUCCESS_CODE,
//        ])->getData();
//    }


    public function testBankcardCreate()
    {
        $json = '{"card_no": "31150100004361","ifsc": "BARB0MUDHOL"}';
        $params = json_decode($json, true);
        $params['token'] = $this->getToken(163);

        $header = [
            'app-key' => 'APP5D69EAEFD74DD38',
        ];

        $this->json('POST', 'app/bankcard/create', $params, $header)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 身份信息校验
     */
    public function testIdentity()
    {
        $this->get('app/data/identity?token=' . $this->getToken())->seeJson([
            'code' => self::SUCCESS_CODE
        ]);
    }

    /**
     * 通讯录上传风控
     * @return mixed
     */
    public function testUserContact()
    {
        $params = [
            'data_json' => $this->testContactData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-contact', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 用户位置信息上传风控
     * @return mixed
     */
    public function testUserPosition()
    {
        $this->getToken();
        $params = [
            'data_json' => $this->testPositionData,
            'token' => '163',
        ];
        $this->json('POST', 'app/data/user-position', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 短信记录上传风控
     * @return mixed
     */
    public function testUserSms()
    {
        $params = [
            'data_json' => 'eNotUE1Lw0AQ/SvPHPyANm6SFklvUUF6KIVW6EE8jMmmGd3uht2tIYj/3WnrbZj3eF9vP0mTLJJc
5WqqimmhkM0Ws3KRPyST5EOQnTa1O2hEh6eOLV1h3Ue0zqNiH7W5CVjaqL2lyM6SwcbRge0ePdVf
uIa2n27E0Ro+cNQN2IrcCa/JmDBB8fKIhiKBbIN8rgQLcC1arzWME9aFCXG8HJJkaRsmEc+Uwna1
RYjk40mUIjahmJflOWGmRHoMKV4dqI78TVFPUBuWZMMwpHRukLK9Z4/bc4q6I7/XAdT3ZrxL/0ti
4NDJd3RHEDoBx5PnmMpIVkaqnqfVcrOuVsnv+x8l3W2L',
            'token' => 163,
        ];
        $this->json('POST', 'app/data/user-sms', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * APP列表上传风控
     * @return mixed
     */
    public function testUserAppList()
    {
        $params = [
            'data_json' => $this->testAppData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-app-list', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    /**
     * 硬件信息上传风控
     * @return mixed
     */
    public function testUserPhoneHardware()
    {
        $this->getToken();
        $params = [
            'data_json' => $this->testHardwareData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-phone-hardware', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testUserContactCount()
    {
        $this->getToken();
        $params = [
            'count' => '1003',
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-contact-count', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testUserPhotoExif()
    {
        $this->getToken();
        $params = [
            'data_json' => $this->testPhotoExifData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-photo-exif', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testUserBehavior()
    {
        $this->getToken();
        $params = [
            'data_json' => $this->testBehaviorData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-behavior', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }

    public function testUserGyroscope()
    {
        $this->getToken();
        $params = [
            'data_json' => $this->testGyroscopeData,
            'token' => '209',
        ];
        $this->json('POST', 'app/data/user-gyroscope', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ])->getData();
    }
}
