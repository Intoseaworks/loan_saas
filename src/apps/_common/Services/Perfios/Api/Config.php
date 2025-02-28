<?php

namespace Common\Services\Perfios\Api;

class Config
{
    /**
     *
     * @var string
     */
    const GENERATE_LINK = '/KuberaVault/insights/api/transaction/request';

    /**
     * 开始构建事务(表单)
     * @var string
     */
    const START_PROCESS = '/KuberaVault/insights/start';

    /**
     * 查看事务状态
     * @var string
     */
    const TXN_STATUS = '/KuberaVault/insights/txnstatus';

    /**
     * 获取报告
     * @var string
     */
    const RETRIEVE = '/KuberaVault/insights/retrieve';

    /**
     * @return mixed
     */
    public static function getVendorId()
    {
        return 'cashNow';
        return config('perfios.vendorId');
    }

    /**
     * @return mixed
     */
    public static function getEmail()
    {
        return 'online@perfios.com';
        return config('perfios.email');
    }

    /**
     * @return string
     */
    public static function getGenerateLinkUrl()
    {
        return 'https://www.perfios.com' . static::GENERATE_LINK;
        return config('perfios.baseUrl') . static::GENERATE_LINK;
    }

    /**
     * @return string
     */
    public static function getStartProcessUrl()
    {
        return 'https://www.perfios.com' . static::START_PROCESS;
        return config('perfios.baseUrl') . static::START_PROCESS;
    }

    public static function getTxnStatusUrl()
    {
        return 'https://www.perfios.com' . static::TXN_STATUS;
        return config('perfios.baseUrl') . static::TXN_STATUS;
    }

    public static function getRetrieveUrl()
    {
        return 'https://www.perfios.com' . static::RETRIEVE;
        return config('perfios.baseUrl') . static::RETRIEVE;
    }

    /**
     * 获取回调地址
     * @return mixed
     */
    public static function getCallbackUrl()
    {
        return 'test.com';
        return config('perfios.callbackUrl');
    }
}
