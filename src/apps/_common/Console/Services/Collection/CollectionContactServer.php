<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Console\Services\Collection;

use Common\Console\Models\Collection\CollectionContact;
use Common\Services\BaseService;

class CollectionContactServer extends BaseService
{
    /**
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $data = CollectionContact::model()->getList($param);
        return $data;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOne($id)
    {
        return CollectionContact::model()->getOne($id);
    }
}
