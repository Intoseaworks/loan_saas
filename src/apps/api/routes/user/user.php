<?php

use Laravel\Lumen\Routing\Router;

/**
 * 用户模块
 * @var Router $router
 */

$router->group([
    'namespace' => 'Api\Controllers\User',
],
    function (Router $router) {
        /** 首页接口 */
        $router->get('user/home', 'UserController@home');
        /** 个人信息 */
        $router->get('user-info/show', 'UserInfoController@show');
        $router->post('user-info/create', 'UserInfoController@create');
        // 获取银行卡信息&判断能否绑卡
        $router->get('user-info/bankcard-info', 'UserInfoController@bankCardInfo');
        // 废弃银行卡
        $router->post('user-info/bankcard-info', 'UserInfoController@bankCardInfo');

        $router->get('user/identity', 'UserController@userIdentity');

        $router->get('/user/risk-product', 'UserController@riskProduct');
        $router->get('/user/invite-code', 'UserInviteController@inviteCode');
        $router->get('/user/invite-friends-cashback', 'UserInviteController@inviteFriendsCashbackConfig');
        $router->post('/user/invite-friends-cashback', 'UserInviteController@inviteFriendsCashback');
        $router->get('/user/bonus-activity', 'UserInviteController@bonusActivity');
        $router->get('/user/bonus-add', 'UserInviteController@bonusAdd');
        $router->get('/user/invited-users', 'UserInviteController@invitedUser');
    });

