<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\User;

use Admin\Exports\User\UserExport;
use Admin\Models\Feedback\Feedback;
use Admin\Models\Order\Order;
use Admin\Models\User\User;
use Admin\Models\User\UserInfo;
use Admin\Services\BaseService;
use Admin\Services\Data\DataServer;
use Common\Utils\Data\DateHelper;
use Common\Validators\Validation;

class UserServer extends BaseService
{
    private $user;

    /**
     * @param null $userId
     * @throws \Common\Exceptions\ApiException
     */
    public function __construct($userId = null)
    {
        if ($userId !== null && !$this->user = User::model()->getOne($userId)) {
            $this->outputException('订单数据不存在');
        }
    }

    /**
     * 获取用户列表
     * @param $param
     * @return mixed
     */
    public function list($param)
    {
        $size = array_get($param, 'size');
        $datas = User::model()->search($param)->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($datas as $data) {
            /** @var $data User */
            $data->setScenario(User::SCENARIO_LIST)->getText();
            $data->isAuths = UserAuthServer::server()->getAuthList($data);
            $data->authCompleteTime = UserAuthServer::server()->getAuthCompleteTime($data->id);
            $data->orderCount = 0;
            if ($data->orders->count()) {
                $data->orderCount = $data->orders->count();
                foreach ($data->orders as $order) {
                    $data->last_order_id = $order->id;
                    $data->last_order_status_text = t(array_get(Order::STATUS_ALIAS, $order->status), 'order');
                    break;
                }
                unset($data->orders);
            }
            if ($data->userInfo) {
                $data->userInfo->setScenario(UserInfo::SCENARIO_LIST)->getText();
                $language = $data->userInfo->language;
                $data->userInfo->language_arr = $language ? explode(',', $language) : [];
                $data->userInfo->city = \Common\Models\Common\Dict::getNameByCode($data->userInfo->city);
                $data->userInfo->province = \Common\Models\Common\Dict::getNameByCode($data->userInfo->province);
            }
            $data->channel && $data->channel->getText(['channel_code', 'channel_name']);
            unset($data->userAuths);
        }
        return $datas;
    }

    public function view()
    {
        $tabs = config('saas-business.user_info_tabs');
        $lastOrderId = $this->user->order->id ?? null;
        return DataServer::server($this->user->id, $lastOrderId)->list($tabs);
    }

    public function getFeedbackList($param)
    {
        $query = Feedback::model()->search($param);
        if ($this->getExport()) {
            UserExport::getInstance()->export($query, UserExport::SCENE_FEEDBACK_LIST);
        }
        $datas = $query->paginate(array_get($param, 'size'));
        foreach ($datas as $data) {
            $data->setScenario(Feedback::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['fullname', 'telephone']);

        }
        return $datas;
    }

    public function addBlack($param)
    {
        $user = User::model()->getOne($param['id']);
        if (!$user) {
            return $this->outputError('该记录不存在');
        }
        if (!UserBlackServer::server()->add($user->telephone, $param)) {
            return $this->outputError('修改失败');
        }
        return $this->outputSuccess('修改成功');
    }
    
    public function batchAddBlack($param) {
        if (isset($param['telephones'])) {
            $telephons = explode(',', $param['telephones']);
            $merchants = $param['merchant_id'];
            $res = [
                "success" => 0,
                "failed" => 0,
            ];
            if ($telephons && is_array($telephons) && is_array($merchants)) {
                foreach ($merchants as $merchantId){
                    foreach ($telephons as $telephone) {
                        $params = [
                            "merchant_id" => $merchantId,
                            'expire_time' => date("Y-m-d H:i:s", time() + 90 * 86400),
                            'black_time' => date("Y-m-d H:i:s"),
                            'remark' => $param['remark'] ?? "",
                        ];
                        if(isset($param['type']) && isset(\Common\Models\User\UserBlack::TPYE[$param['type']])) {
                            $params['type'] = $param['type'];
                        }else{
                            return $this->outputError('Wrong type!');
                        }
                        if (Validation::validateMobile(null, $telephone) && UserBlackServer::server()->add($telephone, $params)) {
                            $res['success']++;
                        }else{
                            $res['failed']++;
                        }
                    }
                }
            }
            return $this->outputSuccess("This time {$res['success']} success, {$res['failed']} failures");
        }
        return $this->outputError("该记录不存在");
    }

    /**
     * 更新用户未老用户
     * @param $id
     * @return mixed
     */
    public function qualityToOld($id)
    {
        return User::model()
            ->where('id', $id)
            ->where('quality', User::QUALITY_NEW)
            ->update([
                'quality' => User::QUALITY_OLD,
                'quality_time' => DateHelper::dateTime(),
            ]);
    }

    /**
     * 按渠道统计用户注册数
     * @param $channel
     * @return mixed
     */
    public function countRegisterByChannel($channel)
    {
        return User::model()->where('channel_id', $channel->id)->count();
    }
}
