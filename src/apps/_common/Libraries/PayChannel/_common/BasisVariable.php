<?php
/**
 * Created by PhpStorm.
 * User: zy
 * Date: 20-10-23
 * Time: 下午2:04
 */

namespace Common\Libraries\PayChannel\_common;

trait BasisVariable
{
    /**
     * 定义结果 - 成功, 失败, 处理中 (不明确的定义为处理中)
     * 默认处理中
     * @var string $result SUCCESS , FAIL, PROCESSING
     */
    public $result = 'PROCESSING';

    /**
     * 定义输出信息 - 第三发返回的具体信息
     * @var string $message ''
     */
    public $message = '';

    /**
     * @var array 请求参数
     */
    public $requestParams = [];

    /**
     * @var array 返回原文
     */
    public $returnOriginal = [];

    /**
     * @var string 第三方订单号
     */
    public $orderId = '';

    /**
     * @var string 第三方交易号
     */
    public $transactionId = '';

    /**
     *  写入日志 - 文件
     */
    private function _log()
    {

    }


}