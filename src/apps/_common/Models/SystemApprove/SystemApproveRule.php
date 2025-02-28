<?php

namespace Common\Models\SystemApprove;

class SystemApproveRule
{
    /******************************************** 申请规则 *******************************************************/
    const RULE_APPLY_10001 = '10001'; // age criteria failed  年龄不符合
    const RULE_APPLY_10002 = '10002'; // customer has been rejected lately 近期有被拒订单
    const RULE_APPLY_10003 = '10003'; // customer hit blacklist 命中黑名单
    const RULE_APPLY_10004 = '10004'; // education criteria failed  学历不符合
    const RULE_APPLY_10005 = '10005'; // occupation criteria failed  职业不符合
    const RULE_APPLY_10006 = '10006'; // apply process criteria failed 申请行为异常
    const RULE_APPLY_10007 = '10007'; // apply process criteria failed 申请行为异常
    const RULE_APPLY_10008 = '10008'; // region criteria failed  地址不符合
    const RULE_APPLY_10009 = '10009'; // multi head criteria failed 多头数据不合格
    const RULE_APPLY_10010 = '10010'; // multi head criteria failed 多头数据不合格
    const RULE_APPLY_10011 = '10011'; // region criteria failed 地址不符合
    const RULE_APPLY_10012 = '10012'; // apply process criteria failed 申请行为异常
    const RULE_APPLY_10013 = '10013'; // region criteria failed 地址不符合
    const RULE_APPLY_10014 = '10014'; // apply process criteria failed 申请行为异常
    const RULE_APPLY_10015 = '10015'; // contacts criteria failed 联系人不符合
    const RULE_APPLY_10016 = '10016'; // applists criteria failed 应用列表不合格
    const RULE_APPLY_10017 = '10017'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_10018 = '10018'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_10019 = '10019'; // channel criteria failed  渠道拒绝
    const RULE_APPLY_10020 = '10020'; // contacts criteria failed 联系人不符合
    const RULE_APPLY_10021 = '10021'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_10022 = '10022'; // region criteria failed 地址不符合
    const RULE_APPLY_10023 = '10023'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_10024 = '10024'; // region criteria failed 地址不符合
    
    
    const RULE_APPLY_10025 = '10025';
    /*nio.wang 20200806*/
    const RULE_APPLY_10026 = '10026';//黑名单
    const RULE_APPLY_10027 = '10027';//没有what's app
    const RULE_APPLY_10028 = '10028';//没有Riskcloud黑名单
    const RULE_APPLY_10029 = '10029';//没有Riskcloud黑名单
    const RULE_APPLY_10030 = '10030';//存在未完成订单
    /******************************************** 设备验证 *******************************************************/
    const RULE_DEVICE_20001 = '20001'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20002 = '20002'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20003 = '20003'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20004 = '20004'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20005 = '20005'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20006 = '20006'; // customer's device in associate with other account 设备存在关联用户
    const RULE_DEVICE_20007 = '20007'; // device criteria failed 设备不符合
    const RULE_DEVICE_20008 = '20008'; // device criteria failed 设备不符合
    const RULE_DEVICE_20009 = '20009'; // device criteria failed 设备不符合
    const RULE_DEVICE_20010 = '20010'; // device criteria failed 设备不符合
    const RULE_DEVICE_20011 = '20011'; // device criteria failed 设备不符合
    /******************************************** 身份验证 *******************************************************/
    const RULE_AUTH_30001 = '30001'; // customer's bank card in associate with other account 银行卡未通过
    const RULE_AUTH_30002 = '30002'; // customer's bank card in associate with other account 银行卡未通过
    const RULE_AUTH_30003 = '30003'; // customer's bank card in associate with other account 银行卡未通过
    const RULE_AUTH_30004 = '30004'; // customer's identity in associate with other account 关联账户不符合
    const RULE_AUTH_30005 = '30005'; // customer's identity in associate with other account 关联账户不符合
    const RULE_AUTH_30006 = '30006'; // customer's identity in associate with other account 关联账户不符合
    const RULE_AUTH_30007 = '30007'; // customer's identity in associate with other account 关联账户不符合
    const RULE_AUTH_30008 = '30008'; // face recognition failed 人脸不合格
    const RULE_AUTH_30009 = '30009'; // customer's identity in associate with other account 关联账户不符合
    const RULE_AUTH_30010 = '30010'; // customer's identity verification failed 身份认证未通过
    const RULE_AUTH_30011 = '30011'; // customer's identity verification failed 身份认证未通过
    const RULE_AUTH_30012 = '30012'; // customer's identity verification failed 身份认证未通过
    const RULE_AUTH_30013 = '30013'; // customer's identity verification failed 身份认证未通过
    const RULE_AUTH_30014 = '30014'; // customer's identity verification failed 身份认证未通过
    
