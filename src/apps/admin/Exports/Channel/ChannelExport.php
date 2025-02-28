<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-02-27
 * Time: 17:32
 */

namespace Admin\Exports\Channel;

use Admin\Models\Channel\Channel;
use Admin\Models\Channel\ChannelCount;
use Admin\Models\User\User;
use Admin\Services\Channel\ChannelServer;
use Admin\Services\Order\OrderServer;
use Admin\Services\User\UserServer;
use Common\Utils\Export\AbstractExport;

class ChannelExport extends AbstractExport
{
    /**
     * 平台列表
     */
    const SCENE_PLATFORM = 'SCENE_PLATFORM';

    /**
     * 流量监控
     */
    const SCENE_MONITOR = 'SCENE_MONITOR';

    /**
     * @var ChannelServer|null
     */
    protected $server;

    /**
     * ChannelExport constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->server = ChannelServer::server();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($scene = null)
    {
        $columns = [
            static::SCENE_PLATFORM => [
                'sort' => '排名',
                'channel_name' => '平台名称',
                'channel_code' => '合作标识',
                'url' => '推广链接',
                'cooperation_time_text' => '合作时间',
                'register_count' => '注册量',
                'order_count' => '借款量',
                'loan_count' => '放款成功订单数',
                'loan_amount' => '放款成功金额',
                'status_text' => '合作状态',
            ],
            static::SCENE_MONITOR => [
                'sort' => '排名',
                'channel_name' => '平台名称',
                'channel_code' => '合作标识',
                'url' => '推广链接',
                'register_pv' => '注册PV',
                'register_uv' => '注册UV',
                'download_pv' => '下载PV',
                'download_uv' => '下载UV',
                'status_text' => '状态',
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
        $data->setScenario(Channel::SCENARIO_LIST);
        $data->getText();

        if ($this->sence == static::SCENE_PLATFORM) {
            $data->register_count = UserServer::server()->countRegisterByChannel($data);
            $data->order_count = OrderServer::server()->countOrdersByChannel($data);
            $data->loan_count = OrderServer::server()->countOrdersSuccessByChannel($data);
            $data->loan_amount = OrderServer::server()->countPaidAmountsSuccessByChannel($data);
        }

        if ($this->sence == static::SCENE_MONITOR) {
            $data->register_pv = $this->server->countChannel($data, $this->params['condition'], ChannelCount::REGISTER_PV);
            $data->register_uv = User::model()->whereChannelId($data->id)->count();
            $data->download_pv = $this->server->countChannel($data, $this->params['condition'], ChannelCount::DOWNLOAD_PV);
            $data->download_uv = $this->server->countChannel($data, $this->params['condition'], ChannelCount::DOWNLOAD_UV);
        }
    }
}
