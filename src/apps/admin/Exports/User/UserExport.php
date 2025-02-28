<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\User;

use Admin\Models\Feedback\Feedback;
use Common\Utils\Data\StringHelper;
use Common\Utils\Export\AbstractExport;

class UserExport extends AbstractExport
{
    /**
     * 用户反馈
     */
    const SCENE_FEEDBACK_LIST = 'SCENE_FEEDBACK_LIST';
    /**
     * 未认证
     */
    const SCENE_SUPER_NOT_AUTH_USER = 'SCENE_SUPER_NOT_AUTH_USER';

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_FEEDBACK_LIST => [
                'user.fullname' => '真实姓名',
                'user.telephone' => '手机号码',
                'type_text' => '用户类型',
                'content' => '意见',
                'created_at' => '意见提交时间',
            ],
            static::SCENE_SUPER_NOT_AUTH_USER => [
                'telephone' => '手机号码',
                'created_at' => '注册时间',
                'client_id' => '注册终端',
                'channel.channel_code' => '渠道',
            ],
        ];

        return $columns[$scene] ?? [];
    }

    /**
     * @param $data
     * @return mixed|void
     */
    protected function beforePutCsv($data)
    {
        if($this->sence == self::SCENE_FEEDBACK_LIST){
            $data->setScenario(Feedback::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['fullname', 'telephone']);
            $data->user->telephone = StringHelper::maskTelephone($data->user->telephone);
        }
        if($this->sence == self::SCENE_SUPER_NOT_AUTH_USER){
            $data->channel;
        }

    }
}
