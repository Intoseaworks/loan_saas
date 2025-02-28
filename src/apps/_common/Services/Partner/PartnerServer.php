<?php

namespace Common\Services\Partner;

use Common\Services\BaseService;
use JMD\JMD;
use JMD\Libs\Services\BaseRequest;

class PartnerServer extends BaseService
{
    /** 充值记录 URL */
    const RECHARGE_LIST_URL = 'app/partner-recharge/list';

    /** 充值申请 URL */
    const RECHARGE_APPLY_URL = 'app/partner-recharge/apply';

    /** 查询余额 URL */
    const QUERY_BALANCE_URL = 'app/partner-balance/query';

    /** 商户详情 */
    const ACCOUNT_PARTNER_DETAIL = 'app/account/partner/detail';
    /** 商户消费记录统计列表url */
    const ACCOUNT_CONSUME_LIST = 'app/account/consume/list';
    /** 商户消费记录明细列表url */
    const ACCOUNT_CONSUME_LOG_LIST = 'app/account/consume-log/list';
    /** 商户账户每日统计列表url */
    const ACCOUNT_STATISTICS_LIST = 'app/account/account-statistics/list';

    const RESULT_SUCCESS_CODE = 18000;
    const RESULT_FAIL_CODE = 18000;

    /** 充值状态：待审核 */
    const RECHARGE_STATUS_WAIT_VERIFY = 1;
    /** 充值状态：充值成功 */
    const RECHARGE_STATUS_SUCCESS = 2;
    /** 充值状态：充值失败 */
    const RECHARGE_STATUS_FAIL = 3;
    /**  充值状态筛选框 */
    const RECHARGE_STATUS = [
        self::RECHARGE_STATUS_WAIT_VERIFY => '待审核',
        self::RECHARGE_STATUS_SUCCESS => '充值成功',
        self::RECHARGE_STATUS_FAIL => '充值失败',
    ];

    /**
     * @param $url
     * @param array $params
     * @param bool $isPost
     * @param bool $export
     * @param $filename
     * @return \JMD\Libs\Services\DataFormat
     * @throws \Exception
     */
    protected function execute($url, array $params, $isPost = true, $export = false, $filename = null)
    {

        array_forget($params, 'token');
        JMD::init(['projectType' => 'lumen']);
        $request = new BaseRequest();

//        //TODO 临时测试
//        $endpoint = 'http://saas-master-wangsl.dev23.jiumiaodai.com/';
//        $endpoint = 'http://saas-master-wangsl.dev23.jiumiaodai.com/';
//        $request->setAppKey('u2bq645KT03x0e');
//        $request->setSecretKey('8d784093a53445a391bec96271958111');
//        $endpoint = 'http://zy-saas-master.dev23.jiumiaodai.com/';

        $endpoint = config('saas-master.outer_domain');
        $request->setEndpoint($endpoint);
        $request->setUrl($url);
        $request->setData($params);

        return $request->execute($isPost, $export, $filename);
    }
}
