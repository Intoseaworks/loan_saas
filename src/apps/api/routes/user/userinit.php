<?php

use Api\Controllers\User\UserInfoController;
use Api\Controllers\User\UserInitController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */
$router->group([],
        function (Router $router) {
    $router->post('user-init/step-one', UserInitController::class . '@stepOne');
    $router->post('user-init/step-two', UserInitController::class . '@stepTwo');
    $router->post('user-init/step-two-new', UserInitController::class . '@stepTwoNew');
    $router->post('user-init/step-three', UserInitController::class . '@stepThree');
    $router->post('user-init/step-four', UserInfoController::class . '@createUserContact');
    $router->post('user-init/index', UserInitController::class . '@initIndex');
    $router->post('user-init/last-loan', UserInitController::class . '@lastLoan');
    $router->post('user-init/trial', UserInitController::class . '@trial');
    $router->post('user-init/detention', UserInitController::class . '@getQuestion');
    $router->post('user-init/surveys-submit', UserInitController::class . '@submitQuestion');
    $router->post('user/replenish-init', UserInitController::class . '@replenishInit');
    $router->post('user-init/get-step', UserInitController::class . '@getStep');
    $router->post('payment/create', UserInitController::class . '@createPayment');

    $router->post('user-init/sample', UserInitController::class . '@sample');
}
);
