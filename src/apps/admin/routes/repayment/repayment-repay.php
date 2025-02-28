<?php
/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-11-6
 * Time: 下午3:59
 */

use Laravel\Lumen\Routing\Router;

/**
 * 调账管理
 *
 * @var Router $router
 */

//调账管理列表
$router->post('/repayment/repay-list', \Admin\Controllers\Repayment\AdjustmentRepayController::class . '@index');
//撤销
$router->post('/repayment/repay-revoke', \Admin\Controllers\Repayment\AdjustmentRepayController::class . '@revoke');
//历史调账记录
$router->post('/repayment/repay-adjustment-list', \Admin\Controllers\Repayment\AdjustmentRepayController::class . '@adjustmentList');
//调账并结清
$router->post('/repayment/repay-adjustment-complete', \Admin\Controllers\Repayment\AdjustmentRepayController::class . '@complete');
//调账
$router->post('/repayment/repay-adjustment-only', \Admin\Controllers\Repayment\AdjustmentRepayController::class . '@only');
