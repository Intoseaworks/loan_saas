<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27
 * Time: 14:03
 */

namespace Admin\Controllers\Export;

use Admin\Controllers\BaseController;
use Admin\Services\Export\ExportServer;

class ExportController extends BaseController
{
    public function notAuthUser()
    {
        return $this->resultSuccess(ExportServer::server()->notAuthUser());
    }

    public function notSignOrder()
    {
        return $this->resultSuccess(ExportServer::server()->notSignOrder());
    }

}
