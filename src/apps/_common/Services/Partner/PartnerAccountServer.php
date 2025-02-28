<?php

namespace Common\Services\Partner;

use Admin\Services\Config\ConfigServer;
use Common\Models\Config\Config;

class PartnerAccountServer extends PartnerServer
{
    /**
     * 商户详情
     * @return \JMD\Libs\Services\DataFormat
     * @throws \Exception
     */
    public function partnerDetail()
    {
        $info = Config::model()->getPartnerAccountInfo();
        $info['partner_account_company_name_edit'] = empty($info['partner_account_company_name']);
        $info['partner_account_app_name_edit'] = empty($info['partner_account_app_name']);
        return $info;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function partnerUpdate($data)
    {
        $data = array_only($data, Config::CONFIG_PARTNER_ACCOUNT_INFO);
        $info = Config::model()->getPartnerAccountInfo();
        if (!empty($info['partner_account_company_name'])) unset($data['partner_account_company_name']);
        if (!empty($info['partner_account_app_name'])) unset($data['partner_account_app_name']);
        return ConfigServer::server()->storeConfig($data)->isSuccess();
    }

    /**
     * 消费记录统计列表
     * @param $params
     * @return \JMD\Libs\Services\DataFormat
     * @throws \Exception
     */
    public function consumeList($params)
    {
        $fileName = '消费统计_' . date('YmdHis');
        return $this->execute(self::ACCOUNT_CONSUME_LIST, $params, true, $this->getExport(), $fileName);
    }

    /**
     * 消费记录明细列表
     * @param $params
     * @return \JMD\Libs\Services\DataFormat
     * @throws \Exception
     */
    public function consumeLogList($params)
    {
        $fileName = '消费记录明细_' . date('YmdHis');
        return $this->execute(self::ACCOUNT_CONSUME_LOG_LIST, $params, true, $this->getExport(), $fileName);
    }

    /**
     * 商户每日统计列表
     * @param $params
     * @return \JMD\Libs\Services\DataFormat
     * @throws \Exception
     */
    public function accountStatisticsList($params)
    {
        $fileName = '商户每日统计_' . date('YmdHis');
        return $this->execute(self::ACCOUNT_STATISTICS_LIST, $params, true, $this->getExport(), $fileName);
    }
}
