<?php

use Admin\Services\Data\DataServer;
use Admin\Services\Risk\RiskServer;
use Common\Models\User\UserAuth;

return [
    /**
     * 完善所需认证项
     */
    'USER_AUTH_TYPE_IS_COMPLETED' => json_encode([
        UserAuth::TYPE_FACES,
        //UserAuth::TYPE_FACEBOOK,
        //UserAuth::TYPE_ADDRESS,
        UserAuth::TYPE_BASE_INFO,
        UserAuth::TYPE_USER_EXTRA_INFO,
        UserAuth::TYPE_PAN_CARD,
        //UserAuth::TYPE_AADHAAR_CARD,
        //UserAuth::TYPE_AADHAAR_CARD_KYC,
        UserAuth::TYPE_CONTACTS,
        //UserAuth::TYPE_BANKCARD,
    ]),

    /**
     * 选填所需认证项
     */
    'USER_OPTIONAL_AUTH_TYPE_IS_COMPLETED' => json_encode([
        UserAuth::TYPE_TELEPHONE,
        UserAuth::TYPE_OLA,
        UserAuth::TYPE_USER_WORK,
    ]),

    /**
     * app 认证列表项
     */
    'USER_APP_AUTH' => json_encode([
        UserAuth::AUTH_IDENTITY,
        UserAuth::AUTH_BASE,
        UserAuth::AUTH_CONTACTS,
        //UserAuth::AUTH_FACEBOOK,
        //UserAuth::AUTH_BANK_BILL,
    ]),
    /**
     * app 认证列表选填项
     */
    'USER_APP_OPTIONAL_AUTH' => json_encode([
        //UserAuth::AUTH_TELEPHONE,
        //UserAuth::AUTH_OLA,
        //UserAuth::AUTH_USER_WORK,
    ]),

    /**
     * 审批列表项
     */
    'APPROVE_TABS' => json_encode([
//        RiskServer::MANUAL_RISK,
//        RiskServer::SYS_CHECK_DETAIL,
        DataServer::USER,
        RiskServer::OPERATOR_REPORT,
//        RiskServer::DUO_TOU,
//        RiskServer::ALIPAY_REPORT,
//        RiskServer::CONTACTS,
//        RiskServer::USER_SMS,
        DataServer::ORDER,
        DataServer::BANK_CARDS,
//        RiskServer::USER_POSITION,
        RiskServer::USER_APP,
    ]),

    /**
     * 用户信息列表选项
     */
    'USER_INFO_TABS' => json_encode([
        DataServer::USER,
//        RiskServer::SYS_CHECK_DETAIL,
//        RiskServer::MANUAL_RISK,
//        RiskServer::OPERATOR_REPORT,
//        RiskServer::DUO_TOU,
//        RiskServer::ALIPAY_REPORT,
//        RiskServer::CONTACTS,
//        RiskServer::USER_SMS,
        DataServer::ORDER_LIST,
        DataServer::BANK_CARDS,
//        RiskServer::USER_POSITION,
        RiskServer::USER_APP,
    ]),

    /**
     * 认证name => 认证项(AuthServer Url数组的下标)
     * UserAuth::AUTH_TELEPHONE => UserAuth::AUTH_TELEPHONE_PROVIDER_XINYAN,
     * 用户认证可以选择的服务商
     */
    'USER_AUTH_PROVIDERS' => json_encode([
        // 运营商
        UserAuth::AUTH_TELEPHONE => UserAuth::AUTH_TELEPHONE_PROVIDER_XINYAN,
    ]),

];
