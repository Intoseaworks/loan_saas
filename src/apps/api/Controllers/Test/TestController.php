<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Test;

use Common\Response\ApiBaseController;
use Illuminate\Support\Carbon;

class TestController extends ApiBaseController {

    public function test() {
        echo t('操作失败');
    }

}
