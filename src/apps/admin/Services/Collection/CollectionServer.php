<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Exports\Collection\CollectionOrderExport;
use Admin\Models\Collection\Collection;
use Admin\Models\Order\Order;
use Admin\Models\Order\RepaymentPlan;
use Admin\Models\Order\RepaymentPlanRenewal;
use Admin\Models\Upload\Upload;
use Admin\Models\User\User;
use Admin\Services\Data\DataServer;
use Api\Rules\Upload\UploadRule;
use Api\Services\Common\DictServer;
use Common\Console\Services\Order\OrderBadServer;
use Common\Models\Collection\CollectionAdmin;
use Common\Models\Collection\CollectionBlackList;
use Common\Models\Common\Config;
use Common\Models\Risk\RiskBlacklist;
use Common\Services\Order\OrderPayServer;
use Common\Services\Rbac\Models\Role;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Services\Risk\RiskBlacklistServer;
use Common\Utils\Data\DateHelper;
use Common\Utils\Export\LaravelExcel\importTrait;
use Common\Utils\LoginHelper;
use Common\Utils\Upload\ImageHelper;
use Common\Utils\ValidatorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Common\Utils\MerchantHelper;

class CollectionServer extends \Common\Services\Collection\CollectionServer {

    use importTrait;

    /**
     * 添加催收记录
     * @param $order Order
     * @param $params
     * @param bool $isRemind
     * @return CollectionServer
     */
    public function collectionSubmit($order, $params, $isRemind = true) {
        return $this->outputException('接口已关闭');

        $finalParams = $this->buildAddRecordParams($order, $params, $isRemind);

        CollectionRecordServer::server()->create($finalParams, $isRemind);

        return $this->outputSuccess('催收记录保存成功');
    }

    /**
     * 获取或创建 collection
     * @param $order
     * @param bool $isRemind
     * @return mixed
     */
    public function firstOrCreateCollection($order, $isRemind = true) {
        /* $collection = $order->collection;
          if (!$collection) {
          // adminId 设为 0
          $collection = self::createCollection($order, 0, null, $isRemind);
          }
          return Collection::model()->getOne($collection->id); */
    }

    /**
     * 构造创建 collection_record 的参数(collection 不存在则创建)
     * @param $order
     * @param $params
     * @param bool $isRemind
     * @return array
     */
    public function buildAddRecordParams($order, $params, $isRemind = true) {
        /* $collection = $this->firstOrCreateCollection($order, $isRemind);

          $contact = CollectionContact::model()->getUserSelfContact($order->id);

          $params = [
          'collection_id' => $collection->id,
          'contact_id' => $contact->id,
          'dial' => array_get($params, 'dial'),
          'progress' => array_get($params, 'progress'),
          'promise_paid_time' => array_get($params, 'promise_paid_time'),
          'remark' => array_get($params, 'remark'),
          ];

          return $params; */
    }

    /**
     * @param $param
     * @param bool $isMyOrderList
     * @return mixed
     */
    public function getList($param, $isMyOrderList = false) {
        $size = array_get($param, 'size');
        $query = Collection::model()->search($param, $isMyOrderList);//->setConnection("mysql_readonly")
        if ($this->getExport()) {
            CollectionOrderExport::getInstance()->export($query, CollectionOrderExport::SCENE_COLLECTION_ORDER_LIST);
        }

        $dataS = $query->paginate($size);
        //@phan-suppress-next-line PhanTypeNoPropertiesForeach
        foreach ($dataS as $data) {
            $data->setScenario(Collection::SCENARIO_LIST)->getText();
            $data->user->telephone = \Common\Utils\Data\StringHelper::desensitization($data->user->telephone);
            $data->order->telephone = \Common\Utils\Data\StringHelper::desensitization($data->order->telephone);
            $data->assign_username = $data->collectionAssignAdmin();
            if($data->call_test_status == 1){
                $data->order->fullname .= "[*]";
            }
            $data->call_test_status_txt = \Common\Models\Collection\Collection::CALL_TEST_STATUS[$data->call_test_status] ?? "";
            //$data->repayment_code = $data->order->reference_no."/".$data->user->userInfo->dg_pay_lifetime_id;
            $repayment_code = [];
            if ($data->order->reference_no) {
                $repayment_code[] = $data->order->reference_no;
            }
            if ($data->user->userInfo->dg_pay_lifetime_id) {
                $repayment_code[] = $data->user->userInfo->dg_pay_lifetime_id;
            }
            $data->repayment_code = implode(" / ", $repayment_code);
        }
        /** 我的订单不返回tabs */
        if ($isMyOrderList) {
            return $dataS;
        }
        return [
            'tabs' => $this->tabs($param),
            'data' => $dataS
        ];
    }

