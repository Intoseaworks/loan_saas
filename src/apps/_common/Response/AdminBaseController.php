<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-13
 * Time: 20:26
 */

namespace Common\Response;


use Common\Traits\Response\AdminSend;

class AdminBaseController extends BaseController
{
    use AdminSend;
}
