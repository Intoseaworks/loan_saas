<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-24
 * Time: 15:10
 */

namespace Common\Utils\Code;


use Common\Exceptions\CustomException;
use Common\Traits\GetInstance;
use Common\Utils\Email\EmailHelper;
use Log;
use ReflectionClass;

class ApproveStatusCode
{
    use GetInstance;

    /**
     * @var int
     */
    const SUCCESS = 18000;

    /**
     * @var int
     */
    const FAIL = 13000;

    /**
     * 可以直接给客户端展示的错误信息
     *
     * @var int
     */
    const CODE_SHOW_CUSTOMER = 14000;

    /**
     * 订单未找到
     *
     * @var int
     */
    const ORDER_NOT_FOUND = 1000;

    /**
     * 获取风控数据失败
     *
     * @var int
     */
    const RISK_DATA_FAILED = 2000;

    /**
     * 审批失败
     *
     * @var int
     */
    const APPROVE_FAILED = 3000;

    /**
     * 订单状态无效
     *
     * @var int
     */
    const ORDER_STATUS_INVALID = 4000;

    /**
     * 参数验证失败
     *
     * @var int
     */
    const PARAMS_VERFIY_FAILED = 5000;

    /**
     * 推送到业务库失败
     *
     * @var int
     */
    const PUSH_TO_BUSINESS_FAILED = 6000;

    /**
     * 验证签名失败
     *
     * @var int
     */
    const VERIFY_SIGN_FAILED = 7000;

    /**
     * @param CustomException $exception
     * @return array
     * @throws \ReflectionException
     */
    public function handleException(CustomException $exception)
    {

        $requestParams = json_encode(request()->all());
        Log::info('审批系统', [$exception->getMessage() . "\n params:{$requestParams}"]);

        if (!$this->canShow($exception->getCode())) {
            EmailHelper::send($exception->getMessage() . "\n params:{$requestParams}", '[审批系统] ' . $exception->getMessage());
            return ['code' => $exception->getCode(), 'msg' => 'FAILED'];
        }

        $statusCode = new static();
        $reflection = new ReflectionClass($statusCode);
        $constants = $reflection->getConstants();
        if (in_array($exception->getCode(), $constants)) {
            $msg = ($exception->getCode() == static::CODE_SHOW_CUSTOMER)
                ? $exception->getMessage()
                : $this->getMsg($exception->getCode());
            return ['code' => $exception->getCode(), 'msg' => $msg];
        }

        return ['code' => $exception->getCode(), 'msg' => 'FAILED'];
    }

    /**
     * 可以给客户端展示信息的状态码
     *
     * @param $code
     * @return bool
     */
    public function canShow($code)
    {
        $list = [
            static::SUCCESS,
            static::FAIL,
            static::CODE_SHOW_CUSTOMER,
            static::ORDER_NOT_FOUND,
            static::APPROVE_FAILED,
            static::ORDER_STATUS_INVALID,
            static::PARAMS_VERFIY_FAILED,
        ];

        return in_array($code, $list);
    }

    /**
     * @param $code
     * @return mixed|string
     */
    public function getMsg($code)
    {
        if ($this->canShow($code)) {
            return $this->messageList()[$code] ?? '';
        }

        return '';
    }

    /**
     * @return array
     */
    public function messageList()
    {
        return [
            static::SUCCESS => 'SUCCESS',
            static::FAIL => 'FAILED',
            static::ORDER_NOT_FOUND => t('订单未找到'),
            static::RISK_DATA_FAILED => t('风控接口调用失败'),
            static::APPROVE_FAILED => t('审批失败'),
            static::ORDER_STATUS_INVALID => t('订单状态错误'),
            static::PARAMS_VERFIY_FAILED => t('参数错误'),
        ];
    }

    /**
     * 如果返回这些状态码审批单需要完结
     *
     * @return array
     */
    public function needCompleteOrder()
    {
        return [
            static::ORDER_NOT_FOUND,
            static::ORDER_STATUS_INVALID,
        ];
    }
}