    /**
     * 展期试算
     * @param $params
     * @return array
     * @throws \Common\Exceptions\ApiException
     */
    public function renewalCalc($params) {
        $renewalDate = $params['date'];
        $id = $params['id'];
        /** @var Collection $collection */
        $collection = Collection::find($id);
        /** @var RepaymentPlan $repaymentPlan */
        $repaymentPlan = $collection->order->lastRepaymentPlan;
        $subject = CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
        $renewalPreInfo = [
            'renewal_days' => RepaymentPlanRenewal::RENEWAL_DEFAULT_DAYS,
            'renewal_charge' => $subject->renewalFee,
            'overdue_fee' => $subject->overdueFee,
            'renewal_paid_amount' => $subject->renewalPaidAmount,
        ];
        return $renewalPreInfo;
    }

    /**
     * 催收列表栏目
     * @return array
     */
    public function tabs($param = []) {
        $query = Collection::query();

        $statusNum = $query->select(DB::raw('count(status) as count, status'))->groupBy('status')->pluck('count',
                        'status')->toArray();

        #承诺还款数
        $statusCommittedRepaymentNum = $query
                ->whereIn('status', Collection::STATUS_NOT_COMPLETE)
                ->whereHas('collectionDetail', function ($collectinoDetail) {
                    $collectinoDetail->where('promise_paid_time', '>=', DateHelper::date());
                })
                ->count();
        $tabs = [];
        foreach (array_keys(Collection::STATUS) as $key) {
            if (in_array($key, Collection::STATUS_HIDDEN)) {
                continue;
            }
            if ($key == Collection::STATUS_COMMITTED_REPAYMENT) {
                $tabs[$key] = [
                    'label' => array_get(ts(Collection::STATUS, 'collection'), $key),
                    'count' => $statusCommittedRepaymentNum,
                ];
                continue;
            }
            $tabs[$key] = [
                'label' => array_get(ts(Collection::STATUS, 'collection'), $key),
                'count' => array_get($statusNum, $key, 0),
            ];
        }
        return $tabs ?? [];
    }

    /**
     * @param $id
     * @param bool $isMyOrderDetail
     * @return mixed
     * @throws \Common\Exceptions\ApiException
     */
    public function getOne($id, $isMyOrderDetail = false) {
        $collection = Collection::model()->getOne($id);
        if (!$collection) {
            return $this->outputException('该记录不存在');
        }
        if ($isMyOrderDetail && $collection->admin_id != LoginHelper::getAdminId()) {
            return $this->outputException('无权限查看该记录');
        }
        $tabs = [
            DataServer::COLLECTION_INFO,
            DataServer::COLLECTION_RECORD_LIST,
            DataServer::ORDER_LIST,
            DataServer::DEDUCTION_HISTORY,
        ];
        $data = DataServer::server($collection->user_id, $collection->order_id)->list($tabs);
        $data['last_collection_id'] = CollectionServer::server()->getLastId($collection->id, $isMyOrderDetail);
        $data['next_collection_id'] = CollectionServer::server()->getNextId($collection->id, $isMyOrderDetail);
//        $data['repay_link'] = $this->getRepayLink($collection->order_id);
        return $data;
    }

