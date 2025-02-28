<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-08
 * Time: 16:18
 */

namespace Approve\Admin\Controllers;


use Approve\Admin\Controllers\Controller;
use Approve\Admin\Rules\ApproveQualityRule;
use Approve\Admin\Services\Quality\ApproveQualityService;

class ApproveQualityController extends ApproveBaseController
{

    /**
     * ApproveQualityController constructor.
     * @param ApproveQualityRule $rule
     * @param ApproveQualityService $services
     */
    public function __construct(ApproveQualityRule $rule, ApproveQualityService $services)
    {
        parent::__construct();
        $this->rule = $rule;
        $this->server = $services;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function index()
    {
        try {

            if (!$this->rule->validate(ApproveQualityRule::SENARIO_INDEX, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->getList($this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     */
    public function approveResultList()
    {
        return $this->resultSuccess($this->server->approveResultList());
    }

    /**
     * @return array
     */
    public function qualityStatusList()
    {
        return $this->resultSuccess($this->server->qualityStatusList());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function show()
    {
        try {
            if (!$this->rule->validate(ApproveQualityRule::SENARIO_DETAIL, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $qualityId = $this->params['id'];
            return $this->resultSuccess($this->server->detail($qualityId));

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     */
    public function qualityResultList()
    {
        return $this->resultSuccess($this->server->qualityResultList());
    }

    /**
     * 质检审批提交
     *
     * @return array
     * @throws \Exception
     */
    public function qualitySubmit()
    {
        try {
            if (!$this->rule->validate(ApproveQualityRule::SENARIO_QUALITY_SUBMIT, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $this->server->qualitySubmit($this->params);
            return $this->resultSuccess();

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

}
