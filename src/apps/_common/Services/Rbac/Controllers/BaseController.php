<?php

namespace Common\Services\Rbac\Controllers;

use Common\Response\AdminBaseController;
use Exception;

class BaseController extends AdminBaseController
{

    /**
     * 错误输出
     *
     * @param Exception $exception
     * @return array
     * @throws Exception
     */
    protected function errorOutput(Exception $exception)
    {
        if ($exception->getCode() == 500) {
            return $this->resultFail($exception->getMessage());
        }
        throw $exception;
    }

}