    const RULE_AUTH_30015 = '30015';//用户Nbfc验证失败
    const RULE_AUTH_30016 = '30016';//Kreditone 过低
    /******************************************** 通讯录验证 *******************************************************/
    const RULE_CONTACTS_40001 = '40001'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40002 = '40002'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40003 = '40003'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40004 = '40004'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40005 = '40005'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40006 = '40006'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40007 = '40007'; // contacts criteria failed 通讯录不合格
    const RULE_CONTACTS_40008 = '40008'; // contacts criteria failed 通讯录不合格
    /******************************************** 借款行为 *******************************************************/
    const RULE_BEHAVIOR_50001 = '50001'; // customer has been rejected too many times 历史被拒次数过多
    const RULE_BEHAVIOR_50002 = '50002'; // previous loan has bad behavior 历史表现不良
    const RULE_BEHAVIOR_50003 = '50003'; // previous loan has bad behavior 历史表现不良
    const RULE_BEHAVIOR_50004 = '50004'; // previous loan has bad behavior 历史表现不良
    const RULE_BEHAVIOR_50005 = '50005'; // previous loan has bad behavior 历史表现不良
    const RULE_BEHAVIOR_50006 = '50006'; // previous loan has bad behavior 历史表现不良
    /******************************************** 其他规则 *******************************************************/
    const RULE_OTHER_60001 = '60001'; // applists criteria failed  应用列表不合格
    const RULE_OTHER_60002 = '60002'; // applists criteria failed 应用列表不合格
    const RULE_OTHER_60003 = '60003'; // customer's identity in associate with other account 关联账户不符合
    const RULE_OTHER_60004 = '60004'; // customer's identity in associate with other account 关联账户不符合
    const RULE_OTHER_60005 = '60005'; // customer's identity in associate with overdue account 关联账号申请条件不符
    const RULE_OTHER_60006 = '60006'; // applists criteria failed  应用列表不合格
    const RULE_OTHER_60007 = '60007'; // applists criteria failed 应用列表不合格

    /******************************************** 多头规则 *******************************************************/
    const RULE_MULTIPOINT_70001 = '70001'; // multi head criteria failed 多头数据不合格

    /******************************************** trueX相关规则 *******************************************************/
    const RULE_APPLY_80001 = '80001'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_80002 = '80002'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_80003 = '80003'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_80004 = '80004'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80005 = '80005'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80006 = '80006'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80007 = '80007'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80008 = '80008'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80009 = '80009'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80010 = '80010'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80011 = '80011'; // customer's personal infomation verification failed 个人信息验证失败
    const RULE_APPLY_80012 = '80012'; // contacts verification failed 通讯录认证未通过
    const RULE_APPLY_80013 = '80013'; // contacts verification failed 通讯录认证未通过

    /******************************************** 征信报告规则 *******************************************************/
    const RULE_90001 = '90001';
    const RULE_90002 = '90002';
    const RULE_90003 = '90003';
    const RULE_90004 = '90004';
    const RULE_90005 = '90005';
    const RULE_90006 = '90006';
    const RULE_90007 = '90007';
    const RULE_90008 = '90008';
    const RULE_90009 = '90009';
    const RULE_90010 = '90010';
    const RULE_90011 = '90011';
    const RULE_90012 = '90012';
    const RULE_90013 = '90013';
    const RULE_90014 = '90014';
    const RULE_90015 = '90015';
    const RULE_90016 = '90016';
    const RULE_90017 = '90017';
    /*************************************************************************************************************
     * Rule end
     ************************************************************************************************************/

