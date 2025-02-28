<?php

/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-20
 * Time: 10:06
 */

namespace Approve\Admin\Services\Approval;

use Admin\Services\Staff\StaffServer;
use Api\Services\Common\DictServer;
use Api\Services\User\UserInfoServer;
use Approve\Admin\Config\Params;
use Approve\Admin\Services\CommonService;
use Approve\Admin\Services\Log\ApproveLogService;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApprovePageStatistic;
use Common\Models\Approve\ApprovePool;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Common\Dict;
use Common\Models\Order\Order;
use Common\Models\Order\OrderDetail;
use Common\Models\User\UserContact;
use Common\Traits\GetInstance;
use Common\Utils\Code\ApproveStatusCode;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Third\AirudderHelper;
use Common\Models\Approve\ApproveCallLog;
use Common\Models\Approve\ApproveUserPool;
use Common\Utils\LoginHelper;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Utils\Upload\ImageHelper;
use Illuminate\Support\Optional;

class CallApproveService {

    use GetInstance;

    /**
     * @var string
     */
    const GAVE_UP = 'call_approve_gave_up';

    /**
     * @var string
     */
    const FAILURE_CHECK = 'call_approve_failure_check';

    /**
     * @var string
     */
    const NO_ANSWER = 'call_approve_no_answer';

    /**
     * @var string
     */
    const PASS = 'call_approve_pass';

    /**
     * @var Order|null
     */
    protected $order = null;

    /**
     * CallApprove constructor.
     *
     * @param $orderId
     * @throws CustomException
     */
    public function __construct($orderId) {
        $this->order = Order::model()->getOne($orderId);
        if (!$this->order) {
            throw new CustomException(t('order not found'), ApproveStatusCode::ORDER_NOT_FOUND);
        }
    }

