<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Config;

use Admin\Services\BaseService;
use Common\Models\Config\Banner;
use Common\Utils\LoginHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Upload\ImageHelper;

class BannerServer extends BaseService {

    public function upload($params) {
        $params['merchant_id'] = MerchantHelper::helper()->getMerchantId();
        $params['admin_id'] = LoginHelper::helper()->getAdminId();
        $res = Banner::model()->createModel($params);
        if ($res) {
            return $this->outputSuccess();
        } else {
            return $this->outputError('Fail to save');
        }
    }

    public function index() {
        $res = Banner::model()->where("is_delete", "1")->orderByDesc("id")->get()->toArray();

        foreach ($res as &$item) {
            $item['file_url'] = ImageHelper::getPicUrl($item['file_path'], 400);
        }
        return $res;
    }

    public function start($id) {
        return Banner::model()->where("id", $id)->update(["status" => 1]);
    }

    public function stop($id) {
        return Banner::model()->where("id", $id)->update(["status" => 2]);
    }

    public function remove($id) {
        return Banner::model()->where("id", $id)->update(["is_delete" => 2]);
    }

}
