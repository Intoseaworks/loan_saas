<?php

namespace Tests\Api\Common;

use Api\Models\BankCard\BankCard;
use Api\Models\User\UserAuth;
use Common\Utils\Data\ArrayHelper;
use JMD\Utils\SignHelper;
use Tests\Api\TestBase;

class BankCardTest extends TestBase
{
    protected $bindParamsKey = 'bind_params';
    protected $requestNoKey = 'request_no_key';

    public function testBankCardIndex()
    {
        $params = [
            'token' => 1409,
        ];

        $this->json('post', 'app/bankcard/index', $params)
            ->getData();
    }

    /**
     * 用过纯测试 不经过支付系统
     */
    public function testBindBankCardTest($userId)
    {
        $this->testBindStart($userId);

        $this->testBankCardAuthBindNotice();
    }

    public function setBankCardStatus($userId)
    {
        //用错方法
        //BankCard::clearStatus($userId, UserAuth::TYPE_BANKCARD);

        $params = [
            'user_id' => $userId,
            'no' => '6217002920131968100',
            'name' => '谭梦斌',
            'bank_name' => '可接受的JFK',
            'bank' => '是开发开始',
            'status' => UserAuth::STATUS_VALID,
            'bank_card_no' => '6217002920131968100',
            'province_code' => '430000',
            'city_code' => '430100',
            'bank_branch_name' => '金盾支行',
            'reserved_telephone' => '18127820722',
        ];

        Bankcard::model(Bankcard::SCENARIO_CREATE)->saveModel($params);
    }

    /**
     * 从 saas 构造请求参数
     */
    public function testBindStart()
    {
        /** 构造请求参数 */
        $params = [
            'token' => $this->getToken(5),
            'bank_card_no' => '6217002920131968100',
            'province_code' => '430000',
            'city_code' => '430100',
            'bank_branch_name' => '金盾支行',
            'reserved_telephone' => '15700767943',
        ];
        $this->json('POST', 'app/bankcard/bind-start', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);

        $response = ArrayHelper::jsonToArray($this->response->content())['data'];

        //缓存 response
        $this->setCache($this->bindParamsKey, $response);
    }

    /**
     * services 发送短验
     */
    public function testBindSendCode()
    {
        $this->getToken(5);
        //获取 response 缓存
        $response = $this->getCache($this->bindParamsKey);
//        $response = [
//            "request_params" =>
//                [
//                    "app_key" => "KmV72310RbWFcDWc",
//                    "bank_card_account" => "王沙亮",
//                    "bank_card_no" => "6217002920131968100",
//                    "bank_card_telephone" => "15700767943",
//                    "id_card_no" => "430381199701085015",
//                    "notice_url" => "http://saas-wangsl.dev23.jiumiaodai.com/bankcard/notice",
//                    "random_str" => "gsSZLKVKsuteSTQYZM3g7aBN8b4qVd2S",
//                    "remark" => "saas鉴权绑卡",
//                    "time" => 1548336419,
//                    "trade_platform" => "yeepay",
//                    "user_id" => 10,
//                    "sign" => "e229558073c949cd92f983d59704f7e8",
//                ],
//              "request_send_url" => "http://api.services.dev23.jiumiaodai.com/app/bankcard/bind_send_code",
//              "request_confirm_url" => "http://api.services.dev23.jiumiaodai.com/app/bankcard/bind_confirm_code",
//        ];

        /** 发送短验到支付系统 */
//        $response['request_send_url'] = "http://services-wangsl.dev23.jiumiaodai.com/app/bankcard/bind_send_code";
//        $response['request_confirm_url'] = "http://services-wangsl.dev23.jiumiaodai.com/app/bankcard/bind_confirm_code";

        $requestSendUrl = $response['request_send_url'];
        $requestParams = $response['request_params'];
        $sendParams = $requestParams;

        $sendCodeResult = $this->postSend($requestSendUrl, $sendParams);

        $this->assertTrue($sendCodeResult['code'] == 18000, '发送短验失败' . $sendCodeResult['msg']);

        //缓存 发送短验得到的 request_no
        $this->setCache($this->requestNoKey, $sendCodeResult['data']);
    }

    /**
     * services 确认短验
     */
    public function testBindConfirmCode()
    {
        $token = $this->getToken(5);
        $response = $this->getCache($this->bindParamsKey);
        $sendCodeResult = $this->getCache($this->requestNoKey);

//        $response['request_confirm_url'] = 'http://services-wangsl.dev23.jiumiaodai.com/app/bankcard/bind_confirm_code';
//        $response['request_params']['bank_card_no'] = '6228481118636433772';
//        $response['request_params']['user_id'] = '10';
//        $sendCodeResult = [
//            'request_no' => 'ED62902B9A2694A8AA00108CC1C257EE',
//        ];


        $requestConfrimUrl = $response['request_confirm_url'];

        /** 支付系统确认短验 */
        $confirmParams = array_merge($sendCodeResult, [
            'sms_code' => '000000',
            'token' => $token,
        ]);
        $confirmCodeResult = $this->postSend($requestConfrimUrl, $confirmParams);

        $this->assertTrue($confirmCodeResult['code'] == 18000, '确认短验失败,msg:' . $confirmCodeResult['msg']);

        /** 确认数据回调正常 */
        $bankCardNo = $response['request_params']['bank_card_no'];
        $userId = BankCard::model()->decodeUserIdentifying($response['request_params']['user_identifying'])['user_id'];
        $model = BankCard::getByUserIdAndNo($userId, $bankCardNo, [
            'status' => BankCard::STATUS_ACTIVE,
        ]);

        $this->assertTrue(isset($model));
    }

    public function testBankCardAuthBindNotice()
    {
        $data = $this->getCache($this->bindParamsKey)['request_params'];
        $userId = $data['user_id'];
        $bankCardAccount = $data['bank_card_account'];
        $bankCardNo = $data['bank_card_no'];
        $bankCardTelephone = $data['bank_card_telephone'];
        $idCardNo = $data['id_card_no'];
        $appKey = config('domain.public_services_config.app_key');
        $secretKey = config('domain.public_services_config.app_secret_key');

        /** 构造请求参数 */
        $params = [
            "app_key" => $appKey,
            "bank_card_account" => $bankCardAccount,
            "bank_card_no" => $bankCardNo,
            "bank_card_telephone" => $bankCardTelephone,
            "id_card_no" => $idCardNo,
            "msg" => "通知业务成功",
            "remark" => "",
            "request_time" => date('Y-m-d H:i:s'),
            "status" => "SUCCESS",
            "time" => time(),
            "user_identifying" => $bankCardTelephone . '_' . $userId,
        ];
        $params['sign'] = SignHelper::sign($params, $secretKey);

        $this->json('POST', 'app/callback/bank-card-auth-bind-notice', $params)->seeJson([
            'code' => self::SUCCESS_CODE,
        ]);
    }
}
