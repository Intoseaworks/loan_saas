<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-01-18
 * Time: 10:20
 */

namespace Approve\Admin\Controllers;


use Approve\Admin\Controllers\Controller;
use Approve\Admin\Rules\ApproveStatisticRule;
use Approve\Admin\Services\Statistic\ApproveStatisticService;

class ApproveStatisticController extends ApproveBaseController
{

    /**
     * ApproveStatisticController constructor.
     * @param ApproveStatisticRule $rule
     * @param ApproveStatisticService $services
     */
    public function __construct(ApproveStatisticRule $rule, ApproveStatisticService $services)
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

            if (!$this->rule->validate(ApproveStatisticRule::SENARIO_INDEX, $this->params)) {
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
     * @throws \Exception
     */
    public function indexAudit()
    {
        try {

            if (!$this->rule->validate(ApproveStatisticRule::SENARIO_INDEX, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->getAuditList($this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function show()
    {
        try {

            if (!$this->rule->validate(ApproveStatisticRule::SENARIO_DETAIL, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->detail($this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function userStatisticSummary()
    {
        try {

            if (!$this->rule->validate(ApproveStatisticRule::SENARIO_USER_SUMMARY, $this->params)) {
                return $this->resultFail($this->rule->getError());
            }

            $data = $this->server->userStatisticSummary($this->params);
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function approveTypeList()
    {
        try {

            $data = $this->server->getApproveTypeList();
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function approveUserList()
    {
        try {

            $data = $this->server->getApproveUserList();
            return $this->resultSuccess($data);

        } catch (\Exception $exception) {
            return $this->handleException($exception);
        }
    }


}
