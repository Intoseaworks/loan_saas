<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2018-12-13
 * Time: 14:36
 */

namespace Approve\Admin\Services\Approval;


use Admin\Services\Staff\StaffServer;
use Approve\Admin\Config\Params;
use Approve\Admin\Services\CommonService;
use Approve\Admin\Services\Log\ApproveLogService;
use Common\Exceptions\CustomException;
use Common\Models\Approve\ApproveFailRecord;
use Common\Models\Approve\ApprovePageStatistic;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ApproveResultSnapshot;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Common\Config;
use Common\Models\Common\Pincode;
use Common\Models\Order\Order;
use Common\Models\Third\ThirdPartyVerify;
use Common\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Models\User\UserThirdData;
use Common\Traits\GetInstance;
use Common\Utils\CityHelper;
use Common\Utils\Code\ApproveStatusCode;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\Upload\ImageHelper;

class FirstApproveService
{
    use GetInstance;

    /**
     * 人审通过待签约
     *
     * @var string
     */
    const PASS = 'manual_approve_pass';

    /**
     *  待补充资料
     *
     * @var string
     */
    const SUPPLEMENTARY = 'manual_approve_supplementary';

    /**
     * 人工已拒绝
     *
     * @var string
     */
    const FRAUD = 'manual_approve_fraud';

    /**
     * @var Order|\Illuminate\Database\Eloquent\Model|null
     */
    protected $order = null;

    /**
     * @var array|bool
     */
    protected $riskData = [];

    /**
     * @var array
     */
    protected $userFiles = [];

    /**
     * @var array
     */
    protected $originUserFiles = [];

    /**
     * FirstApprove constructor.
     *
     * @param $orderId
     * @throws CustomException
     */
    public function __construct($orderId)
    {
        $order = Order::model()->getOne($orderId);
        $this->order = $order;
        if (!$this->order) {
            throw new CustomException(t('order not found'), ApproveStatusCode::ORDER_NOT_FOUND);
        }

        $files = static::getFile($order->user_id, $order->order_id);
        $this->userFiles = $files['data'];
        $this->originUserFiles = $files['origin'];
    }

