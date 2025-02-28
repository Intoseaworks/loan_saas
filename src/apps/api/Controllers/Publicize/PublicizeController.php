<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/3
 * Time: 16:47
 */

namespace Api\Controllers\Publicize;

use Common\Models\Config\Banner;
use Common\Response\ApiBaseController;
use Common\Utils\MerchantHelper;
use Common\Utils\Upload\ImageHelper;

class PublicizeController extends ApiBaseController {

    public function view() {
        $merchantId = MerchantHelper::helper()->getMerchantId();
        $data = Banner::model()
                ->where("merchant_id", $merchantId)
                ->where("status", Banner::STATUS_NORMAL)
                ->where("is_delete", Banner::IS_DELETE_FALSE)->orderBy("id")->get();
        foreach($data as $item){
            $item['pic_url'] = ImageHelper::getPicUrl($item['file_path'], 400);
        }
        return $this->resultSuccess($data);
    }

}
