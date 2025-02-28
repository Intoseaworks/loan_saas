<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/1/30
 * Time: 9:52
 */

namespace Admin\Services\Notice;

use Admin\Models\Inbox\Inbox;
use Admin\Models\Notice\Notice;
use Admin\Services\BaseService;
use Carbon\Carbon;
use Common\Jobs\PushNoticeJob;
use Common\Models\Merchant\App;
use Common\Models\Notice\SmsTaskUser;
use Common\Models\User\User;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Common\Validators\Validation;
use JMD\JMD;
use JMD\Libs\Services\SmsService;

class NoticeServer extends BaseService
{
    const VALUE_SEND_METHOD = [
        'notice' => '通知',
        'marketing' => '营销',
        'voice' => '语音',
//        'sms_captche' => '短信验证码',
//        'sms_notice' => '业务通知',
//        'sms_custom' => '自定义短信',
//        'sms_tpl' => '短信模板',
//        'sms_market' => '营销短信',
//        'sms_voice' => '语音模板',
//        'sms_voice_captche' => '语音验证码',
    ];

    const VALUE_SEND_STATUS = [
        0 => '未发送',
        1 => '待回调',
        2 => '发送成功',
        3 => '发送失败',
    ];

    public function getInboxList($params)
    {
        $datas = Inbox::model()->getInboxList($params);
        foreach ($datas as $data) {
            $data->setScenario(Inbox::SCENARIO_LIST)->getText();
            $data->user && $data->user->getText(['fullname', 'telephone']);
        }
        return $datas;
    }

    public function getNoticeList($params)
    {
        $datas = Notice::model()->getNoticeList($params);
        foreach ($datas as $data) {
            $data->setScenario(Notice::SCENARIO_LIST)->getText();
        }
        return $datas;
    }

    public function getNoticeListAll()
    {
        $merchant = MerchantHelper::getMerchantId();
        $app = App::model()->getByMerchantId($merchant);
        $datas = Notice::model()->where('app_id',$app->id)->where('status','!=',3)->where('pushed_end','>',Carbon::now()->toDateTimeString())->get(['id','title']);
        return $datas;
    }

    public function createOrUpdate($params)
    {
        # 改为后台选择app_id
        //$params['app_id'] = optional(App::model()->getFirstAppByMerchantId(MerchantHelper::getMerchantId()))->id;
        $noticeId = array_get($params, 'id');
        $notice = Notice::model()->updateOrCreateModel(Notice::SCENARIO_CREATE, ['id' => $noticeId], $params);
//        $type = array_get($params, 'type');
//        if ($notice && $type == 'save_and_send') {
//            $this->push($notice);
//        }
        return $notice;
    }

    public function push(Notice $notice)
    {
        $delay = strtotime($notice->pushed_at) - time();
        return dispatch((new PushNoticeJob($notice))->delay($delay));
    }

    public function noticeDelete($params)
    {
        if (!$model = Notice::model()->find($params['id'])) {
            return $this->outputException('记录不存在');
        }
        $model->setScenario(Notice::SCENARIO_UPDATE_STATUS)
            ->saveModel(['status' => Notice::STATUS_DELETED]);
        return $model;
    }

    public function noticeDeleteBySend($params)
    {
        if (!$model = Notice::model()->newQueryWithoutScopes()->find($params['id'])) {
            return $this->outputException('记录不存在');
        }
        if ($model->status != Notice::STATUS_SENDED) {
            return $this->outputException('仅可删除已发送公告');
        }
        $model->setScenario(Notice::SCENARIO_UPDATE_STATUS)
            ->saveModel(['status' => Notice::STATUS_DELETED]);
        return $model;
    }

    public function noticeDetail($params)
    {
        if (!$model = Notice::model()->find($params['id'])) {
            return $this->outputException('记录不存在');
        }
        return $model->setScenario(Notice::SCENARIO_LIST)->getText();
    }


    public function getSmsListOld($params)
    {
        /** 排除token 兼容印牛服务逻辑 */
        $params = array_except($params, 'token');
        $keyword = array_get($params, 'keyword_user');
        if (isset($keyword)) {
            if (Validation::zh($keyword)) {
                $keyword = trim($keyword);
                if (User::where('fullname', 'like', '%' . $keyword . '%')->exists()) {
                    $params['keyword_user'] = implode('_',
                        User::where('fullname', 'like', '%' . $keyword . '%')->pluck('telephone')->toArray());
                }
            }
        }

        // 从印牛服务获取短信列表
        $datas = $this->getServicesSmsList($params);
        $data = array_get($datas, 'data', []);
        // 返回fullname字段
        foreach ($data as $key => $val) {
            $user = User::getByTelephone($val['receive']);
            $datas['data'][$key]['fullname'] = $user->fullname ?? '';
            $datas['data'][$key]['type'] = t($val['type'], 'push');
            $datas['data'][$key]['status_text'] = t($val['status_text'], 'push');
            $datas['data'][$key]['call_status_text'] = t($val['call_status_text'], 'push');
        }
        return $datas;
    }

    public function getSmsList($params)
    {
        return SmsTaskUser::model()->getTaskList($params);
    }

    private function getServicesSmsList($params)
    {
        try {
            JMD::init(['projectType' => 'lumen']);
            $server = new SmsService();
            $res = $server->getSmsList($params);
            if ($res->isSuccess()) {
                return $res->getData();
            } else {
                DingHelper::notice($res->getAll(), '短信列表印牛服务异常');
                return false;
            }
        } catch (\Exception $e) {
            DingHelper::notice($e);
            return false;
        }
    }
}