    /**
     * @return array
     */
    public function detail() {
//        $this->checkOrderStatus();

        $detailData = [];
        //紧急联系人
        $contact = $this->getUserContract($this->order);
        $userContact = $userContactOther = [];
        /** @var UserContact $v */
        foreach ($contact as $key => $v) {
            $userContact[] = [
                'relation' => $v->relation,
                'name' => $v->contact_fullname,
                'phone' => $v->contact_telephone,
                'check_status' => $v->check_status ? t(array_get(AirudderHelper::STATUS_ALIAS, $v->check_status, '---'), 'approve') : '---',
                'manual_call_result' => $v->manual_call_result
            ];
        }
        //补充联系人
        $contact = $this->getUserContract($this->order, true);
        foreach ($contact as $key => $v) {
            $userContactOther[] = [
                'relation' => $v->relation,
                'name' => $v->contact_fullname,
                'phone' => $v->contact_telephone,
                'check_status' => t(array_get(AirudderHelper::STATUS_ALIAS, $v->check_status, '---'), 'approve'),
                'manual_call_result' => $v->manual_call_result
            ];
        }
        $user = $this->order->user;
        $order = $this->order;
        $userInfo = $this->order->userInfo;
        $userWork = $this->order->user->userWork;
        $userBankCard = $user->bankCard;
        $detailData['clm_level'] = '--';
        $detailData['clm_amount'] = $order->principal;
        $detailData['clm_remark'] = '--';
        $riskResult = $order->riskStrategyResult();
        if ($riskResult) {
            $detailData['clm_level'] = $riskResult->score_rank;
            $detailData['clm_amount'] = $riskResult->sug_loan_amt && $riskResult->sug_loan_amt > 0 ? $riskResult->sug_loan_amt : $order->principal;
            $detailData['clm_remark'] = $riskResult->remark;
        }
        $detailData['order'] = [
            "principal" => $this->order->principal,
            "apply_principal" => $this->order->principal,
        ];

        $detailData['order_status'] = CommonService::getInstance()->orderStatus($this->order->status);
        $detailData['order_no'] = $this->order->order_no;
        if ( $approvePool = ApprovePool::model()->where("order_id", $this->order->id)->where("order_status", 4)->where("call_test_status", 1)->orderByDesc("created_at")->first()){
            $detailData['order_no'] = $this->order->order_no.'(call success)';
        }
        $detailData['telephone'] = $user->telephone;
        $detailData['fullname'] = $user->fullname;
        $detailData['gender'] = $userInfo->gender;
        $detailData['current_address'] = $userInfo->address;
        $detailData['birthday'] = $userInfo->birthday;
        $detailData['age'] = DateHelper::getAge($userInfo->birthday);
        $detailData['id_card_type'] = $user->card_type;
        $detailData['id_card_no'] = $user->id_card_no;
        $detailData['marital_status'] = $userInfo->marital_status;
        $detailData['education_level'] = $userInfo->education_level;
        $detailData['confirm_month_income'] = $userInfo->confirm_month_income ?? null;
        $detailData['monthly_income'] = Dict::getNameByCode($userWork->salary);
        $detailData['employment_type'] = Dict::getNameByCode($userWork->employment_type);
        $detailData['work_phone'] = $userWork->work_phone;
        $detailData['work_company'] = $userWork->company;
        $detailData['work_experience_years'] = $userWork->work_experience_years;
        $detailData['working_years'] = Dict::getNameByCode($userWork->working_time_type);
        $detailData['industry'] = Dict::getNameByCode($userWork->industry);
        $detailData['profession'] = $userWork->profession;
        $detailData['social_info'] = ArrayHelper::jsonToArray($userInfo->social_info);

        $detailData['payment_type'] = $userBankCard->payment_type == 'other' ? $userBankCard->channel:$userBankCard->payment_type;
        $detailData['bank_name'] = $userBankCard->bank_name ?? "";
        $detailData['bank_card_no'] = $userBankCard->account_no ?? "";
        $detailData['bank_city'] = null;
        $detailData['can_contact_time'] = Dict::getNameByCode(OrderDetail::model()->getCanContactTime($this->order));

        $detailData['emergency_contact'] = $userContact;
        $approveResultStatus = $this->approveStatusList();
        $detailData['call_approve_result'] = ArrayHelper::arrToOption($approveResultStatus, 'key', 'val');
        $detailData['refusal_code'] = $this->getRefusalCode();
        $detailData['contact_options'] = $this->getContactOptions();

        $upload = (new \Admin\Models\Upload\Upload())->getPathsByUserIdAndType($order->user->id, [
            Upload::TYPE_BUK_NIC,
            Upload::TYPE_BUK_PASSPORT,
        ]);
        $fileUrls = [];
        $imgRelation = array_flip(Upload::TYPE_BUK);
        foreach ($upload as $k => $v) {
            $fileUrls[$k] = ImageHelper::getPicUrl($v, 400);
        }
        $faceFile = array_get($fileUrls, Upload::TYPE_FACES);
        $companyIdFile = array_get($fileUrls, Upload::TYPE_COMPANY_ID);
        /** 菲律宾多个身份证明 */
        $idCardFile = array_get($fileUrls, $imgRelation[$user->card_type]);

        if (!$idCardFile) {
            foreach ($upload as $type => $url) {
                if (in_array($type, [
                            Upload::TYPE_BUK_NIC,
                            Upload::TYPE_BUK_PASSPORT,
                        ])) {
                    $idCardFile = $fileUrls[$type];
                    break;
                }
            }
        }
        $detailData['id_card'] = $idCardFile;
        $detailData['company_id'] = $companyIdFile;
        $detailData['user_face'] = $faceFile;
        $detailData['user_type'] = array_get(User::model()->getQuality(), $order->quality, '');
        $detailData['loan_days'] = $order->loan_days; #贷款周期
        $detailData['principal'] = $order->principal; #贷款额度
        //审核维护联系人
        $detailData['contact_relation'] = $this->getRelationOptions();
        $detailData['emergency_contact_other'] = $userContactOther;
        $detailData['reason_for_failure'] = ApproveLogService::getInstance()->getFirstLog($order->id, ManualApproveLog::APPROVE_TYPE_CALL);
        //按新需求更改拒绝原因
        $reason_for_failure_change = false;
        foreach ($detailData['refusal_code'] as  $v){
            if ($order->refusal_code && $order->refusal_code==$v['key']){
                $detailData['reason_for_failure'] = $v['val'];
                $reason_for_failure_change = true;
                break;
            }
        }
        if (!$reason_for_failure_change) {
            $detailData['reason_for_failure'] = '';
        }
        $firstLogInstance = optional(ApproveLogService::getInstance()->getFirstLogInstance($order->id, ManualApproveLog::APPROVE_TYPE_CALL));
        $detailData['approveResult'] = $firstLogInstance->result;
        $detailData['approveUser'] = optional(StaffServer::findOneById($firstLogInstance->admin_id))->nickname;
        $detailData['approveTime'] = $firstLogInstance->created_at;
        $detailData['orderStatus'] = $firstLogInstance->from_order_status;
        $detailData['reason_for_remark'] = ArrayHelper::jsonToArray($firstLogInstance->remark);
        return $detailData;
    }

