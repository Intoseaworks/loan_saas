<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Bankcard;

use Api\Models\Bankcard\Bankcard;
use Api\Models\Bankcard\BankcardPeso;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Services\BaseService;
use Api\Services\User\UserAuthServer;
use Common\Services\Pay\BasePayServer;
use Common\Utils\Data\ValidtionHelp;
use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use JMD\App\lumen\Utils;
use JMD\JMD;
use JMD\Libs\Services\BaseRequest;
use JMD\Libs\Services\PayService;
use JMD\Utils\SignHelper;

class BankcardServer extends BaseService
{
    /**
     * 获取银行卡bin（废弃）
     * @param $bankcardNo
     * @return mixed
     */
    public function bin($bankcardNo)
    {
        if (empty($bankcardNo)) {
            return $this->outputException('银行卡号为空');
        }
        if (!is_numeric($bankcardNo) || !ValidtionHelp::validBankCardNo($bankcardNo)) {
            return $this->outputException('银行卡格式错误');
        }
        try {
            $cacheKey = 'bin:' . $bankcardNo;
            /** 设置缓存 24小时 */
            return Cache::remember($cacheKey, 24 * 60, function () use ($bankcardNo) {
                $autoPayPlatform = BasePayServer::server()->getAutoPayChannel();
                /** 获取商户放款配置支付渠道 */
                $tradePlatform = current($autoPayPlatform);
                JMD::init(['projectType' => 'lumen']);
                $payServer = new PayService();
                $api = $payServer->bankcardBin;
                $payServer->request->setUrl($api);
                $payServer->request->setData([
                    'bankcard_no' => $bankcardNo,
                    'trade_platform' => $tradePlatform
                ]);
                $result = $payServer->request->execute();
                if ($result->isSuccess()) {
                    return $result->getData();
                }
                EmailHelper::send($result->getAll(), '卡bin查询失败');
            });
        } catch (\Exception $e) {
            EmailHelper::sendException($e, '卡bin查询失败');
            return false;
        }
    }

    public function create($params)
    {
        DB::beginTransaction();

        $userId = array_get($params, 'user_id');
        $status = array_get($params, 'status', Bankcard::STATUS_WAIT_AUTH);
        BankCard::clearStatus($userId, $status);

        $params = [
            'user_id' => $userId,
            'no' => array_get($params, 'no'),
            'name' => array_get($params, 'name'),
            'reserved_telephone' => array_get($params, 'reserved_telephone'),
            'bank_name' => array_get($params, 'bank_name'),
            'bank' => array_get($params, 'bank'),
            'bank_branch_name' => array_get($params, 'bank_branch_name'),
            'province_code' => array_get($params, 'province_code'),
            'city_code' => array_get($params, 'city_code'),
            'status' => $status,
        ];

        $model = Bankcard::model(Bankcard::SCENARIO_CREATE)->saveModel($params);

        if (!$model) {
            DB::rollBack();
            return $this->outputException('银行卡信息保存失败');
        }

        DB::commit();

        return $model;


    }

    /**
     * 设置用户银行卡认证状态
     * 模拟数据
     *
     * @param $userId
     */
    public function setBankCardStatus($userId)
    {
        $user = User::model()->getOne($userId);
        //用错方法
        //BankCard::clearStatus($user->id, UserAuth::TYPE_BANKCARD);
        $params = [
            'user_id' => $user->id,
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
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BANKCARD);
    }

    /**
     * 判断能否绑定银行卡
     * @param $user
     * @return bool
     */
    public function canBindBankCard(User $user)
    {
        // 判断能否绑定银行卡，未完成 身份证正反面 不能进行绑定
        if (UserAuthServer::server()->validAuth($user,
                [UserAuth::TYPE_ID_BACK, UserAuth::TYPE_ID_FRONT]) &&
            $user->id_card_no && $user->fullname) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param $user
     * @param $params
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function bindStart($user, $params)
    {
        $bankCardInfo = $this->bin($params['bank_card_no']);
        if (!$bankCardInfo) {
            $this->outputException('银行卡号不正确，请重新输入');
        }

        $createData = [
            'user_id' => $user->id,
            'no' => $params['bank_card_no'],
            'name' => $user->fullname,
            //卡bin 获取银行卡所属银行 与 简写
            'bank' => $bankCardInfo['bankcode'] ?? '',
            'bank_name' => $bankCardInfo['bankname'] ?? '',
            'reserved_telephone' => $params['reserved_telephone'],
            'bank_branch_name' => $params['bank_branch_name'],
            'province_code' => $params['province_code'],
            'city_code' => $params['city_code'] ?? '',
            'status' => Bankcard::STATUS_WAIT_AUTH,
        ];
        $model = $this->create($createData);

        if (!$model) {
            $this->outputException('银行卡信息保存失败');
        }

        $sendData = $this->constructSendParams($user, $model);

        return $sendData;
    }
    
    public function pesoBindStart($user, $params){
        DB::beginTransaction();
        $userId = array_get($params, 'user_id');
        $status = array_get($params, 'status', Bankcard::STATUS_WAIT_AUTH);
        BankCard::clearStatus($userId, $status);
        
        switch($params['payment_type']){
            
        }

        $params = [
            'user_id' => $userId,
            'no' => array_get($params, 'no'),
            'name' => array_get($params, 'name'),
            'reserved_telephone' => array_get($params, 'reserved_telephone'),
            'bank_name' => array_get($params, 'bank_name'),
            'bank' => array_get($params, 'bank'),
            'bank_branch_name' => array_get($params, 'bank_branch_name'),
            'province_code' => array_get($params, 'province_code'),
            'city_code' => array_get($params, 'city_code'),
            'status' => $status,
        ];

        $model = Bankcard::model(Bankcard::SCENARIO_CREATE)->saveModel($params);

        if (!$model) {
            DB::rollBack();
            return $this->outputException('银行卡信息保存失败');
        }

        DB::commit();

        return $model;
    }
    
    /**
     * 构造请求 service 鉴权绑卡的参数
     * @param User $user
     * @param Bankcard $model
     *
     * @return array
     * @throws \Exception
     */
    public function constructSendParams(User $user, BankCard $model)
    {
        $config = Utils::getParam(BaseRequest::CONFIG_NAME);
        /** 印牛服务外网域名 */
        $servicesDomain = config('domain.public_services_config.outer_endpoint');
        $apiDomain = config('config.api_client_domain');
        $authPlatform = current(BasePayServer::server()->getAutoPayChannel());
        $authNoticeUrl = $apiDomain . '/app/callback/bank-card-auth-bind-notice';
        $authRequestSendUrl = $servicesDomain . 'api/bankcard/bind_send_code';
        $authRequestConfirmUrl = $servicesDomain . 'api/bankcard/bind_confirm_code';
        $requestParams = [
            'app_key' => $config['app_key'],
            'bank_card_no' => $model->no,
            'bank_card_account' => $user->fullname,
            'bank_card_telephone' => $model->reserved_telephone,
            'id_card_no' => $user->id_card_no,
            'user_identifying' => $model->encodeUserIdentifying(),
            'trade_platform' => $authPlatform,
            'notice_url' => $authNoticeUrl,
            'remark' => '鉴权绑卡',
            'bank_name' => $model->bank_name,
            'bank_code' => '',
        ];
        $requestParams['sign'] = SignHelper::sign($requestParams, $config['app_secret_key']);

        $data = [
            'request_params' => $requestParams,
            'request_send_url' => $authRequestSendUrl,
            'request_confirm_url' => $authRequestConfirmUrl,
        ];

        return $data;
    }
}
