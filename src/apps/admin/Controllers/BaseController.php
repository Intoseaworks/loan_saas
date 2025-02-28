<?php

namespace Admin\Controllers;

use Common\Response\AdminBaseController;

class BaseController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        if(isset($_COOKIE['umi_locale'])){
            app('translator')->setLocale($_COOKIE['umi_locale']);
        }
    }
    protected function required($checkItem){
        $params = $this->getParams();
        foreach($checkItem as $key){
            if(!isset($params[$key]) ){
                return false;
            }
        }
        return true;
    }
}