    /**
     * @return bool
     * @throws CustomException
     */
    protected function checkOrderStatus() {
        if (!in_array($this->order->status, [Order::STATUS_WAIT_TWICE_CALL_APPROVE, Order::STATUS_WAIT_CALL_APPROVE])) {
            throw new CustomException(t('该订单不能进行审批'), ApproveStatusCode::ORDER_STATUS_INVALID);
        }
        return true;
    }

    /**
     * 获取紧急联系人,兼容v1.4从快照取数据
     * 2019/07/10 需求变更,这里不取快照的数据
     *
     * @param Order $order
     * @param bool $isSupplement
     * @return \Illuminate\Support\Collection
     */
    protected function getUserContract(Order $order, $isSupplement = false) {
        return UserContact::whereUserId($order->user_id)->whereStatus(UserContact::STATUS_ACTIVE)->whereIsSupplement(intval($isSupplement))->get();
    }

    /**
     * @return array
     */
    protected function getRadioList() {
        $list = Params::callApprove('normal_status_list');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * 电审拒绝码
     * @return array
     */
    protected function getRefusalCode() {
        $list = Params::callApprove('refusal_code');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * 电审拒绝码
     * @return array
     */
    protected function getContactOptions() {
        $list = Params::callApprove('contact_options');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * 电审拒绝码
     * @return array
     */
    protected function getRelationOptions() {
        $list = DictServer::server()->getList('RELATIONSHIP');
        $list = array_column($list, 'code');

        return $list;
    }

    /**
     * @return array
     */
    protected function getEnglistLevelList() {
        $list = Params::callApprove('english_level_list');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * @param bool $format
     * @return array
     */
    protected function getGiveUpLoanList($format = true) {
        $list = Params::callApprove('give_up_loan_list');

        if ($format) {
            return ArrayHelper::arrToOption($list, 'key', 'val');
        } else {
            return $list;
        }
    }

    /**
     * @return array
     */
    protected function approveStatusList() {
        return [
//            static::GAVE_UP => t('客户放弃借款', 'approve'),
            static::PASS => t('审批通过', 'approve'),
            static::FAILURE_CHECK => t('审批不通过', 'approve'),
            static::NO_ANSWER => t('挂起', 'approve'),
        ];
    }

    /**
     * @param $data
     * @return array|bool
     * @throws CustomException
     */
    public function submit($data) {
        $this->checkOrderStatus();

        $order = $this->order;
        $approveStatus = array_get($data, 'status');
        $refusalCode = array_get($data, 'refusal_code');
        $approvePrincipal = array_get($data, 'approve_principal', 0);

        $remarkArray = [];
        //备注加入
        $remarkArray['remark'] = $data['remark'] ?? null;
        $approveStatusList = $this->approveStatusList();
        if ($approveStatusList[$approveStatus] ?? false) {
            $remarkArray[] = $approveStatusList[$approveStatus];
        }

        /** 更新资料 */
        SubmitService::updateUserInfo($order, $data);

        $this->updateContact($data, $order->user_id);

        $statusCode = SubmitService::getInstance()->callApproveSubmit(
                $order->id,
                $approveStatus,
                $refusalCode,
                $remarkArray,
                $approvePrincipal
        );

        # 处理CLM max_leve & total_amount
        /*
          if($approveStatus == CallApproveService::PASS){
          $monthIncome = 12000;
          if($data['confirm_month_income']){
          $monthIncome = max([12000, $data['confirm_month_income']]);
          }
          $customer = \Common\Services\NewClm\ClmServer::server()->getOrInitCustomer($order->user);
          if (\Common\Services\NewClm\ClmServer::server()->isUpdateMaxLevel($customer, round($monthIncome * 2 / 3))) {
          $clmAmount = \Common\Models\NewClm\ClmAmount::getMaxLevelByAmount(round($monthIncome * 2 / 3));
          $customer->max_level = $clmAmount->clm_level;
          $customer->total_amount = $clmAmount->clm_amount;
          $customer->save();
          }
          } */

        // 统计页面耗时
        $commonService = CommonService::getInstance();
        $statisticRedisKey = $commonService->getStartCheckRedisKey($data['id']);
        $cost = $commonService->getRedis()->get($statisticRedisKey) ?: time() - 2;
        // 统计页面耗时信息
        $statisticCost = [
            'merchant_id' => $order->merchant_id,
            'order_id' => $order->id,
            'approve_user_pool_id' => $data['id'],
            'type' => ApprovePageStatistic::TYPE_CALL_APPROVE,
            'admin_id' => $data['admin_id'],
            'cost' => time() - (int) $cost,
            'status' => ApprovePageStatistic::STATUS_EFFECTIVE,
            'page' => ApprovePageStatistic::CASHNOW_PAGE_CALL_BASEDETAIL,
        ];
        $commonService->getRedis()->del($statisticRedisKey);
        ApprovePageStatistic::saveData($statisticCost);

        return [
            'snapshoot' => $this->makeSnapshoot($data, $statusCode),
            'code' => $statusCode,
        ];
    }
    
    public function onlySave($data) {
        $order = $this->order;
        /** 更新资料 */
        SubmitService::updateUserInfo($order, $data);

        $this->updateContact($data, $order->user_id);
        return [
            'snapshoot' => $this->makeSnapshoot($data, ''),
            'code' => '',
        ];
    }

    /**
     * 更新紧急联系人
     *
     * @param $data
     * @param $userId
     */
    public function updateContact($data, $userId) {
        \DB::beginTransaction();
        try {
            UserInfoServer::server()->clearUserContact($userId);
            $emergencyContacts = array_get($data, 'emergency_contact');
            $emergencyContactOthers = array_get($data, 'emergency_contact_other');
            foreach ($emergencyContacts as $emergencyContact) {
                UserInfoServer::server()->addUserContact($emergencyContact, $userId);
            }
            foreach ($emergencyContactOthers as $emergencyContactOther) {
                UserInfoServer::server()->addUserContact($emergencyContactOther, $userId, true);
            }
        } catch (\Exception $exception) {
            \DB::rollBack();
        }
        \DB::commit();
    }

    /**
     * @param $data
     * @param $statusCode
     * @return array
     */
    protected function makeSnapshoot($data, $statusCode) {
        $snapshoot = [];
        //紧急联系人
        $contact = $this->getUserContract($this->order);
        $userContact = $userContactOther = [];
        /** @var UserContact $v */
        foreach ($contact as $key => $v) {
            $userContact[] = [
                'relation' => $v->relation,
                'name' => $v->contact_fullname,
                'phone' => $v->contact_telephone,
                'check_status' => t(array_get(AirudderHelper::STATUS_ALIAS, $v->check_status, '---'), 'approve'),
                'manual_call_result' => $v->manual_call_result
            ];
        }
        //补充联系人
        $contact = $this->getUserContract($this->order, true);
        foreach ($contact as $key => $v) {
            $userContactOther[] = [
                'relation' => $v->relation,
                'name' => $v->contact_fullname,
                'phone' => $v->contact_telephone,
                'check_status' => t(array_get(AirudderHelper::STATUS_ALIAS, $v->check_status, '---'), 'approve'),
                'manual_call_result' => $v->manual_call_result
            ];
        }
        $user = $this->order->user;
        $userInfo = $this->order->userInfo;
        $userWork = $this->order->user->userWork;
        $userBankCard = $user->bankCard;

        $snapshoot['order_status'] = CommonService::getInstance()->orderStatus($this->order->status);
        $snapshoot['order_no'] = $this->order->order_no;
        $snapshoot['telephone'] = $user->telephone;
        $snapshoot['fullname'] = $user->fullname;
        $snapshoot['gender'] = $userInfo->gender;
        $snapshoot['birthday'] = $userInfo->birthday;
        $snapshoot['age'] = DateHelper::getAge($userInfo->birthday);
        $snapshoot['id_card_type'] = $user->card_type;
        $snapshoot['id_card_no'] = $user->id_card_no;
        $snapshoot['marital_status'] = $userInfo->marital_status;
        $snapshoot['education_level'] = $userInfo->education_level;
        $snapshoot['monthly_income'] = Dict::getNameByCode($userWork->salary);
        $snapshoot['employment_type'] = Dict::getNameByCode($userWork->employment_type);
        $snapshoot['work_phone'] = $userWork->work_phone;
        $snapshoot['work_company'] = $userWork->company;
        $snapshoot['work_experience_years'] = $userWork->work_experience_years;
        $snapshoot['working_years'] = Dict::getNameByCode($userWork->working_time_type);
        $snapshoot['industry'] = Dict::getNameByCode($userWork->industry);
        $snapshoot['profession'] = $userWork->profession;

        $snapshoot['bank_name'] = $userBankCard->bank_name;
        $snapshoot['bank_card_no'] = $userBankCard->account_no;
        $snapshoot['bank_city'] = null;
        $snapshoot['can_contact_time'] = Dict::getNameByCode(OrderDetail::model()->getCanContactTime($this->order));

        $snapshoot['emergency_contact'] = $userContact;

        $snapshoot['emergency_contact_other'] = $userContactOther;
        $result = $this->getApproveResultText($data, $statusCode);
        $snapshoot['approveResult'] = $result['approveResult'];
        $snapshoot['riskResult'] = $result['riskResult'];
        $snapshoot['version'] = CommonService::getInstance()->getOrderVersion();
        $snapshoot['flows'] = [];

        return $snapshoot;
    }

    /**
     * @param array|string $key
     * @param array|int $checked
     * @return string
     */
    protected function getSelectedMsg($key, $checked) {
        if (!is_array($key)) {
            $list = Params::callApprove($key);
        } else {
            $list = $key;
        }

        if (!is_array($checked)) {
            $checked = (array) $checked;
        }
        $checked = array_flip($checked);
        $messages = array_intersect_key($list, $checked);
        return implode(',', $messages);
    }

    /**
     * @param $data
     * @param $statusCode
     * @return array
     */
    protected function getApproveResultText($data, $statusCode) {
        // 获取提交的状态
        $resultText = 'Call approval pass';
        $riskText = '';
        // 审批通过
        if ($data['status'] == static::PASS) {
            // 机审拒绝
            if ($statusCode['approveResult'] == ApprovePoolLog::CALL_APPROVE_RESULT_RISK_REJECT) {
                $riskText = t('机审拒绝') . ' - ' . t('居住城市不是目标城市/条件不达标');
            } else {
                $riskText = t('机审通过');
            }
        }
        switch ($data['status']) {
            case static::GAVE_UP:
                $text = $this->getSelectedMsg('give_up_loan_list', $data['gave_up_reason'] ?? -1);
                $resultText = t('人工取消 ') . ' - ' . $text;
                break;
            case static::FAILURE_CHECK:
                $resultText = 'Failure of information check';
                break;
            case static::NO_ANSWER:
                $resultText = t('人工取消 ') . ' - ' . t('电审未接听');
                break;
        }

        return ['approveResult' => $resultText, 'riskResult' => $riskText];
    }

    /**
     * @return array
     */
    public function initSnapshoot() {
        //紧急联系人
        $contact = $this->getUserContract($this->order);
        $userContact = [];
        $contactTelephones = [];
        $userInfo = $this->order->userInfo;
        $userWork = $this->order->user->userWork;
        /** @var UserContact $v */
        foreach ($contact as $v) {
            $contactTelephone = $v->contact_telephone;
            if (!empty($contactTelephone)) {
                $contactTelephones[] = $contactTelephone;
            }
            $userContact[] = $v->contact_fullname . '(' . $v->relation . ')' . $contactTelephone;
        }

        $snapshoot = [];
        $snapshoot['user_name'] = $this->order->user->fullname;
        $snapshoot['gender'] = $userInfo->gender ?? "";
        $snapshoot['date_of_birthday'] = $userInfo->birthday ?? "";
        $snapshoot['mobile_no'] = $this->order->user->telephone;
        $snapshoot['father_name'] = $userInfo->father_name ?? "";
        $snapshoot['pan_no'] = $this->order->userInfo->pan_card_no ?? "";

        $snapshoot['marital_status'] = $userInfo->marital_status ?? "";
        $snapshoot['marital_status_select'] = '';
        $snapshoot['emergency_contact1'] = $userContact[0] ?? '';
        $snapshoot['emergency_contact1_select'] = '';
        $snapshoot['emergency_contact2'] = $userContact[1] ?? '';
        $snapshoot['emergency_contact2_select'] = '';
        $snapshoot['monthly_income'] = $userWork->salary ?? "";
        $snapshoot['monthly_income_select'] = '';
        $snapshoot['englist_level_select'] = '';

        $snapshoot['approveResult'] = '';
        $snapshoot['version'] = CommonService::getInstance()->getOrderVersion();
        $snapshoot['riskResult'] = '';

        return $snapshoot;
    }

    /**
     * @return array
     */
    protected function getLoanInformation() {
        $data = [
            'application_time' => '',
            'debit_bank' => '',
            'ifsc' => '',
            'debit_account' => '',
        ];

        $bank = $this->order->user->bankCard;
        if ($bank->exists) {
            $data['debit_bank'] = $bank->bank;
            $data['debit_account'] = $bank->no;
            $data['ifsc'] = $bank->ifsc;
            $data['application_time'] = DateHelper::dateFormatByEnv($this->order->created_time / 1000);
        }

        return $data;
    }

    public function addCallLog(ApproveUserPool $userPool, $params) {
        if ($userPool) {
            $insert = [
                "approve_pool_id" => $userPool->approve_pool_id,
                "approve_user_pool_id" => $userPool->id,
                "admin_id" => LoginHelper::helper()->getAdminId(),
                "name" => array_get($params, "name"),
                "telephone" => array_get($params, "telephone"),
                "order_id" => $userPool->order_id,
                "tv1" => array_get($params, "tv1"),
                "tv2" => array_get($params, "tv2"),
                "note" => array_get($params, "note", "")
            ];
            if (!isset($params['id'])) {
                $res = ApproveCallLog::model()->createModel($insert);
            } else {
                $res = ApproveCallLog::model()->updateOrCreateModel($insert, ["id" => $params['id']]);
            }
            return $res;
        }
        return false;
    }

    public function callLogList($orderId) {
        return ApproveCallLog::model()->newQuery()->where("order_id", $orderId)->get();
    }

}
