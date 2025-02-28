<?php

namespace Api\Services\Common;

use Api\Services\BaseService;
use Common\Models\Common\BankInfo;
use Common\Models\Common\Dict;
use Common\Utils\CityHelper;
use Illuminate\Support\Facades\Cache;

class DictServer extends BaseService {

    public function getList($parentCode = '') {
        return Cache::remember("DistServer::getList::{$parentCode}1".date("Ymd"), 60*24, function () use ($parentCode) {
            return $this->getListByArray(["parent_code" => $parentCode]);
        });
    }

    public function getListByArray($where = []){
        $key = md5(json_encode($where));
        return Cache::remember("DistServer::getListByArray::{$key}1".date("YmdHis"), 60*24, function () use ($where) {
            return Dict::model()->getList($where)->toArray();
        });
    }
}