    //获取催收数据
    public function getCollectionData($res)
    {
        $res['data']['repay_link'] = $res['data']['collectionInfo']['order']->reference_no ?? "--";
        $res['data']['repay_code'] = $res['data']['collectionInfo']['order']->reference_no ?? "--";
        $res['data']['social_info'] = !empty($res['data']['collectionInfo']['user']->userInfo->social_info) ? json_decode($res['data']['collectionInfo']['user']->userInfo->social_info):null;
        $res['data']['user_work'] = $res['data']['collectionInfo']['user']->userWork;
        if (!empty(trim($res['data']['user_work']->employment_type))){
            $where = [];
            $where[] = ['code','=',$res['data']['user_work']->employment_type];
            $res['data']['user_work']->employment_type = isset(DictServer::server()->getListByArray($where)[0]) ? DictServer::server()->getListByArray($where)[0]['name']:$res['data']['user_work']->employment_type;
        }
        if (!empty(trim($res['data']['user_work']->working_time_type))){
            $where = [];
            $where[] = ['code','=',$res['data']['user_work']->working_time_type];
            $res['data']['user_work']->work_experience_years = isset(DictServer::server()->getListByArray($where)[0]) ? DictServer::server()->getListByArray($where)[0]['name']:$res['data']['user_work']->working_time_type;
        }
        if (!empty(trim($res['data']['user_work']->industry))){
            $where = [];
            $where[] = ['code','=',$res['data']['user_work']->industry];
            $res['data']['user_work']->industry = isset(DictServer::server()->getListByArray($where)[0]) ? DictServer::server()->getListByArray($where)[0]['name']:$res['data']['user_work']->industry;
        }
        //个人自拍照
        $upload = (new \Admin\Models\Upload\Upload())->getPathsByUserIdAndType($res['data']['collectionInfo']['user']->id, [
            Upload::TYPE_FACES
        ]);
        $fileUrls = [];
        foreach ($upload as $k => $v) {
            $fileUrls[$k] = ImageHelper::getPicUrl($v, 400);
        }
        $faceFile = array_get($fileUrls, Upload::TYPE_FACES);
        $res['data']['user_face'] = $faceFile;
        //个人自拍照end
        $res['data']['dg_pay_link'] = $res['data']['collectionInfo']['user']->userInfo->dg_pay_lifetime_id ?? "--";
        $res['data']['dg_pay_code'] = $res['data']['collectionInfo']['user']->userInfo->dg_pay_lifetime_id ?? "--";
        $admin_id = $res['data']['collectionInfo']['admin_id'];
        $colloction = CollectionAdmin::model()->where(['admin_id' => $admin_id, 'status' => '1'])->get();
        if (count($colloction)) {
            $colloction = $colloction->toArray();
            foreach ($colloction as $col) {
                $levels[] = $col['level_name'];
            }
            $res['data']['collection_levels'] = $levels;
        }else{
            $res['data']['collection_levels'] = null;
        }
//        $res['tabs'][] = ['label'=>'减免记录','name'=>'deductionHistory','count'=>0];
        return $res;
    }

    /**
     * @param $collectionId
     * @param bool $isMyOrderDetail
     * @return int
     */
    public function getLastId($collectionId, $isMyOrderDetail = false) {
        if (!$collection = Collection::model()->getLastOne($collectionId,
                $isMyOrderDetail ? LoginHelper::getAdminId() : 0)) {
            return 0;
        }
        return $collection->id;
    }

    /**
     * @param $collectionId
     * @param bool $isMyOrderDetail
     * @return int
     */
    public function getNextId($collectionId, $isMyOrderDetail = false) {
        if (!$collection = Collection::model()->getNextOne($collectionId,
                $isMyOrderDetail ? LoginHelper::getAdminId() : 0)) {
            return 0;
        }
        return $collection->id;
    }

    /**
     * 取出<催收>字样角色人员作为催收人员
     */
    public function getCollector($onlyCollector = false) {
        /** 取出<催收>字样角色 */
        $roleQuery = Role::where('deleted_at', null);
        $roleQuery->where(function ($query) {
            $query->where('name', 'like', '%催收%');
            $query->orWhere('name', 'like', '%collect%');
            $query->orWhere('name', 'like', '%Collect%');
        });
        if ($onlyCollector) {
            $roleQuery->where('name', '!=', '催收管理员');
        }
        $roleIds = $roleQuery->pluck('id')->toArray();
        $userArr = array_get((new Role)->roleUserList(999, $roleIds), 'data');
        $data = [];
        foreach ($userArr as $user) {
            $data[$user['id']] = $user['username'];
        }
        return $data ?? [];
    }

    public function getByIds($collectionIds, $status = []) {
        $query = Collection::query();
        if ($status) {
            $query->whereIn('status', (array) $status);
        }
        return $query->whereIn('id', $collectionIds)->get();
    }

    /**
     * 判断是否催收员
     *
     * @param string $adminId
     * @return bool
     */
    public function isCollector($adminId = '') {
        if ($adminId == '') {
            $adminId = LoginHelper::getAdminId();
        }
        return array_key_exists($adminId, $this->getCollector(true));
    }

    public function checkCaseBelong($collection) {
        $adminId = LoginHelper::getAdminId();
        if (CollectionServer::server()->isCollector($adminId) && $adminId != $collection->admin_id) {
            return $this->outputException('催收单已流转其他催收员');
        }
    }

