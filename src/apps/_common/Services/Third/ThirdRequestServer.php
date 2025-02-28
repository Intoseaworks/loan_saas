<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/11/18
 * Time: 20:07
 */

namespace Common\Services\Third;


use Common\Models\Third\ThirdPartyLog;
use Common\Services\BaseService;
use Common\Utils\Services\AuthRequestHelper;
use JMD\Libs\Services\DataFormat;

class ThirdRequestServer extends BaseService
{
    public function requestDataCheck($request, ThirdPartyLog $thirdPartLogModel, $authFailMsg = '')
    {
        if (!$request) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL);
            return $this->outputException('Error, please try again');
        }
        /** @var DataFormat $request */
        $response = $request->getAll();
        $data = $request->getData();
        $msg = $request->getMsg();
        $reportId = array_get($data, 'report_id', '');
        // 响应结果保存到日志
        $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $response, $reportId);
        //认证不成功
        if ($msg != AuthRequestHelper::AUTH_SUCCESS) {
            $thirdPartLogModel->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL);
            return $this->outputException($authFailMsg ?: 'The status is not active,please check again.');
        }
    }
}
