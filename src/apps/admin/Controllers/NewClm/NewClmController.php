<?php

namespace Admin\Controllers\NewClm;

use Admin\Controllers\BaseController;
use Admin\Rules\NewClm\ClmConfigRule;
use Common\Services\NewClm\ClmConfigServer;
use Illuminate\Support\Arr;

class NewClmController extends BaseController
{
    /**
     * 获取等级金额配置
     *
     * @return array
     */
    public function getClmAmountConfig(): array
    {
        return $this->resultSuccess(ClmConfigServer::server()->getClmAmountConfig());
    }

    /**
     * 新增等级金额配置
     *
     * @param ClmConfigRule $rule
     *
     * @return array
     */
    public function addClmAmountConfig(ClmConfigRule $rule): array
    {
        $param = $this->request->all();
        if (!$rule->validate(ClmConfigRule::SCENARIO_ADD_CLM_AMOUNT_CONFIG, $param)) {
            return $this->resultFail($rule->getError());
        }

        $server = ClmConfigServer::server()->addClmAmountConfig($param);
        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData());
    }

    /**
     * 编辑等级金额配置
     *
     * @param ClmConfigRule $rule
     *
     * @return array
     */
    public function editClmAmountConfig(ClmConfigRule $rule): array
    {
        $param = $this->request->all();
        if (!$rule->validate(ClmConfigRule::SCENARIO_EDIT_CLM_AMOUNT_CONFIG, $param)) {
            return $this->resultFail($rule->getError());
        }

        $server = ClmConfigServer::server()->editClmAmountConfig($param['id'], Arr::except($param, 'id'));
        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess($server->getData());
    }

    /**
     * 删除等级金额配置
     *
     * @return array
     * @throws \Exception
     */
    public function delClmAmountConfig(): array
    {
        $params = $this->getParams();

        if (!isset($params['id'])) {
            return $this->resultFail('参数不正确');
        }

        $server = ClmConfigServer::server()->delClmAmountConfig((int)$params['id']);
        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess();
    }

    /**
     * 获取初始化等级配置
     *
     * @return array
     */
    public function getInitLevelConfig(): array
    {
        return $this->resultSuccess(ClmConfigServer::server()->getInitLevelConfig());
    }

    /**
     * 修改初始化等级配置
     *
     * @return array
     */
    public function edieInitLevelConfig(): array
    {
        $params = $this->getParams();

        if (!isset($params['id']) || !isset($params['level'])) {
            return $this->resultFail('参数不正确');
        }

        $server = ClmConfigServer::server()->editInitLevelConfig($params['id'], $params['level']);
        if ($server->isError()) {
            return $this->resultFail($server->getMsg());
        }

        return $this->resultSuccess();
    }
}
