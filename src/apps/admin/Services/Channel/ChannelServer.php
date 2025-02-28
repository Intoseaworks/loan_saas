<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Channel;

use Admin\Exports\Channel\ChannelExport;
use Admin\Models\Channel\Channel;
use Admin\Models\Channel\ChannelCount;
use Admin\Models\User\User;
use Admin\Services\BaseService;
use Admin\Services\User\UserServer;
use Carbon\Carbon;
use Common\Models\Merchant\App;
use Common\Redis\Channel\ChannelRecordRedis;
use Common\Services\Order\OrderServer;
use Common\Utils\Baidu\BaiduApi;
use Common\Utils\Lock\LockRedisHelper;

class ChannelServer extends BaseService
{
    /**
     * 平台列表
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $query = Channel::model()->search($param);

        if ($this->getExport()) {
            ChannelExport::getInstance()->export($query, ChannelExport::SCENE_PLATFORM);
        }

        $datas = $query->paginate(array_get($param, 'size'));
        foreach ($datas as $data) {
            $data->setScenario(Channel::SCENARIO_LIST);
            $data->getText();
            $data->register_count = UserServer::server()->countRegisterByChannel($data);
            $data->order_count = OrderServer::server()->countOrdersByChannel($data);
            $data->loan_count = OrderServer::server()->countOrdersSuccessByChannel($data);
            $data->loan_amount = OrderServer::server()->countPaidAmountsSuccessByChannel($data);
        }
        return $datas;
    }

    /**
     * 流量监控
     * @param $param
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMonitor($param)
    {
        $query = Channel::model()->search($param);

        if ($this->getExport()) {
            ChannelExport::getInstance(['condition' => $param])->export($query, ChannelExport::SCENE_MONITOR);
        }

        $datas = $query->paginate(array_get($param, 'size'));
        foreach ($datas as $data) {
            /** @var $data Channel */
            $data->setScenario(Channel::SCENARIO_LIST);
            $data->getText();
            $data->register_pv = $this->countChannel($data, $param, ChannelCount::REGISTER_PV);
            $data->register_uv = User::model()->whereChannelId($data->id)->count();
            $data->download_pv = $this->countChannel($data, $param, ChannelCount::DOWNLOAD_PV);
            $data->download_uv = $this->countChannel($data, $param, ChannelCount::DOWNLOAD_UV);
        }
        return $datas;
    }

    /**
     * 渠道每日投放效果实时统计
     * @param $param
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMonitorItem($param)
    {
        $channelCode = array_get($param, 'channel_code');
        $channel = Channel::model()->whereChannelCode($channelCode)->first();
        $condition = [
            'channel_code' => $channelCode,
        ];
        $datas = User::model()->dailyStatistics($condition)->paginate(array_get($param, 'size'));
        foreach ($datas as $data) {
            $curDate = $data->count_at;
            $channelCount = ChannelCount::query()->whereCountAt($curDate)->first();
            $data->register_pv = $channelCount->register_pv ?? 0;
            $data->downloan_pv = $channelCount->download_pv ?? 0;
            $data->downloan_uv = $channelCount->download_uv ?? 0;
        }
        $res['channel'] = $channel->setScenario(Channel::SCENARIO_LIST)->getText();
        $res['list'] = $datas;
        return $res;
    }

    /**
     * 根据渠道条件统计count
     * @param Channel $channel
     * @param $param
     * @param $countParam
     * @return int|string
     */
    public function countChannel(Channel $channel, $param, $countParam)
    {
        $query = ChannelCount::model()->where('channel_id', $channel->id);

        if (($timeStart = array_get($param, 'time_start')) && ($timeEnd = array_get($param, 'time_end'))) {
            $query->whereBetween('count_at', [$timeStart, $timeEnd]);
        }

        /** 取出当天实时数据 */
        $today = Carbon::now()->toDateString();
        $count = ChannelRecordRedis::redis()->getValue($channel->channel_code . ':' . $countParam, $today) ?? 0;
        return $query->sum($countParam) + $count;
    }

    public function create($param)
    {
        $pageName = array_get($param, 'page_name');
        $channelCode = array_get($param, 'channel_code');
        if (!LockRedisHelper::helper()->addLock("lock:channel:{$pageName}-{$channelCode}", 3)) {
            return $this->outputException('操作过于频繁, 请稍后再试!');
        }
        if ($channel = Channel::model()->getOne(['page_name' => $pageName, 'channel_code' => $channelCode])) {
            return $this->outputError('渠道标识已存在');
        }
        $param['url'] = $this->buildUrl($pageName, $channelCode);
        $param['short_url'] = BaiduApi::getShortUrl($param['url']);
        $app = (new App())->getByPageName($pageName);
        $param['app_id'] =  $app->id ?? 0;
        return $this->outputSuccess('', Channel::model(Channel::SCENARIO_CREATE)->saveModel($param));
    }

    public function update($id, $param)
    {
        $model = Channel::model()->getOne($id);
        if (!$model) {
            return $this->outputError('记录不存在');
        }
        $model->setScenario(Channel::SCENARIO_UPDATE);
        if (!$model->saveModel($param)) {
            return $this->outputError('修改失败');
        }
        return $this->outputSuccess('修改成功');
    }

    public function del($id)
    {
        $channel = Channel::model()->getOne($id);
        if ($channel->status == Channel::STATUS_DELETE) {
            return $this->outputError('该渠道已删除');
        }
        if (!Channel::model()->delById($id)) {
            return $this->outputError('删除失败');
        }
        return $this->outputSuccess('删除成功');
    }

    /**
     * 渠道详情
     * @param $id
     * @return Channel
     */
    public function getOne($id)
    {
        return Channel::model()->getOne($id);
    }

    /**
     * 生成渠道推广地址
     * @param $code
     * @return string
     */
    public function buildUrl($pageName, $channelCode)
    {
        return "https://play.google.com/store/apps/details?id={$pageName}&referrer={$channelCode}";
    }

    /**
     * 更新状态
     * @param $id
     * @param $status
     * @return ChannelServer
     */
    public function updateStatus($id, $status)
    {
        $channel = Channel::model()->getOne($id);
        if (!$channel) {
            return $this->outputError('渠道不存在');
        }
        if ($channel->status == Channel::STATUS_DELETE) {
            return $this->outputError('该渠道已删除');
        }
        if ($channel->status == $status) {
            return $this->outputError('已修改');
        }
        if (!Channel::model()->updateStatus($id, $status)) {
            return $this->outputError('修改失败');
        }
        return $this->outputSuccess('修改成功');
    }

    /**
     * 置顶
     * @param $id
     * @return ChannelServer|void
     * @throws \Common\Exceptions\ApiException
     */
    public function updateTop($id)
    {
        $channel = Channel::model()->getOne($id);
        if (!$channel) {
            return $this->outputException('渠道不存在');
        }
        $top = $channel->is_top == Channel::NOT_TOP ? Channel::IS_TOP : Channel::NOT_TOP;
        if (!Channel::model()->updateTop($channel, $top)) {
            return $this->outputException('修改失败');
        }
        return $this->outputSuccess('修改成功');
    }

}
