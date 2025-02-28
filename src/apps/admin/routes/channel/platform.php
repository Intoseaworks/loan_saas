<?php

use Laravel\Lumen\Routing\Router;

/**
 * 平台列表
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Channel',
    'path' => 'cooperation-platform.platform',
], function (Router $router) {
    //渠道列表
    $router->get('channel/index', 'ChannelController@index');
    //渠道详情
    $router->get('channel/view', 'ChannelController@view');
    //添加渠道
    $router->post('channel/create', 'ChannelController@create');
    //更新渠道
    $router->post('channel/update', 'ChannelController@update');
    //删除渠道
    $router->get('channel/del', 'ChannelController@del');
    //渠道验证
    $router->get('channel/check_code', 'ChannelController@checkCode');
    //修改渠道状态
    $router->get('channel/update_status', 'ChannelController@updateStatus');
    //置顶渠道
    $router->post('channel/update_top', 'ChannelController@updateTop');
});