    public function setOrderPartRepayOn($params) {
        $on = array_get($params, 'on');
        $id = array_get($params, 'id');
        $collection = Collection::model()->getOne($id);
        $this->checkCaseBelong($collection);
        $repaymentPlan = $collection->lastRepaymentPlan;
        if (in_array($repaymentPlan->status, RepaymentPlan::FINISH_STATUS)) {
            return $this->outputException('还款计划已完结');
        }
        if ($on == RepaymentPlan::CAN_PART_REPAY) {
            if (!(new Config)->getRepayPartRepayOn() || !($repayPartRepayConfig = (new Config)->getRepayPartRepayConfig())) {
                return $this->outputException('所有部分还款开关关闭');
            }
            # 所有逾期部分开关关闭
//            if (!(new Config)->getRepayPartAllOverdueOn()) {
//                return $this->outputException('所有逾期部分还款开关关闭');
//            }
            # 部分还款最低逾期天数配置
            $repayPartMinOverdueDays = (new Config)->getRepayPartMinOverdueDays();
            if ($repayPartMinOverdueDays === false) {
                return $this->outputException('部分还款最低逾期天数未配置');
            }
            # 逾期天数小于配置跳过
            if ($repaymentPlan->overdue_days < $repayPartMinOverdueDays) {
                return $this->outputException('逾期天数小于配置');
            }
        }
        $repaymentPlan->can_part_repay = $on;
        if (!$repaymentPlan->save()) {
            return $this->outputException('保存失败');
        }
    }

    public function getRepayLink($orderId, $repayAmount = null) {
        $order = Order::query()
                ->where([
                    'id' => $orderId,
                ])
                ->first();
        if (!in_array($order->status, Order::WAIT_REPAYMENT_STATUS)) {
            return t('当前不支持还款,请联系客服');
        }
        $server = OrderPayServer::server()->htmlPayment($order, "razorpay", $repayAmount, null, 'collection');
        return $server['pay_url'];
    }

    /**
     * 切换催收黑名单状态
     * @param $userId
     * @return bool|CollectionBlackList|int
     */
    public function switchBlackList($userId) {
        $user = User::model()->getOne($userId);
        if (!$user) {
            return $this->outputException('用户不存在');
        }
        $blackList = CollectionBlackList::query()->whereUserId($userId)->first();
        $order = $user->order;
        /** 添加 */
        if (!$blackList) {
            // 坏账处理
            $order->lastRepaymentPlan && OrderBadServer::server()->handleBad($order->lastRepaymentPlan);
            // 风控黑名单入黑
            RiskBlacklistServer::server()->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE);
            return CollectionBlackList::model()->create($userId);
        }
        /** 修改状态 */
        $toStatus = intval(!$blackList->status);
        if ($toStatus == CollectionBlackList::STATUS_DISABLE) {
            // 若失效黑名单撤销坏账状态，重新允许催收分配
            OrderBadServer::server()->recallBad($order);
        } else {
            // 坏账处理
            OrderBadServer::server()->handleBad($order->lastRepaymentPlan);
        }
        return CollectionBlackList::model()->setStatus($userId, $toStatus);
    }

    /**
     * 导入催收黑名单
     * @param Request $request
     * @return CollectionServer
     */
    public function importBlacklist(Request $request) {
        $importData = $this->importExcel($request->file('file'));
        $success = 0;
        $count = count($importData);
        foreach ($importData as $data) {
            $this->checkoutFormat($data);
            $success += $this->manualAddBlack($data);
        }
        return $this->outputSuccess("上传{$count}条，成功导入{$success}条");
    }

    /**
     * 检查导入黑名单excel格式
     * @param $data
     * @param UploadRule $rule
     * @return RiskBlacklistServer
     * @throws \Common\Exceptions\ApiException
     */
    private function checkoutFormat($data) {
        list($cardType, $cardNo, $telephone) = $data;
        if (!in_array($cardType, Upload::TYPE_BUK)) {
            return $this->outputException('cardType不合法 请使用' . implode('|', Upload::TYPE_BUK));
        }
        if (!ValidatorHelper::mobile($telephone)) {
            return $this->outputException("号码格式不正确[{$telephone}]");
        }
    }

    /**
     * 导入人工催收入黑
     * @param $data
     * @return bool|CollectionBlackList
     * @throws \Exception
     */
    private function manualAddBlack($data) {
        list($cardType, $cardNo, $telephone) = $data;
        # 根据caryType+cardNo/telephone寻找用户
        $user = User::whereCardType($cardType)->whereIdCardNo($cardNo)->orWhere('telephone', $telephone)->first();
        if (!$user) {
            return false;
        }
        $blackList = CollectionBlackList::query()->whereUserId($user->id)->first();
        $order = $user->order;
        /** 添加 */
        if (!$blackList) {
            // 坏账处理
            $order->lastRepaymentPlan && OrderBadServer::server()->handleBad($order->lastRepaymentPlan);
            // 风控黑名单入黑
            RiskBlacklistServer::server()->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE);
            return CollectionBlackList::model()->create($user->id);
        }
        return false;
    }

}
