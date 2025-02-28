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

class ActivityRecordExport extends AbstractExport {

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
                'activity.title' => '活动名称',
                'user.telephone' => '中奖手机号',
                'created_at' => '中奖时间',
                'activity.awards' => '中奖奖品',
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
            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
            $data->activity->awards = $data->activity->awards[0]->title;
        }
    }

}
