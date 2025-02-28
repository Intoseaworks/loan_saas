<?php
namespace Common\Services\Partner;

use JMD\JMD;
use JMD\Libs\Services\BaseRequest;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 19-3-25
 * Time: 下午9:11
 */
class PartnerBalanceServer extends PartnerServer
{
    /**
     * 查询余额
     * @return mixed
     */
    public function queryBalance()
    {
        return $this->execute(self::QUERY_BALANCE_URL, []);
    }
}
