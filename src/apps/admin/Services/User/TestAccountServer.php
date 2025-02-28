<?php

namespace Admin\Services\User;

use Admin\Models\User\TestAccount;
use Admin\Models\User\User;
use Admin\Services\CollectionStatistics\CollectionStatisticsServer;
use Admin\Services\Test\TestOrderServer;
use Api\Services\Data\DataServer;
use Api\Services\Order\OrderServer;
use Common\Console\Services\Order\OrderBadServer;
use Illuminate\Support\Facades\Artisan;

class TestAccountServer extends \Common\Services\User\TestAccountServer
{
    const CONTROL_PANEL = [
        'overdue', //一键逾期
        'collection_bad', //一键坏账
        'id_card_complete', //身份证认证完善
        'id_card_clear', //身份认证清空
        'face_personal_complete', //人脸认证/个人信息补全
        'face_personal_clear', //人脸认证/个人信息清空
        'telephone_complete', //运营商认证补全
        'telephone_clear', //运营商认证清空
        'collection_statistics_clear', //催收统计清空
        'flux_statistics_clear', //流量统计清空
        'cancel_last_order', //取消最后一笔订单
        'collection_allocation', //催收分单
    ];

    /**
     * 测试用户列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getList($params)
    {
        $testAccount = TestAccount::model()->getList($params, ['user']);

        foreach ($testAccount as $item) {
            $item->user->setScenario(User::SCENARIO_LIST)->getText();
        }

        return $testAccount;
    }

    /**
     * 根据关键字查找用户
     * @param $keyword
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findUser($keyword)
    {
        $params = [
            'keyword' => $keyword,
        ];
        $data = User::model()->search($params)->get();
        foreach ($data as $item) {
            $item->setScenario(User::SCENARIO_LIST)->getText();
        }

        return $data;
    }

    /**
     * 根据 user_id 判断测试用户是否已存在
     * @param $userId
     * @return bool
     */
    public function findByUserId($userId)
    {
        return TestAccount::query()->where('user_id', $userId)->first();
    }

    /**
     * 添加测试用户
     * @param $userId
     * @return mixed
     */
    public function addTestAccount($userId)
    {
        return TestAccount::model()->add($userId);
    }

    /**
     * 获取测试用户详情
     * @param $id
     * @return array
     */
    public function getDetail($id)
    {
        $testAccount = TestAccount::model()->getById($id, ['user']);
        $user = $testAccount->user;

        return UserServer::server($user->id)->view();
    }

    /**
     * 测试面板功能
     * @param $id
     * @param $panel
     * @return bool
     */
    public function controlPanel($id, $panel)
    {
        $testAccount = TestAccount::model()->getById($id, ['user']);
        $user = $testAccount->user;

        $this->$panel($user);

        return true;
    }

    /**
     * 一键逾期
     * @param $user
     */
    protected function overdue($user)
    {
        TestOrderServer::server()->overdue($user, 10);
    }

    /**
     * 一键坏账
     * @param $user
     */
    protected function collection_bad($user)
    {
        OrderBadServer::server()->orderToBad();
    }

    /**
     * 身份证认证完善
     * @param $user
     *
     */
    protected function id_card_complete($user)
    {
        $dataServer = new DataServer();
        $dataServer->setIdCardStatus($user->id);
    }

    /**
     * 身份证认证清空
     * @param $user
     *
     */
    protected function id_card_clear($user)
    {
        $dataServer = new DataServer();
        $dataServer->clearIdCardStatus($user->id);
    }

    /**
     * 人脸认证/个人信息补全
     * @param $user
     *
     */
    protected function face_personal_complete($user)
    {
        $dataServer = new DataServer();
        $dataServer->setFaceAndBaseInfo($user->id);
    }

    /**
     * 人脸认证/个人信息清空
     * @param $user
     *
     */
    protected function face_personal_clear($user)
    {
        $dataServer = new DataServer();
        $dataServer->clearFaceAndBaseInfo($user->id);
    }

    /**
     * 运营商认证补全
     * @param $user
     *
     */
    protected function telephone_complete($user)
    {
        $dataServer = new DataServer();
        $dataServer->setTelephoneStatus($user->id);
    }

    /**
     * 运营商认证清空
     * @param $user
     *
     */
    protected function telephone_clear($user)
    {
        $dataServer = new DataServer();
        $dataServer->clearTelephoneStatus($user->id);
    }

    /**
     * 催收统计清空
     * @param $user
     *
     */
    protected function collection_statistics_clear($user)
    {
        if (!CollectionStatisticsServer::server()->resetCollectionStatistics()) {
            $this->outputError('正式环境统计不支持清空处理！');
        }
    }

    /**
     * 流量统计清空 todo
     * @param $user
     *
     */
    protected function flux_statistics_clear($user)
    {

    }

    /**
     * 取消最后一笔订单
     * @param $user
     */
    protected function cancel_last_order($user)
    {
        //只能取消 待签约状态订单，是否需要改成 将任意单状态置为取消
        OrderServer::server()->userCancel($user->order->id);
    }

    /**
     * 催收分单
     * @param $user
     *
     */
    protected function collection_allocation($user)
    {
        Artisan::call("collection:assign");
    }
}
