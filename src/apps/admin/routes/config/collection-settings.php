<?php

use Laravel\Lumen\Routing\Router;

/**
 * 催收设置
 *
 * @var Router $router
 */

$router->group([
    'namespace' => 'Admin\Controllers\Collection',
    'path' => 'system-settings.collection-settings',
], function (Router $router) {
    //催收设置详情
    $router->get('collection_setting/view', 'CollectionSettingController@view');
    //添加催收设置
    $router->post('collection_setting/create', 'CollectionSettingController@create');
    $router->post('collection_setting/rule', 'CollectionSettingController@rule');
    $router->post('collection_setting/reallocation', 'CollectionSettingController@reallocation');
    $router->post('collection_setting/admin-list', 'CollectionSettingController@adminList');
    $router->post('collection_setting/update-weight', 'CollectionSettingController@updateWeight');
    $router->post('collection_setting/remove-group', 'CollectionSettingController@removeFromGroup');
    $router->get('collection_setting/email-list', 'CollectionSettingController@emailList');
});
