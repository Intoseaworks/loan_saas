<?php
namespace Common\Services\Partner;

/**
 * Created by PhpStorm.
 * User: zy
 * Date: 19-3-25
 * Time: 下午9:11
 */
class PartnerRechargeServer extends PartnerServer
{

    /** 充值记录 URL */
    const RECHARGE_LIST_URL = 'app/partner-recharge/list';

    /** 充值申请 URL */
    const RECHARGE_APPLY_URL = 'app/partner-recharge/apply';


    /**
     * 充值记录
     * @param $params
     * @return mixed
     */
    public function rechargeList($params)
    {
        return $this->execute(self::RECHARGE_LIST_URL, $params, true, $this->getExport());
    }

    /**
     * 充值申请
     * @param $params
     * @return mixed
     */
    public function rechargeApply($params)
    {
        return $this->execute(self::RECHARGE_APPLY_URL, $params);
    }
}
