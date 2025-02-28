<?php

namespace Admin\Controllers\Partner;

use Admin\Controllers\BaseController;
use Admin\Rules\Partner\PartnerAccountRule;
use Common\Services\Partner\PartnerAccountServer;

class PartnerAccountController extends BaseController
{
    /**
     * 查看商户详情
     *
     * @return array
     * @throws \Exception
     */
    public function partnerDetail(PartnerAccountRule $rule)
    {
        if ($this->request->isMethod('get')) {
            // 获取信息
            $result = PartnerAccountServer::server()->partnerDetail();
            return $this->resultSuccess($result);

        } else {
            // 更新信息
            $params = $this->request->all();
            if (!$rule->validate($rule::SCENARIO_ACCOUNT_UPDATE, $params)) {
                return $this->resultFail($rule->getError());
            }

            $result = PartnerAccountServer::server()->partnerUpdate($params);
            if ($result) {
                return $this->resultSuccess([], '更新成功');
            }

            return $this->resultFail('更新失败');
        }
    }

    /**
     * 消费记录统计列表
     * @param PartnerAccountRule $rule
     * @return array
     * @throws \Exception
     */
    public function consumeList(PartnerAccountRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_CONSUME_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $result = PartnerAccountServer::server()->consumeList($params);
        if (!$result->isSuccess()) {
            return $this->resultFail($result->getMsg());
        }
        return $this->resultSuccess($result->getData());
    }

    /**
     * 消费记录明细列表
     * @param PartnerAccountRule $rule
     * @return array
     * @throws \Exception
     */
    public function consumeLogList(PartnerAccountRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_CONSUME_LOG_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $result = PartnerAccountServer::server()->consumeLogList($params);
        if (!$result->isSuccess()) {
            return $this->resultFail($result->getMsg());
        }
        return $this->resultSuccess($result->getData());
    }

    /**
     * 商户每日统计列表
     * @param PartnerAccountRule $rule
     * @return array
     * @throws \Exception
     */
    public function accountStatisticsList(PartnerAccountRule $rule)
    {
        $params = $this->request->all();
        if (!$rule->validate($rule::SCENARIO_ACCOUNT_STATISTICS_LIST, $params)) {
            return $this->resultFail($rule->getError());
        }
        $result = PartnerAccountServer::server()->accountStatisticsList($params);
        if (!$result->isSuccess()) {
            return $this->resultFail($result->getMsg());
        }
        return $this->resultSuccess($result->getData());
    }
}
