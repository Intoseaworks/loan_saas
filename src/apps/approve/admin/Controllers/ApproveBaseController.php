<?php


namespace Approve\Admin\Controllers;


use Common\Exceptions\CustomException;
use Common\Response\BaseController;
use Common\Utils\Code\ApproveStatusCode;
use Common\Utils\LoginHelper;
use Exception;

class ApproveBaseController extends BaseController
{
    public $userId = null;

    public function __construct()
    {
        parent::__construct();
        $this->userId = LoginHelper::getAdminId();
    }

    /**
     * @param Exception $exception
     * @return array
     * @throws Exception
     */
    protected function handleException(Exception $exception)
    {
        if ($exception instanceof CustomException) {
            $msg = ApproveStatusCode::getInstance()->handleException($exception);
            return $this->resultFail($msg['msg']);
        }

        throw $exception;
    }

    /**
     * 接口统一输出
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function result($code = 18000, $msg = '', $data = [])
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
