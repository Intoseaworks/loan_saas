<?php

namespace Common\Utils;

use Admin\Models\Staff\Staff;
use Illuminate\Http\Request;


class LoginHelper
{
    use Helper;
    /**
     * @var null|Staff
     */
    public static $modelUser = null;

    public static function getAdminId()
    {
        return self::$modelUser['id'] ?? null;
    }

    /**
     * @return array|Request|string
     */
    public function getTicket()
    {
        $ticket = @$_COOKIE['ticket_saas'];
        return $ticket;
    }
    
    public static function isSuper(){
        return self::$modelUser['is_super'] ?? null == Staff::IS_SUPER_YES;
    }
}
