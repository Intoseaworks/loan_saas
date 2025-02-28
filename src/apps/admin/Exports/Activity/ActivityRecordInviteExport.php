<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Activity;

use Admin\Models\Channel\Channel;
use Admin\Models\Channel\ChannelCount;
use Admin\Models\User\User;
use Admin\Services\Channel\ChannelServer;
use Admin\Services\Order\OrderServer;
use Admin\Services\User\UserServer;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class ActivityRecordInviteExport extends AbstractExport {

    /**
     * 平台列表
     */
    const SCENE_EXPORT = 'SCENE_EXPORT';

    /**
     * ChannelExport constructor.
     * @param array $params
     */
    public function __construct(array $params = []) {
        parent::__construct($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null) {
        $columns = [
            static::SCENE_EXPORT => [
                'user.fullname' => '用户名',
                'user.telephone' => '电话',
//                'user.activities_records_count' => '邀请好友数量',
                'user.activities_registered_count' => '成功注册数',
                'user.activities_signed_count' => '成功完件数',
                'user.activities_payed_count' => '成功放款数',
                'user.activities_finish_count' => '正常结清数',
            ]
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        if($this->sence == self::SCENE_EXPORT){
            $data->user && $data->user->getText([ 'telephone']);
            $data->user->fullname = StringHelper::maskTelephone($data->user->fullname);
            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
//            $data->user->activities_records_count = $data->user->activities_records_count;
//            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
//            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
//            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
//            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
        }
    }

}
