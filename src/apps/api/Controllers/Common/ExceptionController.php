<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/14
 * Time: 14:56
 */

namespace Api\Controllers\Common;


use Common\Response\ApiBaseController;
use Common\Utils\DingDing\DingHelper;

class ExceptionController extends ApiBaseController
{
    /**
     * 终端异常上报
     */
    public function report()
    {
        $content = $this->getParam('content');
        $title = '终端异常上报|' . $this->appVersion . '|' . app()->environment();
        if (DingHelper::notice($content, $title, null, true, DingHelper::ROBOT_APP_EXCEPTION)) {
            $this->resultSuccess([], '终端异常上报成功');
        }
        $this->resultFail('终端异常上报失败');
    }
}
