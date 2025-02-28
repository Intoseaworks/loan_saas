<?php

namespace Api\Controllers\Cloud;

use Common\Response\ServicesApiBaseController;

class DataController extends ServicesApiBaseController {

    public function get() {
        $params = $this->getParams();
        $res = $this->sign($params);
        if (is_numeric($res)) {
            if (isset($params['model']) && (isset($params['uid']) || isset($params['oid']))) {
                $table = $params['model'];
                $where = " 1 ";
                if (isset($params['uid'])) {
                    $where .= " AND user_id='{$params['uid']}' ";
                }
                if (isset($params['oid'])) {
                    $where .= " AND user_id='{$params['oid']}' ";
                }
                $res = \DB::select("select * from `{$table}` where {$where} ORDER BY id");
                $res = $this->resultSuccess($res);
            }
        }
        return $res;
    }

}
