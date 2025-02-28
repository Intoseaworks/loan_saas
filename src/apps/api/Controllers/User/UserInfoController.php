<?php

namespace Api\Controllers\User;

use Api\Models\BankCard\BankCardPeso;
use Api\Models\Order\Order;
use Api\Rules\User\UserInfoRule;
use Api\Services\Bankcard\BankcardServer;
use Api\Services\Third\WhatsappServer;
use Api\Services\User\UserInfoServer;
use Api\Services\User\UserServer;
use Auth;
use Common\Response\ApiBaseController;
use Common\Utils\Data\StringHelper;
use Yunhan\Utils\Env;

class UserInfoController extends ApiBaseController
{
    /**
     * 获取用户详情
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show()
    {
        $server = UserInfoServer::server();
        $server->info($this->identity());

        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData());
    }

    /**
     * 保存用户详情
     * @param UserInfoRule $rule
     * @return array
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(UserInfoRule $rule)
    {
        $user = $this->identity();
        if ($user->order && $user->order->isNotComplete()) {
            return $this->resultFail('当前有进行中订单，无法修改个人信息');
        }
        $server = UserInfoServer::server();
        $params = $this->getParams();
        $params = $server->handleUserInfo($params);

        if (!$rule->validate(UserInfoRule::SCENARIO_CREATE, $params)) {
            return $this->resultFail($rule->getError());
        }

        $server->postInfo($this->identity(), $params);

        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess([], '保存成功');
    }

    /**
     * 判断&获取银行卡信息
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bankCardInfo()
    {
        $user = $this->identity();
        //废弃银行卡
        if ( $this->request->isMethod('POST') ){
            //银行账户用于放款不能删除20211019
            $bankcard = BankCardPeso::model()->whereId($this->params['bankcard_id'])->where('status', BankCardPeso::STATUS_ACTIVE)->first();
//            $bankcard_repay = $user->order->orderDetails->where('key','bank_card_no')->where('value',$bankcard->account_no)->first();
//            $order_repay = !in_array($user->order->status,array_merge(Order::STATUS_COMPLETE,[Order::STATUS_CREATE]));
//            if ( $bankcard_repay && $order_repay ){
            if ( $bankcard ){
                return $this->resultFail('This account cannot be deleted');
            }
            BankCardPeso::model()->clearPeralending($this->params['bankcard_id']);
            return $this->resultSuccess();
        }

        if (!BankcardServer::server()->canBindBankCard($user)) {
            return $this->resultFail('请先完善身份认证！');
        }

        return $this->resultSuccess($user->bankCard);
    }

    /**
     * 工作邮箱验证
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\RuleException
     */
    public function workEmailValid(UserInfoRule $rule)
    {
        $params = $rule->validateE($rule::SCENARIO_WORK_EAMIL_VALID);
        if (UserInfoServer::server()->workEmailValid($params['email'], $params['captcha'], $this->identity())) {
            return $this->resultSuccess([], t('工作邮箱验证成功', 'captcha'));
        }

        return $this->resultFail(t('工作邮箱验证失败', 'captcha'));
    }

    /**
     * 工作信息保存
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createUserWork(UserInfoRule $rule)
    {
        $this->identity();
        $parmas = $rule->validateE($rule::SCENARIO_CREATE_USER_WORK);
        if ($userWork = UserInfoServer::server()->createUserWork($parmas)) {
            return $this->resultSuccess($userWork);
        }

        return $this->resultFail(t('工作信息保存失败', 'auth'));
    }

    /**
     * 用户信息保存
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\RuleException
     */
    public function createUserInfo(UserInfoRule $rule)
    {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_CREATE_USER_INFO);
        if ($userInfo = UserInfoServer::server()->createUserInfo($params)) {
            return $this->resultSuccess($userInfo);
        }

        return $this->resultFail(t('个人信息保存失败', 'auth'));
    }

    /**
     * 用户基本信息保存
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\RuleException
     */
    public function createBaseUserInfo(UserInfoRule $rule)
    {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_CREATE_BASE_USER_INFO);
        if ($baseUserInfo = UserInfoServer::server()->createBaseUserInfo($params)) {
            return $this->resultSuccess($baseUserInfo);
        }

        return $this->resultFail(t('基本信息保存失败', 'auth'));
    }

    /**
     * 用户订单意向收集
     */
    public function createUserIntention(UserInfoRule $rule)
    {
        $this->identity();
        $params = $rule->validateE($rule::SCENARIO_CREATE_USER_INTENTION);
        if ($baseUserInfo = UserInfoServer::server()->createUserIntention($params)) {
            return $this->resultSuccess($baseUserInfo);
        }
        return $this->resultFail(t('用户意向保存失败', 'auth'));
    }

    /**
     * 获取用户信息
     *
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getUserInfo()
    {
        $this->identity();
        $userInfo = UserInfoServer::server()->getUserInfo(Auth::id());
        return $this->resultSuccess($userInfo);
    }

    /**
     * 获取用户详情
     *
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationExceptionk
     */
    public function getUserDetail()
    {
        $this->identity();
        return $this->resultSuccess(UserServer::server()->home(true));
        /*$userDetail = UserInfoServer::server()->getUserDetail(Auth::id());
        return $this->resultSuccess($userDetail);*/
    }

    /**
     * 职业列表
     * @return array
     */
    public function getProfession() {
        $professionList = UserInfoServer::server()->getProfession();
        return $this->resultSuccess($professionList);
    }

    /**
     * 人的关系
     * @return array
     */
    public function getRelationship() {
        $professionList = UserInfoServer::server()->getRelationship();
        return $this->resultSuccess($professionList);
    }

    /**
     * 行业列表
     * @return array
     */
    public function getIndustry() {
        $professionList = UserInfoServer::server()->getIndustry();
        return $this->resultSuccess($professionList);
    }

    /**
     * 紧急联系人
     *
     * @param UserInfoRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Common\Exceptions\RuleException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createUserContact(UserInfoRule $rule)
    {
        $user = $this->identity();
        /* 手机号过滤( ) - 处理*/
        $data = $this->request->all();
        if ($contactTelephone1 = array_get($data, 'contactTelephone1')) {
            $data['contactTelephone1'] = StringHelper::formatTelephone($contactTelephone1);
        }
        if ($contactTelephone2 = array_get($data, 'contactTelephone2')) {
            $data['contactTelephone2'] = StringHelper::formatTelephone($contactTelephone2);
        }
        $params = $rule->validateE($rule::SCENARIO_CREATE_USER_CONTACT, $data);
        UserInfoServer::server()->createUserContact($params);
        # 发送what's 验证请求
        if(Env::isProd()){
            WhatsappServer::server()->send($data['contactTelephone1']);
            WhatsappServer::server()->send($data['contactTelephone2']);
        }
        return $this->resultSuccess();
    }


}