    /**
     * @param $userId
     * @param $orderId
     * @return array
     */
    public static function getFile($userId, $orderId)
    {
        /** @var \Admin\Models\User\User $userModel */
        $userModel = User::where(['id' => $userId])->first();
        $files = Upload::where(['user_id' => $userId, 'status' => Upload::STATUS_NORMAL])->get();

        $data = [
            'user_pdf' => [],
            'user_pay_slip' => [],
            'pay_slip_password' => '',
            'user_employee_card' => [
                'return' => [],
                'origin' => [],
            ],
            'bill_password' => '',
            'user_face' => [
                'return' => [],
                'origin' => [],
            ],
        ];

        $origin = $data;

        $userFaceType = [
            Upload::TYPE_PAN_CARD => t('pan card', 'approve'),
            Upload::TYPE_FACES => t('人脸识别照片', 'approve'),
            Upload::TYPE_PASSPORT_IDENTITY => t('护照正面', 'approve'),
            Upload::TYPE_PASSPORT_DEMOGRAPHICS => t('护照反面', 'approve'),
            Upload::TYPE_VOTER_ID_CARD_FRONT => t('身份证正面', 'approve'),
            Upload::TYPE_VOTER_ID_CARD_BACK => t('身份证反面', 'approve'),
            Upload::TYPE_DRIVING_LICENSE_FRONT => t('驾驶证正面', 'approve'),
            Upload::TYPE_DRIVING_LICENSE_BACK => t('驾驶证反面', 'approve'),
            Upload::TYPE_AADHAAR_CARD_FRONT => t('aadhaar正面', 'approve'),
            Upload::TYPE_AADHAAR_CARD_BACK => t('aadhaar反面', 'approve'),
            Upload::TYPE_AADHAAR_CARD_KYC => t('ekyc照片', 'approve'),
        ];

        $workCardType = [
            Upload::TYPE_WORK_CARD_BACK => t('工作证反面', 'approve'),
            Upload::TYPE_WORK_CARD_FRONT => t('工作证正面', 'approve'),
        ];
        $k = -1;
        //var_dump($files->toArray());exit();

        foreach ($files as $file) {
            /** @var Upload $file */
            if (!$file->path) {
                continue;
            }

//            if ($file->type == Upload::TYPE_BILL && $userModel->getBankBillStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
//                $data['user_pdf'][$file->type][] = [
//                    'id' => $file->id,
//                    'pdf' => ImageHelper::getPicUrl($file->path, 0),
//                    'name' => pathinfo($file->path)['basename'],
//                    'password' => $file->getFilePassword(),
//                ];
//                $origin['user_pdf'][$file->type] = $file->path;
//                if ($file->getFilePassword()) {
//                    $origin['bill_password'] = $file->getFilePassword();
//                }
//            } else
            if (in_array($file->type, array_keys($userFaceType))) {
                $src = ImageHelper::getPicUrl($file->path, 0);
                // 判断选填项是否过期
                $flag = false;
                switch ($file->type) {
                    case Upload::TYPE_PAN_CARD:
                        if ($userModel->getPanCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_FACES:
                        if ($userModel->getFaceStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_PASSPORT_IDENTITY:
                    case Upload::TYPE_PASSPORT_DEMOGRAPHICS:
                        if ($userModel->getAddressPassportStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_VOTER_ID_CARD_FRONT:
                    case Upload::TYPE_VOTER_ID_CARD_BACK:
                        if ($userModel->getAddressVoterIdCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_DRIVING_LICENSE_FRONT:
                    case Upload::TYPE_DRIVING_LICENSE_BACK:
                        if ($userModel->getAddressDrivingLicenseStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_AADHAAR_CARD_FRONT:
                    case Upload::TYPE_AADHAAR_CARD_BACK:
                        if ($userModel->getAadhaarCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                    case Upload::TYPE_AADHAAR_CARD_KYC:
                        if ($userModel->getAadhaarCardKYCStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                            $flag = true;
                        }
                        break;
                }

                if ($flag) {
                    $data['user_face']['return'][] = [
                        'src' => $src,
                        'name' => $file->type,
                        'id' => ++$k,
                    ];
                    $origin['user_face']['return'][] = [
                        'src' => $file->path,
                        'name' => $file->type,
                        'id' => ++$k,
                    ];
                    $data['user_face']['origin'][$file->type] = $src;
                }
            } elseif ($file->type == Upload::TYPE_PAY_SLIP && $userModel->getPaySlipStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                $data['user_pay_slip'][$file->type] = ImageHelper::getPicUrl($file->path, 0);
                $origin['user_pay_slip'][$file->type] = $file->path;
                if ($file->getFilePassword()) {
                    $data['pay_slip_password'] = $file->getFilePassword();
                    $origin['pay_slip_password'] = $file->getFilePassword();
                }
            } elseif (in_array($file->type, array_keys($workCardType))) {
                $data['user_employee_card']['return'][] = [
                    'src' => ImageHelper::getPicUrl($file->path, 0),
                    'name' => $file->type,
                    'id' => ++$k,
                ];
                $origin['user_employee_card']['return'][] = [
                    'src' => $file->path,
                    'name' => $file->type,
                    'id' => ++$k,
                ];
                $data['user_employee_card']['origin'][$file->type] = ImageHelper::getPicUrl($file->path, 0);
            }
        }

        if (isset($data['user_pdf']['bill']) && is_array($data['user_pdf']['bill'])) {
            $data['user_pdf']['bill'] = [
                last($data['user_pdf']['bill']),
            ];
        }
        return ['data' => $data, 'origin' => $origin];
    }

    /**
     * 用户基础信息
     *
     * @return array
     */
    public function bashDetail()
    {
        //$this->checkOrderStatus();
        CommonService::getInstance()->getRealOrderStatus($this->order);

        $baseDetail = [];
        $order = $this->order;
        $userInfo = $order->userInfo;
        $user = $order->user;
//        $currentAddressInfo = CommonService::getInstance()->getAddress($userInfo->pincode, $user, 'current');
//        $permanentAddressInfo = CommonService::getInstance()->getAddress($userInfo->permanent_pincode, $user, 'permanent');
//        $riskData = $this->riskData;

        $upload = (new \Admin\Models\Upload\Upload())->getPathsByUserIdAndType($order->user->id, [
            Upload::TYPE_BUK_NIC,
            Upload::TYPE_BUK_PASSPORT,
            Upload::TYPE_BUK_NIC_BACK,
            Upload::TYPE_FACES
        ]);
        $fileUrls = [];
        $imgRelation = array_flip(Upload::TYPE_BUK);
        foreach ($upload as $k => $v) {
            $fileUrls[$k] = ImageHelper::getPicUrl($v, 600);
        }
        $baseDetail['clm_level'] = '--';
        $baseDetail['clm_amount'] = $order->principal;
        $baseDetail['clm_remark'] = '--';
        $riskResult = $order->riskStrategyResult();
        if($riskResult){
            $baseDetail['clm_level'] = $riskResult->score_rank;
            $baseDetail['clm_amount'] = $riskResult->sug_loan_amt && $riskResult->sug_loan_amt>0 ? $riskResult->sug_loan_amt: $order->principal;
            $baseDetail['clm_remark'] = $riskResult->remark;
        }
        $faceFile = array_get($fileUrls, Upload::TYPE_FACES);
//        $companyIdFile = array_get($fileUrls, Upload::TYPE_COMPANY_ID);
        /** 菲律宾多个身份证明 */
        $idCardFile = isset($imgRelation[$user->card_type]) ? array_get($fileUrls, $imgRelation[$user->card_type]):null;
        $idCardBackFile =  array_get($fileUrls, Upload::TYPE_BUK_NIC_BACK, NULL);
        
        # 如果对应证件找不到
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
        $baseDetail['order_no'] = $order->order_no;
        $baseDetail['telephone'] = $user->telephone;
        $baseDetail['fullname'] = $user->fullname;
        $baseDetail['fullname_radio_list'] = $this->getRadioList();
        // 用户图片
        $baseDetail['user_face'] = $faceFile;
        $baseDetail['user_face_comparison_selector'] = $this->getFaceCompareSelectorFile();
        $baseDetail['id_card'] = $idCardFile;
        $baseDetail['id_card_back'] = $idCardBackFile;
        $baseDetail['id_card_type'] = $user->card_type;
        $baseDetail['id_card_no'] = $user->id_card_no;
        $baseDetail['id_card_radio_list'] = $this->getRadioList();
        $baseDetail['company_id'] = $idCardBackFile;//$companyIdFile;
        $baseDetail['principal'] = $order->principal;
        $baseDetail['loan_days'] = $order->loan_days;
        $baseDetail['user_type'] = array_get(User::model()->getQuality(), $order->quality, '');
        $baseDetail['loan_times'] = $user->getRepeatLoanCnt();
        // date of birthday
        $baseDetail['birthday'] = $userInfo->birthday;
        $baseDetail['age'] = DateHelper::getAge($userInfo->birthday);
        // gender
        $baseDetail['gender'] = $userInfo->gender;
        $baseDetail['current_address'] = $userInfo->address;

        $baseDetail['order_status'] = CommonService::getInstance()->orderStatus($this->order->_status);
        $baseDetail['reason_for_failure'] = ApproveLogService::getInstance()->getFirstLog($order->id, ManualApproveLog::APPROVE_TYPE_MANUAL);
        $firstLogInstance = optional(ApproveLogService::getInstance()->getFirstLogInstance($order->id, ManualApproveLog::APPROVE_TYPE_MANUAL));
        $baseDetail['reason_for_remark'] = ArrayHelper::jsonToArray($firstLogInstance->remark);
//
//        // full name
//        $baseDetail['first_name'] = $order->user->fullname;
//        // father name
//        $baseDetail['father_name'] = $userInfo->father_name;
//        // user type
//        if($order->quality){
//            $prevOrder = OrderServer::server()->prevOrder($order);
//            if($prevOrder){
//                $baseDetail['prev_order_status'] = $prevOrder->status;
//            }
//        }
//        $baseDetail['user_max_overdue_days'] = OrderServer::server()->getMaxOverdueDays($order->user_id);
//        $baseDetail['current_address_info'] = [
//            'city' => $currentAddressInfo['city'] ?? '',
//            'exist' => $currentAddressInfo['exist'] ?? false,
//        ];
//        // pincode
//        $baseDetail['current_pin_code'] = $userInfo->pincode;
//        // permanent Address
//        $permanetnInfo = $this->getPermanentInfo();
//        $baseDetail['permanent_address'] = $permanetnInfo['permanent_address'];
//        // pincode
//        $baseDetail['permanent_pin_code'] = $permanetnInfo['permanent_pincode'];
//        // address pinco
//        $baseDetail['address'] = [
//            'city' => $permanetnInfo['permanent_pincode'] ? ($permanentAddressInfo['city'] ?? '') : '',
//            'exist' => $permanetnInfo['permanent_pincode'] ? ($permanentAddressInfo['exist'] ?? false) : false,
//        ];
//        $baseDetail['location_address'] = (new OrderDetail)->getLocation($order) ?: (new UserPosition())->getAddress($order->user_id);
//        // pan card
//        $baseDetail['pan_card_no'] = $user->userInfo->pan_card_no;
//        // passport No.
//        $baseDetail['passport_no'] = $userInfo->passport_no;
//        // passport File
//        $baseDetail['passport_file_list'] = $this->getPassportFileList();
//        // aadhaar
//        $baseDetail['aadhaar_card_no'] = $userInfo->aadhaar_card_no;
//        // aadhaar File
//        $baseDetail['aadhaar_file_list'] = $this->getAadhaarFileList();
//        // Driving licence No.
//        $baseDetail['driving_license_no'] = $userInfo->driving_license_no;
//        // Driving licence File
//        $baseDetail['driving_licence_file_list'] = $this->getDrivingLicenceFileList();
//        // voter Id No.
//        $baseDetail['voter_id_card_no'] = $userInfo->voter_id_card_no;
//        // appearance
//        $baseDetail['appearance_list'] = $this->getAppearanceList();
//        // environment
//        $baseDetail['environment_list'] = $this->getEnvironment();
//        // 认证项是否展示
//        static::optionalCertification($order, $baseDetail);
//
//        $list = $this->getFailListByUserAuth($baseDetail);
//        // approval failed
//        $baseDetail['approval_failed_list'] = $list['failed'];
//        // suspected fraud/disqualified
//        $baseDetail['suspected_fraud_list'] = $list['fraud'];
//
        // 提交选项
        $baseDetailStatus = [
            static::PASS => t('审批通过', 'approve'),
            static::FRAUD => t('审批不通过', 'approve'),
//            static::SUPPLEMENTARY => t('补件', 'approve'),
        ];
        $baseDetail['base_detail_status'] = ArrayHelper::arrToOption($baseDetailStatus, 'key', 'val');
        $baseDetail['refusal_code'] = $this->getRefusalCode(); //拒绝码
        //按新需求更改拒绝原因
        $reason_for_failure_change = false;
        foreach ($baseDetail['refusal_code'] as  $v){
            if ($order->refusal_code && $order->refusal_code==$v['key']){
                $baseDetail['reason_for_failure'] = $v['val'];
                $reason_for_failure_change = true;
                break;
            }
        }
        if (!$reason_for_failure_change) {
            $baseDetail['reason_for_failure'] = '';
        }
        $baseDetail['approveResult'] = $firstLogInstance->result;
        $baseDetail['approveUser'] = optional(StaffServer::findOneById($firstLogInstance->admin_id))->nickname;
        $baseDetail['approveTime'] = $firstLogInstance->created_at;
        $baseDetail['orderStatus'] = $firstLogInstance->from_order_status;
        $baseDetail['approveResult'] = $firstLogInstance->result;
        $baseDetail['suspected_fraud_status'] = $this->getSupplementSelector(); //补件选项
//        $baseDetail['base_detail_status_checked'] = $this->getLastApproveStatus($riskData['basicDetails']['approval_status'] ?? 0);
//        // e-KYC认证状态
//        $baseDetail['ekyc_auth_status'] = boolval($order->user->getAadhaarCardKYCStatus() == UserAuth::AUTH_STATUS_SUCCESS);
//        $baseDetail['aadhaar_verify_status'] = boolval((new UserThirdData())->getAadhaarVerityCheck($user->id));
        return $baseDetail;
    }

    /**
     * @return bool
     * @throws CustomException
     */
    protected function checkOrderStatus()
    {
        if (!in_array($this->order->status, [Order::STATUS_WAIT_MANUAL_APPROVE, Order::STATUS_REPLENISH])) {
            throw new CustomException(t('该订单不能进行审批'), ApproveStatusCode::ORDER_STATUS_INVALID);
        }
        return true;
    }

    /**
     * @return array
     */
    protected function getPermanentInfo()
    {
        $permanentInfo = [
            'permanent_address' => '',
            'permanent_pincode' => '',
        ];
        $userInfo = $this->order->userInfo;

        $permanentInfo['permanent_address'] = $userInfo->permanent_address;
        $permanentInfo['permanent_pincode'] = $userInfo->permanent_pincode;

        return $permanentInfo;

        if (!Config::model()->getHyperOcrIsOpen()) {
            $permanentInfo['permanent_address'] = $userInfo->permanent_address;
            $permanentInfo['permanent_pincode'] = $userInfo->permanent_pincode;
        } else {
            $userFIleIds = Upload::where(['user_id' => $this->order->user_id, 'status' => Upload::STATUS_NORMAL,])
                ->whereIn('type', [Upload::TYPE_AADHAAR_CARD_BACK, Upload::TYPE_DRIVING_LICENSE_BACK, Upload::TYPE_PASSPORT_DEMOGRAPHICS, Upload::TYPE_VOTER_ID_CARD_BACK])
                ->get()
                ->groupBy('type')
                ->map(function ($item) {
                    return $item->max('user_file_id');
                })
                ->values()
                ->toArray();

            $flag = false;
            // 如果订单是回退订单(第二次初审)这时候判断如果上次审批输入了永久居住地址那么永久居住地址展示出来
            $approveUserPool = ApproveUserPool::where('order_id', $this->order->order_id)
                ->whereIn('status', [ApproveUserPool::STATUS_FIRST_RETURN])
                ->orderBy('id', 'desc')
                ->first();
            if ($approveUserPool) {
                $snapshot = ApproveResultSnapshot::where('approve_user_pool_id', $approveUserPool->id)
                    ->where('approve_type', ApproveResultSnapshot::TYPE_FIRST_APPROVE)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($snapshot) {
                    $flag = !array_get(json_decode($snapshot->result, true), 'bashDetail.permanent_address') ?: true;
                }
            }

            // ocr地址认证成功才显示永久居住地址和Pincode
            $count = ThirdPartyVerify::whereIn('source_id', $userFIleIds)
                ->where(['status' => ThirdPartyVerify::STATUS_SUCCESS])
                ->count();
            if ($count > 0 || $flag) {
                $permanentInfo['permanent_address'] = $userInfo->permanent_address;
                $permanentInfo['permanent_pincode'] = $userInfo->permanent_pincode;
            }
        }

        return $permanentInfo;
    }

    /**
     * @return array
     */
    protected function getPassportFileList()
    {
        $data = $this->riskData;
        $checked = ($data['basicDetails']['passport_file'] ?? '') ?: '';

        $list = Params::firstApprove('passport_file_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * @return array
     */
    protected function getAadhaarFileList()
    {
        $data = $this->riskData;
        $checked = ($data['basicDetails']['aadhaar_file'] ?? '') ?: '';

        $list = Params::firstApprove('aadhaar_file_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * @return array
     */
    protected function getDrivingLicenceFileList()
    {
        $checked = ($this->riskData['basicDetails']['driving_license_file'] ?? '') ?: '';

        $list = Params::firstApprove('driving_licence_file_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * @return array
     */
    protected function getAppearanceList()
    {
        $checkedJson = ($this->riskData['basicDetails']['figure'] ?? '{}') ?: '{}';
        $checked = json_decode($checkedJson, true);

        $list = Params::firstApprove('appearance_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * @return array
     */
    protected function getEnvironment()
    {
        $checked = $this->riskData['basicDetails']['environment'] ?? '';

        $list = Params::firstApprove('face_ambient_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * 选择项认证
     *
     * @param Order $order
     * @param $baseDetail
     * @return void
     */
    public static function optionalCertification(Order $order, &$baseDetail)
    {

        $user = $order->user;
        // 如果接口有返回住址信息则不显示
        $baseDetail['permanent_address_show'] = true;

        // pan card
        $panUserThirdDateType = [UserThirdData::TYPE_PAN_CARD];
        $baseDetail['pan_card_status'] = static::checkUserCertification($order->user, $panUserThirdDateType, UserAuth::TYPE_PAN_CARD);

        // passport no
        $passportUserThirdDataType = [UserThirdData::TYPE_PASSPORT];
        $baseDetail['passport_status'] = static::checkUserCertification($order->user, $passportUserThirdDataType, UserAuth::TYPE_ADDRESS_PASSPORT);

        // aadhaar no
        $aadhaaruserThirdDataType = [UserThirdData::TYPE_AADHAAR];
        $baseDetail['aadhaar_status'] = static::checkUserCertification($order->user, $aadhaaruserThirdDataType, UserAuth::TYPE_AADHAAR_CARD);

        // driving licence
        $DLThridDataType = [UserThirdData::TYPE_DRIVING_LICENCE];
        $baseDetail['driving_license_status'] = static::checkUserCertification($order->user, $DLThridDataType, UserAuth::TYPE_ADDRESS_DRIVING, false);

        // voter id
        $voterUserThirdDataType = [UserThirdData::TYPE_VOTER_ID];
        $baseDetail['voter_id_status'] = static::checkUserCertification($order->user, $voterUserThirdDataType, UserAuth::TYPE_ADDRESS_VOTER);
    }

    /**
     * 认证项检查 true 需要认证 false 已认证
     *
     * @param User $user
     * @param $type
     * @param $userAuthType
     * @param null $edit
     * @return array
     */
    public static function checkUserCertification(User $user, $type, $userAuthType, $edit = null)
    {
        $return = [
            // 是否展示
            'show' => false,
            // 是否可编辑
            'edit' => false,
        ];
        // 存在一种情况:用户创建订单之前资料是有效的,进入审批系统后资料变为无效.所以这里判断 user_auth_time的认证项时间 > 0就可以了
        if (!UserAuth::where(['user_id' => $user->id, 'type' => $userAuthType])->exists()) {
            return $return;
        }

        // 验证时间
        if (!$userAuth = (new UserAuth())->getAuth($user->id, $userAuthType)) {
            return $return;
        }
        $authTime = strtotime($userAuth->time) - 300;
        $authCount = UserThirdData::where('user_id', $user->id)
            ->whereIn('type', $type)
            ->where('updated_at', '>', date('Y-m-d H:i:s', $authTime))
            ->count();

        // aadhaarApi识别完成,不可编辑
        if ($authCount >= count($type)) {
            $return['show'] = true;
            return $return;
        }

        $userFileType = [];
        foreach ($type as $item) {
            if (isset(UserThirdData::USER_FILE_MAP[$item])) {
                array_push($userFileType, ...UserThirdData::USER_FILE_MAP[$item]);
            }
        }
        // aadhaarApi未识别
        $fileCount = Upload::where('user_id', $user->id)
            ->whereIn('type', $userFileType)
            ->where('status', Upload::STATUS_NORMAL)
            ->count();

        // 用户上传文件,审批人员审核
        if ($fileCount >= count($userFileType)) {
            $return['show'] = true;
            if (!is_null($edit)) {
                $return['edit'] = $edit;
            } else {
                $return['edit'] = true;
            }
            return $return;
        }

        return $return;
    }

    /**
     * @param $baseDetail
     * @return array
     */
    protected function getFailListByUserAuth($baseDetail)
    {
        // 值具体查看 Params::getFirstApprove('base_detail_failed_list/base_detail_suspected_fraud_list')
        $map = [
            'failed' => [
                '_common' => [11],
                'pan_card_status' => [1],
                'passport_status' => [8, 9, 10],
                'aadhaar_status' => [6, 7],
                'driving_license_status' => [4, 5, 12],
                'voter_id_status' => [2, 3],
            ],
            'fraud' => [
                '_common' => [7, 10],
                'pan_card_status' => [1, 4],
                'passport_status' => [3, 6],
                'aadhaar_status' => [9],
                'driving_license_status' => [8],
                'voter_id_status' => [5],
            ],
        ];

        $authData = array_only($baseDetail, ['pan_card_status', 'passport_status', 'aadhaar_status', 'driving_license_status', 'voter_id_status']);
        $failed = [];
        $fraud = [];
        foreach ($authData as $k => $auth) {
            if ($auth['show']) {
                empty($map['failed'][$k]) ?: array_push($failed, ...$map['failed'][$k]);
                empty($map['fraud'][$k]) ?: array_push($fraud, ...$map['fraud'][$k]);
            }
        }

        empty($map['failed']['_common']) ?: array_push($failed, ...$map['failed']['_common']);
        empty($map['fraud']['_common']) ?: array_push($fraud, ...$map['fraud']['_common']);

        return [
            'failed' => [
                'list' => ArrayHelper::arrToOption(array_intersect_key($this->getApproveFailedList(false)['list'], array_flip($failed)), 'key', 'val')
            ],
            'fraud' => [
                'list' => ArrayHelper::arrToOption(array_intersect_key($this->getSuspectedFraudList(false)['list'], array_flip($fraud)), 'key', 'val')
            ],

        ];
    }

    /**
     * @param bool $format
     * @return array
     */
    protected function getApproveFailedList($format = true)
    {
        $list = Params::firstApprove('base_detail_failed_list');

        if ($format) {
            return [
                'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            ];
        } else {
            return [
                'list' => $list,
            ];
        }

    }

    /**
     * @param bool $format
     * @return array
     */
    protected function getFaceCompareSelectorFile($format = true)
    {
        $list = Params::firstApprove('face_compare_selector_list');

        if ($format) {
            return [
                'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            ];
        } else {
            return [
                'list' => $list,
            ];
        }

    }

    /**
     * @return array
     */
    protected function getRadioList()
    {
        $list = Params::callApprove('normal_status_list');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * 初审拒绝码列表
     * @return array
     */
    protected function getRefusalCode()
    {
        $list = Params::firstApprove('refusal_code');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * 补件选项
     * @return array
     */
    protected function getSupplementSelector()
    {
        $list = Params::firstApprove('suspected_fraud_status');

        return ArrayHelper::arrToOption($list, 'key', 'val');
    }

    /**
     * @param bool $format
     * @return array
     */
    protected function getSuspectedFraudList($format = true)
    {
        $list = Params::firstApprove('base_detail_suspected_fraud_list');

        if ($format) {
            return [
                'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            ];
        } else {
            return [
                'list' => $list,
            ];
        }
    }

    /**
     * @param $status
     * @return int
     */
    protected function getLastApproveStatus($status)
    {
        switch ($status) {
            case 1:
                return static::PASS;
            case 2:
                return static::FRAUD;
            default:
                return -1;
        }
    }

    /**
     * @return array
     */
    public function getOriginUserFiles(): array
    {
        return $this->originUserFiles;
    }

    /**
     * @return array
     */
    public function getUserFiles(): array
    {
        return $this->userFiles;
    }

    /**
     * @param $data
     * @return bool|mixed
     * @throws CustomException
     */
    public function submit($data)
    {
        $this->checkOrderStatus();

        $order = $this->order;
        if (!empty($data['fullname'])) {
            // 去除中间空格
            $data['fullname'] = StringHelper::spacesToSpace($data['fullname']);
        }

        /** 拒绝时不更新资料 */
        if ( isset($data['base_detail_status']) && $data['base_detail_status']==FirstApproveService::PASS ){
            $return = SubmitService::updateUserInfo($order, $data);
            if ($return == 'Card number update failed, failed reason: already exist') {
                return $return;
            }
        }

        /** 统计页面耗时信息Start */
        $commonService = CommonService::getInstance();
        $statisticRedisKey = $commonService->getStartCheckRedisKey($data['id']);
        $cost = $commonService->getRedis()->get($statisticRedisKey) ?: time() - 1;
        $statisticCost = [
            'merchant_id' => $order->merchant_id,
            'order_id' => $order->id,
            'approve_user_pool_id' => $data['id'],
            'type' => ApprovePageStatistic::TYPE_FIRST_APPROVE,
            'admin_id' => $data['admin_id'],
            'cost' => time() - (int)$cost,
            'page' => ApprovePageStatistic::CASHNOW_PAGE_BASE_DETAIL,
        ];
        ApprovePageStatistic::saveData($statisticCost);
        /** 统计页面耗时信息End */

        $approveStatus = array_get($data, 'base_detail_status'); //审批结果选项
        $refusalCode = array_get($data, 'refusal_code'); //审批拒贷码
        $remarkArray = array_only(array_filter($data), [ //人工点选项集合
            'confirm_selfie_id_phone',
            'confirm_selfie_work_phone',
            'confirm_fullname',
            'confirm_id_card',
            'remark'
        ]);
        $userTimeClear = array_get($data, 'suspected_fraud_status');
        $statusCode = SubmitService::getInstance()->firstApproveSubmit(
            $order->id,
            $approveStatus,
            $refusalCode,
            $remarkArray,
            $userTimeClear
        );

        $commonService->getRedis()->del($statisticRedisKey);

        // 保存回退项
        $this->saveFailRecord($data);

        // 设置统计生效
        ApprovePageStatistic::where('approve_user_pool_id', $data['id'])
            ->update(['status' => ApprovePageStatistic::STATUS_EFFECTIVE]);

        return [
            'snapshoot' => $this->makeSnapshoot($data, $statusCode),
            'code' => $statusCode,
        ];

    }

    /**
     * @param $data
     */
    protected function updatePincode($data)
    {
        $hander = function ($key, $pincode) use ($data) {
            $pincodeModel = Pincode::wherePincode($pincode)->first();
            if (($pincodeModel && $pincodeModel->type == Pincode::TYPE_MANUAL && $data[$key]) || (!$pincodeModel && $data[$key])) {
                $pincodeInfo = array_map('trim', explode('/', $data[$key] ?? ''));
                $data = [
                    'pincode' => $pincode,
                    'statename' => $pincodeInfo[0],
                    'districtname' => $pincodeInfo[1],
                    'type' => Pincode::TYPE_MANUAL,
                ];
                Pincode::updateOrInsert(['pincode' => $pincode], $data);
            }
        };
        $hander('current_region', $data['current_pin_code']);
        $hander('permanent_region', $data['permanent_pin_code']);
    }

    /**
     * @param $data
     * @return array
     */
    protected function getSubmitStatus($data)
    {
        $statusKeys = [
            WorkFlowService::BASE_DETAIL => 'base_detail_status',
        ];
        $flows = WorkFlowService::getInstance()->setFisrtApproveWorkFlow();

        $list = [];
        foreach (array_reverse($flows['flows']) as $flow) {
            $list[] = [
                'statusKey' => $statusKeys[$flow],
                'page' => $flow,
            ];
        }

        foreach ($list as $item) {
            if (isset($data[$item['statusKey']])) {
                return ['status' => $data[$item['statusKey']], 'page' => $item['page']];
            }
        }

        return ['status' => -1, 'page' => ''];
    }

    /**
     * @param Order $order
     * @return array
     */
    public static function getOptionalCertValue(Order $order)
    {
        $data = request()->all();
        $certifications = [];
        $riskData = [];
        static::optionalCertification($order, $certifications);

        if ($certifications['pan_card_status']['edit'] && $certifications['pan_card_status']['show']) {
            $riskData['pan_card_no'] = $data['pan_card_no'] ?? '';
        } else {
            $riskData['pan_card_no'] = $order->userInfo->pan_card_no;
        }

        if ($certifications['passport_status']['edit'] && $certifications['passport_status']['show']) {
            $riskData['passport_no'] = $data['passport_no'] ?? '';
        } else {
            $riskData['passport_no'] = $order->userInfo->passport_no;
        }

        if ($certifications['aadhaar_status']['edit'] && $certifications['aadhaar_status']['show']) {
            $riskData['aadhaar_card_no'] = $data['aadhaar_card_no'] ?? '';
        } else {
            $riskData['aadhaar_card_no'] = $order->userInfo->aadhaar_card_no;
        }

        if ($certifications['driving_license_status']['edit'] && $certifications['driving_license_status']['show']) {
            $riskData['driving_license_no'] = $data['driving_license_no'] ?? '';
        } else {
            $riskData['driving_license_no'] = $order->userInfo->driving_license_no;
        }

        if ($certifications['voter_id_status']['edit'] && $certifications['voter_id_status']['show']) {
            $riskData['voter_id_card_no'] = $data['voter_id_card_no'] ?? '';
        } else {
            $riskData['voter_id_card_no'] = $order->userInfo->voter_id_card_no;
        }

        return $riskData;
    }

    /**
     * 风控基础资料
     *
     * @param $data
     * @param $optionalCertData
     * @return array|bool
     */
    protected function riskBaseDetail($data, $optionalCertData)
    {
        $statusList = $this->getApproveStepStatus($data);
        $baseDetailStatus = $statusList['base_detail_status'];
        if (!is_null($baseDetailStatus)) {
            $order = $this->order;
            // 需要修改用户认证项
            $userTimeClear = [];
            // 基本资料审核不通过
            $failedStatus = $data['approval_failed_status'] ?? [];
            // 诈骗
            $suspectedFraudStatus = $data['suspected_fraud_status'] ?? [];
            // 基础资料审核失败原因
            $remarkArray = [];
            $otherRemark = '';
            // 待补充资料
            if ($baseDetailStatus == static::SUPPLEMENTARY) {
                $faileList = $this->getApproveFailedList(false)['list'];
                foreach ($failedStatus as $v) {
                    if (isset($faileList[$v])) {
                        $remarkArray[ManualApproveLog::REMARK_KEY_MAP[$v]] = $faileList[$v];
                        // 更新用户过期证件信息
                        if (in_array($v, array_keys(UserAuth::BASE_DETAIL_FAILED_LIST))) {
                            $userTimeClear[] = UserAuth::BASE_DETAIL_FAILED_LIST[$v];
                        }
                    }
                }
            }

            // 诈骗
            if ($baseDetailStatus == static::FRAUD) {
                $fraudList = $this->getSuspectedFraudList(false)['list'];
                foreach ($suspectedFraudStatus as $v) {
                    if (isset($fraudList[$v])) {
                        // other选项值
                        /*if ($v == 10) {
                            $remarkArray[] = array_get($data, 'suspected_fraud_other_remark', '');
                        } else {
                            $remarkArray[] = $fraudList[$v];
                        }*/
                        $remarkArray[] = $fraudList[$v];
                        if ($v == 10) {
                            $otherRemark = array_get($data, 'suspected_fraud_other_remark', '');
                        }
                    }
                }
            }

            // pancard 不清晰 具体查看 Params::getFirstApprove('appearance_list')
            $appearanceList = Params::firstApprove('appearance_list');
            $figureList = [4, 5];
            $figure = $data['appearance_status'] ?? [];
            // 戴面纱
            foreach ($figureList as $item) {
                if ((in_array($item, $figure))) {
                    $remarkArray[] = $appearanceList[$item];
                }
            }

            $userInfo = $order->userInfo;

            $baseDetail = [
                'basic_details' => [
                    'full_name' => $order->user->fullname,
                    'gender' => $userInfo->gender,
                    'date_of_birth' => $userInfo->birthday,
                    'father_name' => $userInfo->father_name,
                    'state' => $userInfo->state,
                    'city' => $userInfo->city,
                    'current_address' => $userInfo->address,
                    'pin_code' => $userInfo->pincode,
                    'pan_card' => $optionalCertData['pan_card_no'],
                    'passport_no' => $optionalCertData['passport_no'],
                    'passport_file' => $data['passport_status'] ?? 0,
                    'aadhaar_card' => $optionalCertData['aadhaar_card_no'],
                    'aadhaar_file' => $data['aadhaar_card_status'] ?? 0,
                    'driving_license_no' => $optionalCertData['driving_license_no'],
                    'driving_license_file' => $data['driving_license_status'] ?? 0,
                    'voter_id_no' => $optionalCertData['voter_id_card_no'],
                    'environment' => $data['environment_status'] ?? 0,
                    'figure' => $data['appearance_status'] ?? [],
                    'approval_status' => $baseDetailStatus == static::PASS ? 1 : 2,
                ]
            ];

            return [
                'baseDetail' => $baseDetail,
                'remark' => $remarkArray,
                'userTimeClear' => $userTimeClear,
                'otherRemark' => $otherRemark,
            ];

        } else {

            return false;
        }

    }

    /**
     * 获取审批状态
     *
     * @return array
     */
    protected function getApproveStepStatus($data)
    {
        $status = [
            'base_detail_status' => null,
        ];

        foreach ($status as $k => $item) {
            if (isset($data[$k]) && $data[$k]) {
                $status[$k] = $data[$k];
            }
        }

        return $status;
    }

    /**
     * @param $data
     */
    protected function saveFailRecord($data)
    {
        $status = $data['base_detail_status'];
        $insert = [];
        $handler = function ($data, $options) use (&$insert) {
            foreach ($options as $option) {
                $insert[] = array_merge($data, ['option_value' => $option]);
            }
        };
        $row = [
            'merchant_id' => $this->order->merchant_id,
            'order_id' => $data['order_id'],
            'user_id' => $data['admin_id'],
            'type' => ApproveFailRecord::TYPE_MANUAL_APPROVE,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($status == static::FRAUD) {
            $row['option_type'] = ApproveFailRecord::OPTION_TYPE_SUSPECTED;
            $handler($row, $data['suspected_fraud_status'] ?? []);
        } elseif ($status == static::SUPPLEMENTARY) {
            $row['option_type'] = ApproveFailRecord::OPTION_TYPE_FAILED;
            $handler($row, $data['approval_failed_status'] ?? []);
        }

        if ($insert) {
            ApproveFailRecord::insert($insert);
        }
    }

    /**
     * @param $data
     * @param $statusCode
     * @return mixed
     */
    protected function makeSnapshoot($data, $statusCode)
    {
        $baseDetail = [];
        $user = $this->order->user;
        $order = $this->order;
        $userInfo = $this->order->userInfo;
        $upload = (new \Admin\Models\Upload\Upload())->getPathsByUserIdAndType($order->user->id, [
            Upload::TYPE_BUK_NIC,
            Upload::TYPE_BUK_PASSPORT,
        ]);
        $imgRelation = array_flip(Upload::TYPE_BUK);
        $faceFile = array_get($upload, Upload::TYPE_FACES);
        $companyIdFile = array_get($upload, Upload::TYPE_COMPANY_ID);
        /** 菲律宾多个身份证明 */
        $idCardFile = array_get($upload, $imgRelation[$user->card_type]);
        $baseDetail['order_no'] = $order->order_no;
        $baseDetail['telephone'] = $user->telephone;
        $baseDetail['fullname'] = $user->fullname;
        // 用户图片
        $baseDetail['user_face'] = $faceFile;
        $baseDetail['id_card'] = $idCardFile;
        $baseDetail['id_card_type'] = $user->card_type;
        $baseDetail['id_card_no'] = $user->id_card_no;
        $baseDetail['company_id'] = $companyIdFile;
        $baseDetail['principal'] = $order->principal;
        $baseDetail['loan_days'] = $order->loan_days;
        $baseDetail['user_type'] = array_get(User::model()->getQuality(), $order->quality, '');
        $baseDetail['loan_times'] = $user->getRepeatLoanCnt();
        // date of birthday
        $baseDetail['birthday'] = $userInfo->birthday;
        $baseDetail['age'] = DateHelper::getAge($userInfo->birthday);
        // gender
        $baseDetail['gender'] = $userInfo->gender;
        $baseDetail['current_address'] = $userInfo->address;
        $baseDetail['confirm_selfie_id_phone'] = array_get($data, 'confirm_selfie_id_phone');
        $baseDetail['confirm_selfie_work_phone'] = array_get($data, 'confirm_selfie_work_phone');

        $result = $this->getApproveResultText($data, $statusCode);

        $snapshoot = [
            WorkFlowService::BASE_DETAIL => $baseDetail,
            'approveResult' => $result['approveResult'],
            'riskResult' => $result['riskResult'],
        ];

        $snapshoot['flows'] = $this->getWorkFlow($snapshoot);
        $snapshoot['version'] = CommonService::getInstance()->getOrderVersion();

        return $snapshoot;
    }

    /**
     * @param array|string $key
     * @param array|int $checked
     * @return string
     */
    protected function getSelectedMsg($key, $checked)
    {
        if (!is_array($key)) {
            $list = Params::firstApprove($key);
        } else {
            $list = $key;
        }

        if (!is_array($checked)) {
            $checked = (array)$checked;
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
    protected function getApproveResultText($data, $statusCode)
    {
        // 获取提交的状态
        $result = $this->getSubmitStatus($data);
        $resultText = '';
        $riskText = '';
        // 审批通过
        if ($result['status'] == static::PASS) {
            $resultText = 'Approval PASS';
            // 机审拒绝
            if ($statusCode['approveResult'] == ApprovePoolLog::FIRST_APPROVE_RESULT_RISK_REJECT) {
                $riskText = t('机审拒绝') . '-' . t('居住城市不是目标城市/条件不达标');
            } else {
                $riskText = t('机审通过');
            }
        }
        switch ($result['page']) {
            case WorkFlowService::BASE_DETAIL:
                if ($result['status'] == static::SUPPLEMENTARY) {
                    $text = $data['suspected_fraud_status'];
                    $resultText = t('初审回退') . '-' . $text;
                }
                if ($result['status'] == static::FRAUD) {
                    $text = '';
//                    // other选项值
//                    if (in_array(10, $data['suspected_fraud_status'])) {
//                        $text .= 'other:' . array_get($data, 'suspected_fraud_other_remark', '') . ',';
//                        $data['suspected_fraud_status'] = array_diff($data['suspected_fraud_status'], [10]);
//                    }
//                    $text .= $this->getSelectedMsg('base_detail_suspected_fraud_list', $data['suspected_fraud_status'] ?? -1);
                    $resultText = 'Suspected fraud/disqualified - ' . $text;
                }
                break;
        }

        return ['approveResult' => $resultText, 'riskResult' => $riskText];
    }

    /**
     * @param $data
     * @return array
     */
    protected function getWorkFlow($data)
    {
        $emptyRemoveList = WorkFlowService::getInstance()->getFirstEmptyRemoveList();
        $flows = array_flip(WorkFlowService::FIRST_WORK_FLOW);
        foreach ($emptyRemoveList as $k => $item) {
            if (isset($flows[$k]) && empty($data[$k][$item])) {
                unset($flows[$k]);
            }
        }
        $flows = array_keys($flows);
        return array_combine($flows, $flows);
    }

    /**
     * @return array
     */
    public function initSnapshoot()
    {
        $userFiles = $this->originUserFiles;
        $userInfo = $this->order->userInfo;
        $snapshoot = [
            WorkFlowService::BASE_DETAIL =>
                [
                    'user_face' => [],
                    'order_no' => '',
                    'first_name' => '',
                    'father_name' => '',
                    'gender' => '',
                    'birthday' => '',
                    'user_type' => '',
                    'current_address' => '',
                    'current_pin_code' => '',
                    'permanent_address' => '',
                    'permanent_pin_code' => '',
                    'pan_card_no' => '',
                    'passport_no' => '',
                    'passport_file_checked' => '',
                    'aadhaar_card_no' => '',
                    'aadhaar_card_file_checked' => '',
                    'driving_license_no' => '',
                    'driving_licence_file_checked' => '',
                    'voter_id_card_no' => '',
                    'environment_checked' => '',
                    'appearance_checked' => '',
                ],
            'approveResult' => '',
            'riskResult' => '',
        ];

        $baseDetail = WorkFlowService::BASE_DETAIL;
        $snapshoot[$baseDetail]['user_face'] = $userFiles['user_face']['return'];
        $snapshoot[$baseDetail]['order_no'] = $this->order->order_no;
        $snapshoot[$baseDetail]['first_name'] = $this->order->user->fullname;
        $snapshoot[$baseDetail]['father_name'] = $userInfo->father_name ?? "";
        $snapshoot[$baseDetail]['gender'] = $userInfo->gender ?? "";
        $snapshoot[$baseDetail]['birthday'] = $userInfo->birthday ?? "";
        $snapshoot[$baseDetail]['user_type'] = array_get(User::model()->getQuality(), $this->order->quality, '');
        // 当前居住地址
        $state = $userInfo->province ?? "";
        $city = $userInfo->city ?? "";
        $address = $userInfo->address ?? "";
        $snapshoot[$baseDetail]['current_address'] = "{$state}/{$city}/{$address}";
        $snapshoot[$baseDetail]['current_pin_code'] = $userInfo->pincode ?? "";
        // 永久居住地址
        $state = $userInfo->permanent_province ?? "";
        $city = $userInfo->permanent_city ?? "";
        $address = $userInfo->permanent_address ?? "";
        $snapshoot[$baseDetail]['permanent_address'] = "{$state}/{$city}/{$address}";
        $snapshoot[$baseDetail]['permanent_pin_code'] = $userInfo->permanent_pincode ?? "";
        $snapshoot[$baseDetail]['pan_card_no'] = $userInfo->pan_card_no ?? "";
        $snapshoot[$baseDetail]['passport_no'] = $userInfo->passport_no ?? "";
        $snapshoot[$baseDetail]['aadhaar_card_no'] = $userInfo->aadhaar_card_no ?? "";
        $snapshoot[$baseDetail]['driving_license_no'] = $userInfo->driving_license_no ?? "";
        $snapshoot[$baseDetail]['voter_id_card_no'] = $userInfo->voter_id_card_no ?? "";
        static::optionalCertification($this->order, $snapshoot[$baseDetail]);

        $snapshoot['flows'] = $this->getWorkFlow($snapshoot);
        $snapshoot['version'] = CommonService::getInstance()->getOrderVersion();

        return $snapshoot;
    }

    /**
     * 城市列表
     *
     * @return array
     */
    protected function getCityList()
    {
        $cities = CityHelper::getInstance()->getCityList();
        $tree = [];
        foreach ($cities as $state => $city) {
            $temp = [
                'value' => $state,
                'label' => $state,
            ];
            $children = [];
            foreach ($city as $item) {
                $children[] = [
                    'value' => $item,
                    'label' => $item,
                ];
            }
            $temp['children'] = $children;

            $tree[] = $temp;
        }

        return $tree;
    }

    /**
     * @return array
     */
    protected function getVoterIdCardStautsList()
    {
        $data = $this->riskData;
        $checkedJson = ($data['basicDetails']['voter_id_card_status'] ?? '{}') ?: '{}';
        $checked = json_decode($checkedJson, true);

        $list = Params::firstApprove('voter_id_card_stauts_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $checked,
        ];
    }

    /**
     * @param $param
     * @return array
     */
    protected function getNormalStatusList($param)
    {
        $list = Params::firstApprove('normal_status_list');

        return [
            'list' => ArrayHelper::arrToOption($list, 'key', 'val'),
            'checked' => $param,
        ];
    }
}
