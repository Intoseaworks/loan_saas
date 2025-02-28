<?php
namespace Common\Libraries\PayChannel\_common;
/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-10-31
 * Time: 下午4:41
 */
interface BasisInterface
{
    /**
     * 查询余额
     * @return mixed
     */
    public function queryBalance();

    /**
     * 单笔收款
     */
    public function singlePayment();

    /**
     * 单笔出款
     */
    public function singlePayout();

    /**
     * 交易成功
     */
    public function tradeFailed();

    /**
     * 交易失败
     */
    public function tradeSuccess();

    /**
     * 查询订单
     */
    public function queryOrder();
}