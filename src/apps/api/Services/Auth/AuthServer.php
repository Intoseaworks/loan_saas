<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Auth;

use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Services\BaseService;
use Common\Models\Approve\ManualApproveLog;
use Common\Utils\Host\HostHelper;
use Exception;
use Illuminate\Http\Request;

class AuthServer extends BaseService
{

    const AUTH_CONFIG = [
        UserAuth::AUTH_IDENTITY => [
            'icon' => '/images/icon/auth/my_icon_authentication@3x.png',
            'name' => 'AUTH_IDENTITY',
            'title' => 'KYC Documents',
        ],
        UserAuth::AUTH_BASE => [
            'icon' => '/images/icon/auth/my_icon_information@3x.png',
            'name' => 'AUTH_BASE',
            'title' => 'Employment Details',
        ],
        UserAuth::AUTH_CONTACTS => [
            'icon' => '/images/icon/auth/my_icon_emergency_contac@2x.png',
            'name' => 'AUTH_CONTACTS',
            'title' => 'Emergency Contacts',
        ]
    ];

    /**
     * @param $param
     * @suppress PhanUndeclaredMethod, PhanTypeMismatchArgument
     * @return mixed
     */
    public function getList($param)
    {
        /** @var User $user */
        $user = \Auth::user();

        //默认回跳地址
        $redirectUrl = $param['redirect_url'] ?? 'saas://close';
        $client = $param['client'] ?? 'android';
        $supplementTips = $this->supplementTips($user);
        $canUpdate = $this->canUpdate($user);

        $identityStatus = intval($user->getIdentityStatus());
        $baseInfoStatus = intval($user->getBaseInfoStatus());
        $personalInfoStatus = intval($user->getPersonalInfoStatus());
        $telephoneStatus = intval($user->getTelephoneStatus());
        $contactsStatus = intval($user->getContactsStatus());
        $userWorkStatus = intval($user->getUserWorkStatus());
        $bankBillStatus = intval($user->getBankBillStatus());
        $facebookAuthStatus = intval($user->getFacebookAuthStatus());
        $OLAAuthStatus = intval($user->getOLAAuthStatus());
        $bankcardStatus = intval($user->getBankCardStatus());

        /** 根据app类型区分认证列表 */
        $appType = app(Request::class)->header('app-type');
        $authArr = [UserAuth::AUTH_IDENTITY, UserAuth::AUTH_BASE, UserAuth::AUTH_CONTACTS];
        if ($appType) {
            $authArr = [
                UserAuth::AUTH_BASE, UserAuth::AUTH_CONTACTS, UserAuth::AUTH_IDENTITY, UserAuth::AUTH_USER_INFO, UserAuth::AUTH_BANK_CARD
            ];
        }
        $closeTip = 'Please complete in order';

        $authList = [
            //身份认证
            UserAuth::AUTH_IDENTITY => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_kyc_documents.png',
                'name' => UserAuth::AUTH_IDENTITY,
                'title' => t(UserAuth::AUTH[UserAuth::AUTH_IDENTITY], 'messages'),
                'url' => '',
                'canUpdate' => false,
                'closeTip' => ($baseInfoStatus && $contactsStatus) ? '' : $closeTip,
                'supplementTip' => $identityStatus != '1' ? array_get($supplementTips, UserAuth::AUTH_IDENTITY, '') : '',
                'status' => $identityStatus,
                'status_text' => $this->checkCreditStatus($identityStatus, $supplementTips),
            ],
            //个人信息
            UserAuth::AUTH_BASE => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_basic_details.png',
                'name' => UserAuth::AUTH_BASE,
                'title' => t(UserAuth::AUTH[UserAuth::AUTH_BASE], 'messages'),
                'url' => '',
                'canUpdate' => $canUpdate,
                'closeTip' => '',
                'supplementTip' => $baseInfoStatus != '1' ? array_get($supplementTips, UserAuth::AUTH_BASE, '') : '',
                'status' => $baseInfoStatus,
                'status_text' => $this->checkCreditStatus($baseInfoStatus, $supplementTips),
            ],
            //运营商认证
            /*UserAuth::AUTH_TELEPHONE => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_airtel@3x.png',
                'name' => UserAuth::AUTH_TELEPHONE,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_TELEPHONE), 'messages'),
                // url 如果没有在 saas-business.php 配置默认取第一条
                'url' => '',
                'canUpdate' => false,
                'supplementTip' => '',
                'status' => $telephoneStatus,
                'status_text' => $this->checkCreditStatus($telephoneStatus),
            ],*/
            //紧急联系人
            UserAuth::AUTH_CONTACTS => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_emergency_contacts.png',
                'name' => UserAuth::AUTH_CONTACTS,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_CONTACTS), 'messages'),
                'url' => '',
                'canUpdate' => $canUpdate,
                'closeTip' => $baseInfoStatus ? '' : $closeTip,
                'supplementTip' => $contactsStatus != '1' ? array_get($supplementTips, UserAuth::AUTH_CONTACTS, '') : '',
                'status' => $contactsStatus,
                'status_text' => $this->checkCreditStatus($contactsStatus, $supplementTips),
            ],
            //工作信息
            /*UserAuth::AUTH_USER_WORK => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/my_icon_accumulation_fund@3x.png',
                'name' => UserAuth::AUTH_USER_WORK,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_USER_WORK), 'messages'),
                'url' => '',
                'canUpdate' => $canUpdate,
                'supplementTip' => '',
                'status' => $userWorkStatus,
                'status_text' => $this->checkCreditStatus($userWorkStatus),
            ],*/
            //facebook验证
            /*UserAuth::AUTH_FACEBOOK => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_facebook@3x.png',
                'name' => UserAuth::AUTH_FACEBOOK,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_FACEBOOK), 'messages'),
                'url' => '',
                'canUpdate' => false,
                'supplementTip' => '',
                'status' => $facebookAuthStatus,
                'status_text' => $this->checkCreditStatus($facebookAuthStatus),
            ],*/
            //银行卡账单
            /*UserAuth::AUTH_BANK_BILL => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/my_icon_creditcard@3x.png',
                'name' => UserAuth::AUTH_BANK_BILL,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_BANK_BILL), 'messages'),
                'url' => '',
                'canUpdate' => false,
                'supplementTip' => '',
                'status' => $bankBillStatus,
                'status_text' => $this->checkCreditStatus($bankBillStatus),
            ],*/
            //OLA认证
            /*UserAuth::AUTH_OLA => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/my_icon_ola@3x.png?v=1',
                'name' => UserAuth::AUTH_OLA,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_OLA), 'messages'),
                'url' => '',
                'canUpdate' => false,
                'supplementTip' => '',
                'status' => $OLAAuthStatus,
                'status_text' => $this->checkCreditStatus($OLAAuthStatus),
            ],*/
            //用户扩展信息
            UserAuth::AUTH_USER_INFO => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_employment_details.png',
                'name' => UserAuth::AUTH_USER_INFO,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_USER_INFO), 'messages'),
                'url' => '',
                'canUpdate' => false,
                'closeTip' => ($baseInfoStatus && $contactsStatus && $identityStatus) ? '' : $closeTip,
                'supplementTip' => '',
                'status' => $personalInfoStatus,
                'status_text' => $this->checkCreditStatus($personalInfoStatus),
            ],
            UserAuth::AUTH_BANK_CARD => [
                'icon' => HostHelper::getDomain() . '/images/icon/auth/icon_bank_details.png',
                'name' => UserAuth::AUTH_BANK_CARD,
                'title' => t(array_get(UserAuth::AUTH, UserAuth::AUTH_BANK_CARD), 'messages'),
                'url' => '',
                'canUpdate' => false,
                'closeTip' => ($baseInfoStatus && $contactsStatus && $identityStatus && $personalInfoStatus) ? '' : $closeTip,
                'supplementTip' => '',
                'status' => $bankcardStatus,
                'status_text' => $this->checkCreditStatus($bankcardStatus),
            ]
        ];
        $creditList = [
            'required' => array_map(function ($authKey) use ($authList) {
                return $authList[$authKey];
            }, $authArr),
            'optional' => array_map(function ($authKey) use ($authList) {
                return $authList[$authKey];
            }, config('saas-business.user_app_optional_auth')),
        ];

        // 选择服务商 && 如果已经认证就不展示h5 URL
        $userAuthProviders = config('saas-business.user_auth_providers', []);
        $handler = function (&$data) use ($userAuthProviders) {
            foreach ($data as $k => &$auth) {
                // url是数组,代表有多中服务商选择.如果没有在 saas-business.php 配置默认取第一条
                if (is_array(array_get($auth, 'url'))) {
                    if (isset($userAuthProviders[$auth['name']])) {
                        $url = $auth['url'][$userAuthProviders[$auth['name']]] ?? null;
                        if (!$url) {
                            throw new Exception("{$userAuthProviders[$auth['name']]}服务商未配置");
                        }
                        if (is_callable($url)) {
                            $url = $url();
                        }
                        $auth['url'] = $url;
                    } else {
                        reset($auth);
                        // 默认取第一个
                        $url = current($auth['url']);
                        if (is_callable($url)) {
                            $url = $url();
                        }
                        $auth['url'] = $url;
                    }
                }

                // 如果已经认证就不展示h5 URl,防刷
                if (array_get($auth, 'url') && array_get($auth, 'status') == UserAuth::AUTH_STATUS_SUCCESS) {
                    $auth['url'] = '';
                }
            }
        };

        $handler($creditList['required']);
        $handler($creditList['optional']);

        //判断是否完善资料
        $creditList['is_completed'] = $user->getIsCompleted();
        $creditList['optional_is_completed'] = $user->getOptionalIsCompleted();
        return $creditList;
    }

    /**
     * 根据认证状态状态文字
     * @param $status
     * @return array
     */
    protected function checkCreditStatus($status, $supplementTips = '')
    {
        switch ($status) {
            case UserAuth::AUTH_STATUS_SUCCESS:
                return "<font color='#CCCCCC'>" . t('已认证', 'auth') . "</font>";
            case UserAuth::AUTH_STATUS_EXPIRE:
                return "<font color='#FF8C00'>" . t('已过期', 'auth') . "</font>";
            case UserAuth::AUTH_STATUS_IN:
                return "<font color='#FFA500'>" . t('认证中', 'auth') . "</font>";
            default:
                if ($supplementTips) {
                    return "<font color='#FF6200'>" . t('无效', 'auth') . "</font>";
                }
                return "<font color='#5F77FF'>" . t('去认证', 'auth') . "</font>";
        }
    }

    protected function supplementTips(User $user)
    {
        $supplementTips = ManualApproveLog::model()->supplementTips($user);
        if (!$supplementTips || !is_array($supplementTips)) {
            return [];
        }
        $supplementTipsByAuth = [];
        foreach ($supplementTips as $supplementName => $supplementTip) {
            if ($authName = array_get(ManualApproveLog::SUPPLEMENT_AUTHS, $supplementName)) {
                $supplementTipByAuth = array_get($supplementTipsByAuth, $authName, '');
                if ($supplementTipByAuth) {
                    $supplementTipByAuth = $supplementTipByAuth . ';';
                }
                $supplementTip = t($supplementTip, 'approve');
                $supplementTipsByAuth[$authName] = $supplementTipByAuth . $supplementTip;
            }
        }
        return $supplementTipsByAuth;
    }

    public function canUpdate(User $user)
    {
        $order = $user->order;
        if (!$order) {
            return true;
        }
        if ($order->isApprovePass()) {
            return false;
        }
        return true;
    }

}