    /** 具体规则 */
    const SHOW_TYPE_CLASSIFY = [
        self::RULE_APPLY_10001 => '年龄不符合', // age criteria failed  年龄不符合
        self::RULE_APPLY_10002 => '近期有被拒订单', // customer has been rejected lately 近期有被拒订单
        self::RULE_APPLY_10003 => '命中黑名单', // customer hit blacklist 命中黑名单
        self::RULE_APPLY_10004 => '学历不符合', // education criteria failed  学历不符合
        self::RULE_APPLY_10005 => '职业不符合', // occupation criteria failed  职业不符合
        self::RULE_APPLY_10006 => '申请行为异常', // apply process criteria failed 申请行为异常
        self::RULE_APPLY_10007 => '申请行为异常', // apply process criteria failed 申请行为异常
        self::RULE_APPLY_10008 => '地址不符合', // region criteria failed  地址不符合
        self::RULE_APPLY_10009 => '多头数据不合格', // multi head criteria failed 多头数据不合格
        self::RULE_APPLY_10010 => '多头数据不合格', // multi head criteria failed 多头数据不合格
        self::RULE_APPLY_10011 => '地址不符合', // region criteria failed 地址不符合
        self::RULE_APPLY_10012 => '申请行为异常', // apply process criteria failed 申请行为异常
        self::RULE_APPLY_10013 => '地址不符合', // region criteria failed 地址不符合
        self::RULE_APPLY_10014 => '申请行为异常', // apply process criteria failed 申请行为异常
        self::RULE_APPLY_10015 => '联系人不符合', // contacts criteria failed 联系人不符合
        self::RULE_APPLY_10016 => '应用列表不合格', // applists criteria failed 应用列表不合格
        self::RULE_APPLY_10017 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_10018 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_10019 => '渠道拒绝', // channel criteria failed  渠道拒绝
        self::RULE_APPLY_10020 => '联系人不符合', // contacts criteria failed 联系人不符合
        self::RULE_APPLY_10021 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_10022 => '地址不符合', // region criteria failed 地址不符合
        self::RULE_APPLY_10023 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_10024 => '地址不符合', // region criteria failed 地址不符合
        self::RULE_APPLY_10025 => '个人信息验证失败', // region criteria failed 地址不符合
        self::RULE_APPLY_10026 => '黑名单命中', // 黑名单命中
        self::RULE_APPLY_10027 => '没有Whatsapp账号', // 没有Whatsapp账号
        self::RULE_APPLY_10028 => 'Riskcloud黑名单', // Riskcloud黑名单
        
        self::RULE_APPLY_10029 => 'Riskcloud验证失败', // Riskcloud黑名单
        self::RULE_APPLY_10030 => '存在未完成订单',
        self::RULE_APPLY_10035 => 'Airudder验证失败',
        /******************************************** 设备验证 *******************************************************/
        self::RULE_DEVICE_20001 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20002 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20003 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20004 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20005 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20006 => '设备存在关联用户', // customer's device in associate with other account 设备存在关联用户
        self::RULE_DEVICE_20007 => '设备不符合', // device criteria failed 设备不符合
        self::RULE_DEVICE_20008 => '设备不符合', // device criteria failed 设备不符合
        self::RULE_DEVICE_20009 => '设备不符合', // device criteria failed 设备不符合
        self::RULE_DEVICE_20010 => '设备不符合', // device criteria failed 设备不符合
        self::RULE_DEVICE_20011 => '设备不符合', // device criteria failed 设备不符合
        /******************************************** 身份验证 *******************************************************/
        self::RULE_AUTH_30001 => '银行卡未通过', // customer's bank card in associate with other account 银行卡未通过
        self::RULE_AUTH_30002 => '银行卡未通过', // customer's bank card in associate with other account 银行卡未通过
        self::RULE_AUTH_30003 => '银行卡未通过', // customer's bank card in associate with other account 银行卡未通过
        self::RULE_AUTH_30004 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_AUTH_30005 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_AUTH_30006 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_AUTH_30007 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_AUTH_30008 => '人脸不合格', // face recognition failed 人脸不合格
        self::RULE_AUTH_30009 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_AUTH_30010 => '身份认证未通过', // customer's identity verification failed 身份认证未通过
        self::RULE_AUTH_30011 => '身份认证未通过', // customer's identity verification failed 身份认证未通过
        self::RULE_AUTH_30012 => '身份认证未通过', // customer's identity verification failed 身份认证未通过
        self::RULE_AUTH_30013 => '身份认证未通过', // customer's identity verification failed 身份认证未通过
        self::RULE_AUTH_30014 => '身份认证未通过', // customer's identity verification failed 身份认证未通过
        self::RULE_AUTH_30015 => 'Nbfc 未通过',
        self::RULE_AUTH_30016 => 'K 积分未通过',
        /******************************************** 通讯录验证 *******************************************************/
        self::RULE_CONTACTS_40001 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40002 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40003 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40004 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40005 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40006 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40007 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        self::RULE_CONTACTS_40008 => '通讯录不合格', // contacts criteria failed 通讯录不合格
        /******************************************** 借款行为 *******************************************************/
        self::RULE_BEHAVIOR_50001 => '历史被拒次数过多', // customer has been rejected too many times 历史被拒次数过多
        self::RULE_BEHAVIOR_50002 => '历史表现不良', // previous loan has bad behavior 历史表现不良
        self::RULE_BEHAVIOR_50003 => '历史表现不良', // previous loan has bad behavior 历史表现不良
        self::RULE_BEHAVIOR_50004 => '历史表现不良', // previous loan has bad behavior 历史表现不良
        self::RULE_BEHAVIOR_50005 => '历史表现不良', // previous loan has bad behavior 历史表现不良
        self::RULE_BEHAVIOR_50006 => '历史表现不良', // previous loan has bad behavior 历史表现不良
        /******************************************** 其他规则 *******************************************************/
        self::RULE_OTHER_60001 => '应用列表不合格', // applists criteria failed  应用列表不合格
        self::RULE_OTHER_60002 => '应用列表不合格', // applists criteria failed 应用列表不合格
        self::RULE_OTHER_60003 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_OTHER_60004 => '关联账户不符合', // customer's identity in associate with other account 关联账户不符合
        self::RULE_OTHER_60005 => '关联账号申请条件不符', // customer's identity in associate with overdue account 关联账号申请条件不符
        self::RULE_OTHER_60006 => '应用列表不合格', // applists criteria failed  应用列表不合格
        self::RULE_OTHER_60007 => '应用列表不合格', // applists criteria failed 应用列表不合格

        /******************************************** 多头规则 *******************************************************/
        self::RULE_MULTIPOINT_70001 => '多头数据不合格', // multi head criteria failed 多头数据不合格

        /******************************************** trueX相关规则 *******************************************************/
        self::RULE_APPLY_80001 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_80002 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_80003 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_80004 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80005 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80006 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80007 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80008 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80009 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80010 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80011 => '个人信息验证失败', // customer's personal infomation verification failed 个人信息验证失败
        self::RULE_APPLY_80012 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
        self::RULE_APPLY_80013 => '通讯录认证未通过', // contacts verification failed 通讯录认证未通过
    ];

    public static function showTypeClassifyReason(array $ruleCodes)
    {
        $res = [];
        foreach ($ruleCodes as $ruleCode) {
            $reason = array_get(self::SHOW_TYPE_CLASSIFY, $ruleCode);

            if (is_null($reason) && preg_match("/^9.+$/", $ruleCode)) {
                $reason = '征信不合格';
            }
            if (is_null($reason)) {
                $reason = '其他规则';
            }

            $res[] = $reason;
        }

        return array_unique($res);
    }
}
