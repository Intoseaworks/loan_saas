<?php

use Api\Controllers\User\UserInfoController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([],
    function (Router $router) {
        $router->post('user-info/work-eamil-valid', UserInfoController::class . '@workEmailValid');
        $router->post('user-info/create-user-work', UserInfoController::class . '@createUserWork');
        $router->post('user-info/create-user-info', UserInfoController::class . '@createUserInfo');
        $router->post('user-info/create-base-user-info', UserInfoController::class . '@createBaseUserInfo');
        $router->get('user-info/get-user-info', UserInfoController::class . '@getUserInfo');
        $router->get('user-info/get-user-detail', UserInfoController::class . '@getUserDetail');
        $router->get('user-info/get-profession', UserInfoController::class . '@getProfession');
        $router->get('user-info/get-relationship', UserInfoController::class . '@getRelationship');
        $router->get('user-info/get-industry', UserInfoController::class . '@getIndustry');
        $router->post('user-info/create-user-contact', UserInfoController::class . '@createUserContact');
        //$router->post('user-info/create-user-intention', UserInfoController::class . '@createUserIntention');
    }
);

