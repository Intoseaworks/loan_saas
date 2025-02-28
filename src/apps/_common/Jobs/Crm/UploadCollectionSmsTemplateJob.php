<?php

namespace Common\Jobs\Crm;


use Common\Jobs\Job;
use Common\Models\Crm\CollectionSmsTemplate;

class UploadCollectionSmsTemplateJob extends Job {

    public $queue = 'collection_sms_template';
    public $tries = 3;
    private $_list;
    private $_adminId;
    private $_merchantId;

    public function __construct($merchantId,$res, $adminId) {
        $this->_list = $res;
        $this->_adminId = $adminId;
        $this->_merchantId = $merchantId;
    }

    public function handle() {
        foreach ($this->_list[0] as $i => $item) {
            if ($i > 0) {
                if(!$item[0] && !$item[1] && !$item[2]){
                    continue;
                }
                $model = [
                    "merchant_id" => $this->_merchantId,
                    'collection_level' => $item[0],
                    'tpl_name' => $item[1],
                    'tpl_content' => $item[2],
                    'remark' => $item[3]??null,
                    'admin_id' => $this->_adminId
                ];
                $res = CollectionSmsTemplate::model()->createModel($model);
            }
        }
    }

}
