<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 15:47
 */

namespace Api\Services\User;


use Api\Models\Inbox\Inbox;
use Api\Models\Order\OrderDetail;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Services\BaseService;
use Api\Services\Inbox\InboxServer;
use Api\Services\Order\OrderServer;
use Carbon\Carbon;
use Common\Models\Config\Config;
use Common\Models\User\UserBlack;
use Common\Services\Config\LoanMultipleConfigServer;

class UserServer extends BaseService
{

    const SCENARIO_IDENTITY_START_UP = 'start_up';//启动页请求场景

    /**
     *
     * @return User
     */
    public function home($isDetail = false)
    {
        /** @var $user User */
        $user = \Auth::user();
        $user = $user->setScenario(User::SCENARIO_HOME)->getText();
        $user->email = $user->userInfo->email ?? '';
        $user->identityStatus = $user->getIdentityStatus();
        $user->personalInfoStatus = $user->getPersonalInfoStatus();
        $user->contactsStatus = $user->getContactsStatus();
        //过期时间contacts
        if ( $user->contactsStatus == UserAuth::AUTH_STATUS_EXPIRE ) {
            $user->contactseEpireDate = $user->getContactseEpireDate();
        }
        $user->telephoneStatus = $user->getTelephoneStatus();
        $user->workStatus = $user->getUserWorkStatus();
        $user->idCardStatus = $user->getIdCardStatus();
        $user->basicDetailStatus = $user->getBaseInfoStatus();
        //过期时间basicDetail
        if ( $user->basicDetailStatus == UserAuth::AUTH_STATUS_EXPIRE ) {
            $user->basicDetailEpireDate = $user->getBasicDetailEpireDate();
        }
        $user->isCompleted = $user->getIsCompleted();
        $user->optionalIsCompleted = $user->getOptionalIsCompleted();
        $user->bankCardStatus = $user->getBankCardStatus();
        $user->bankChannelName = "";
        $user->paymentType = "";
        //过期时间bankCard
        if ( $user->bankCardStatus == UserAuth::AUTH_STATUS_EXPIRE ) {
            $user->bankCardEpireDate = $user->getBankCardEpireDate();
        }
		$user->bankCardNo = '';
        if($user->order){
            $user->bankChannelName = $user->order->getPaymentChannel();
            $user->paymentType = $user->order->getPaymentType();
            if("Gcash" == $user->bankChannelName){
                $user->bankCardNo = $user->telephone;
            }else{
                $user->bankCardNo = OrderDetail::model()->getBankCardNo($user->order);
            }
        }
        $user->address = $user->userInfo->address ?? '';
        $user->unreadInboxSum = Inbox::getUnreadSum($user->id);
        $user->panCardStatus = $user->getPanCardStatus();
        $user->quality = $user->getRealQuality();
        unset($user->userInfo);
        unset($user->bankCard);
        #首页最大可借额度
        $user->quota = LoanMultipleConfigServer::server()->getLoanAmountMax($user);
        $user->hasUnreadMsg = InboxServer::server()->hasUnreadMessage($user->id);
        //skypay和dragonpay都去掉了coins这个支付渠道，而我们APP之前做了一个功能，就是复贷会直接取已经有的bank值进行填充，就会导致有用户选择了coins而无法放款成功接口不返回account_no
        if("coins" == strtolower($user->bankChannelName)){
            $user->order->user->bankCard->account_no = '';
            $user->order->user->bankCard->channel = '';
        }
        if ($isDetail) {
            return ['user' => $user];
        }
        $lastOrder = OrderServer::server()->getLastOrder($user);
        unset($user->order, $user->orders);
        return ['user' => $user, 'lastOrder' => $lastOrder];
    }

    public function identity($data)
    {
        $scenario = array_get($data, 'scenario');
        /** @var $user User */
        $user = \Auth::user();
        //$ocrDataServer = new OcrDataServer();
        // 认证项配置
        $settingAuth = Config::model()->getAuthSetting();
        if ($settingAuth && is_array($settingAuth)) {
            foreach ($settingAuth as $settingAuthKey => &$settingAuthVal) {
                array_get($settingAuthVal, 'closeTip') == '' && $settingAuthVal['closeTip'] = "Please fill in the reminder information of {$settingAuthKey}";
            }
        }
        $auth = [
            'user_id' => $user->id,
            'fullname' => $user->fullname,
            'quality' => $user->getRealQuality(),
            'baseInfoStatus' => $user->getBaseInfoStatus(),
            'personalInfoStatus' => $user->getPersonalInfoStatus(),
            'contactsStatus' => $user->getContactsStatus(),
            'faceStatus' => $user->getFaceStatus(),
            'aadhaarCardStatus' => $user->getAadhaarCardStatus(),
            'aadhaarCardKYCStatus' => $user->getAadhaarCardKYCStatus(),
            'panCardStatus' => $user->getPanCardStatus(),
            'addressAuthStatus' => $user->getAddressAuthStatus(),
            'facebookAuthStatus' => $user->getFacebookAuthStatus(),
            'isCannotAuthBlack' => UserBlack::model()->isCannotAuth($user->telephone),
            'authSetting' => $settingAuth,
//            'pan_card' => [
//                'ocr' => $ocrDataServer->panCard($user),
//                'status' => $user->getPanCardStatus(),
//            ],
//            'address_card' => [
//                'address_aadhaar' => [
//                    'ocr' => $ocrDataServer->addressAadhaar($user),
//                    'status' => $user->getAddressAadhaarStatus(),
//                ],
//                'voter_id_card' => [
//                    'ocr' => $ocrDataServer->addressVoter($user),
//                    'status' => $user->getAddressVoterIdCardStatus(),
//                ],
//                'passport' => [
//                    'ocr' => $ocrDataServer->addressPassport($user),
//                    'status' => $user->getAddressPassportStatus(),
//                ]
//            ],
            'bankCardStatus' => $user->getBankCardStatus(),
        ];
        if ($scenario == self::SCENARIO_IDENTITY_START_UP) {
            $auth['has_order'] = OrderServer::server()->hasOrder($user);
            $auth['is_reject'] = $user->order && $user->order->isRejected();
            $auth['to_index'] = $auth['has_order'] || $auth['is_reject'];//终端是否去首页
        }
        # 有无进行中的订单
        $auth['hasProgressOrder'] = false;
        if ($user->order && $user->order->isNotComplete()) {
            $auth['hasProgressOrder'] = true;
        }
        return $auth;
    }

    public function getDailyRegisterCount($date = null)
    {
        $dateCarbon = Carbon::today();
        if (!is_null($date)) {
            $dateCarbon = Carbon::parse($date);
        }

        $where = [
            'status' => User::STATUS_NORMAL,
        ];

        return User::query()->where($where)
            ->whereBetween('created_at', [$dateCarbon->toDateString(), $dateCarbon->addDay()->toDateString()])
            ->count();
    }
}
