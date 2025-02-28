<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Export;

use Admin\Exports\Order\OrderExport;
use Admin\Exports\User\UserExport;
use Admin\Models\Feedback\Feedback;
use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Admin\Models\User\UserInfo;
use Admin\Services\BaseService;
use Admin\Services\Data\DataServer;
use Common\Utils\Data\DateHelper;

class ExportServer extends BaseService
{

    /**
     * 获取用户列表
     * @param $param
     * @return mixed
     */
    public function notAuthUser()
    {
        $params = [
            'register_time_start' => DateHelper::subDays(1),
            'register_time_end' => DateHelper::date(),
            'fullname' => ''
        ];
        $query = User::model()->search($params);
        UserExport::getInstance()->export($query, UserExport::SCENE_SUPER_NOT_AUTH_USER);
    }

    public function notSignOrder()
    {
        $params = [
            'pass_time' => [DateHelper::subDays(1), DateHelper::date()],
            'status' => [Order::STATUS_MANUAL_PASS, Order::STATUS_SYSTEM_PASS]
        ];
        $query = Order::model()->search($params);
        OrderExport::getInstance()->export($query, OrderExport::SCENE_SUPER_NOT_SIGN_ORDER);
    }

}
