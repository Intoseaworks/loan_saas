<?php
/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 下午3:59
 */

use Laravel\Lumen\Routing\Router;

/**
 * 续期
 *
 * @var Router $router
 */

//续期 - 管理
$router->post('/repayment/renewal-pre-list', \Admin\Controllers\Repayment\RenewalRepaymentController::class . '@renewalPreList');
//申请续期
$router->post('/repayment/apply-renewal', \Admin\Controllers\Repayment\RenewalRepaymentController::class . '@applyRenewal');
//续期列表
$router->post('/repayment/renewal-list', \Admin\Controllers\Repayment\RenewalRepaymentController::class . '@renewalList');
//todo delete 测试
$router->post('/repayment/renewal-test', \Admin\Controllers\Repayment\RenewalRepaymentController::class . '@renewalTest');
//todo delete 测试
$router->post('/repayment/renewal-trial', \Admin\Controllers\Repayment\RenewalRepaymentController::class . '@renewalTrial');
