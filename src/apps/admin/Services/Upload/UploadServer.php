<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Upload;

use Admin\Models\Upload\Upload;

class UploadServer extends \Common\Services\Upload\UploadServer
{
    /**
     * 根据 SourceId 与 Type 获取path 数组
     *
     * @param $sourceId
     * @param $type
     *
     * @return mixed
     */
    public static function getPathsBySourceIdAndType($sourceId, $type)
    {
        return Upload::model()->getPathsBySourceIdAndType($sourceId, $type);
    }

    public static function hasBySourceIdAndType($sourceId, $type)
    {
        return Upload::model()->hasBySourceIdAndType($sourceId, $type);
    }

    /**
     * @param $attributes
     * @param int $userId
     * @return bool|mixed
     */
    public static function create($attributes, $userId = 0)
    {
        $attributes['user_id'] = $userId;
        return Upload::model('create')->saveModel($attributes);
    }

}
