<?php

use Api\Controllers\Repay\RepayController;
use Laravel\Lumen\Routing\Router;

/**
 * @var Router $router
 */

$router->group([],
        function (Router $router) {
    //还款方式列表
    $router->get('repay/mode', RepayController::class . '@mode');
    //获取第三方还款链接
    $router->post('repay/repay', RepayController::class . '@repay');
    //获取app还款参数
    $router->post('repay/app-repay', RepayController::class . '@appRepay');
    //代扣
    $router->get('repay/daikou', RepayController::class . '@daikou');
    //查询交易
    $router->get('repay/query-trade', RepayController::class . '@queryTrade');
    //尾期减免
    $router->post('repay/deduction', RepayController::class . '@deduction');

    //还款银行
    $router->post('repay/repay-list', RepayController::class . '@repayList');
    //还款列表
    $router->post('repay/online-check', RepayController::class . '@checkRepay');
    
    #2022
    # 添加还款银行卡
    $router->post('repay/add-bank', RepayController::class . '@addRepayBank');
    # 删除还款银行卡
    $router->post('repay/remove-bank', RepayController::class . '@removeRepayBank');
    # 获取还款银行卡
    $router->post('repay/get-bank', RepayController::class . '@getRepayBankList');
    # 使用还款银行卡
    $router->post('repay/use-bank', RepayController::class . '@useBank');
    
}
);
