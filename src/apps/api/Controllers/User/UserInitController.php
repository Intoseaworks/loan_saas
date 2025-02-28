<?php

namespace Api\Controllers\User;

use Api\Models\Coupon\Coupon;
use Api\Models\Order\Order;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Rules\Order\OrderRule;
use Api\Rules\User\UserInfoRule;
use Api\Rules\User\UserInitRule;
use Api\Rules\User\UserSurveyRule;
use Api\Services\Bankcard\BankcardPesoServer;
use Api\Services\Order\OrderDetailServer;
use Api\Services\Order\OrderServer;
use Api\Services\Upload\UploadServer;
use Api\Services\User\UserAuthServer;
use Api\Services\User\UserInfoServer;
use Auth;
use Carbon\Carbon;
use Closure as BaseClosure;
use Common\Models\Common\Detention;
use Common\Models\Config\LoanMultipleConfig;
use Common\Models\Coupon\CouponReceive;
use Common\Models\NewClm\ClmSelectLog;
use Common\Models\Upload\Upload;
use Common\Models\User\UserInitStep;
use Common\Models\User\UserSurvey;
use Common\Response\ApiBaseController;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\NewClm\ClmServer;
use Common\Services\RepaymentPlan\CalcRepaymentSubjectServer;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;

class UserInitController extends ApiBaseController {

    public function stepOne(UserInitRule $rule) {
        $params = $this->getParams();
        $ruleName = UserInitRule::STEP_ONE;
        if (!isset($params['fullname'])) {
            $ruleName = UserInitRule::STEP_ONE_B;
        }
        if (!$rule->validate($ruleName, $params)) {
            return $this->resultFail($rule->getError());
        }
        $userModel = $this->identity();
        if (isset($params['fullname'])) {
            $userModel->fullname = trim($params['fullname']);
        } else {
            $userModel->firstname = trim($params['firstname']);
            $userModel->lastname = trim($params['lastname']);
            $userModel->fullname = $userModel->firstname . " " . $userModel->lastname;
        }
        $userModel->save();
        $params['birthday'] = DateHelper::formatToDate($params['birthday'], 'd/m/Y');
        $facebook_messager = array_get($params, 'facebook_messager', '');
        if ($facebook_messager) {
            $params['social_info'] = ['facebook_messager' => $facebook_messager];
            $params['social_info'] = ArrayHelper::arrayToJson($params['social_info']);
        }
        if ($baseUserInfo = UserInfoServer::server()->initUserInfo($params)) {
            return $this->resultSuccess($baseUserInfo);
        }
        return $this->resultFail(t('保存失败', 'auth'));
    }

    public function stepTwo(UserInitRule $rule) {
        $params = $this->getParams();
        $ruleName = UserInitRule::STEP_TWO;
        if (!isset($params['contact_fullname1'])) {
            $ruleName = UserInitRule::STEP_TWO_U4;
        } else {
            if (substr($params['contact_telephone1'], -10) == substr($params['contact_telephone2'], -10) || substr($params['contact_telephone3'], -10) == substr($params['contact_telephone2'], -10) || substr($params['contact_telephone3'], -10) == substr($params['contact_telephone1'], -10)) {
                return $this->resultFail("Same contact number exists");
            }
        }
        if (!$rule->validate($ruleName, $params)) {
            return $this->resultFail($rule->getError());
        }

        $userModel = $this->identity();
        if ($baseUserInfo = UserInfoServer::server()->initUserInfo($params)) {
            if ($ruleName == UserInitRule::STEP_TWO) {
                $userContacts = [
                    [
                        'contactFullname' => $params['contact_fullname1'],
                        'relation' => $params['relation1'],
                        'contactTelephone' => $params['contact_telephone1'],
                    ],
                    [
                        'contactFullname' => $params['contact_fullname2'],
                        'relation' => $params['relation2'],
                        'contactTelephone' => $params['contact_telephone2'],
                    ],
                    [
                        'contactFullname' => $params['contact_fullname3'],
                        'relation' => $params['relation3'],
                        'contactTelephone' => $params['contact_telephone3'],
                    ],
                ];
                UserInfoServer::server()->clearUserContact($userModel->id);
                foreach ($userContacts as $userContact) {
                    UserInfoServer::server()->addUserContact($userContact, Auth::id());
                }
                UserAuthServer::server()->setAuth($userModel->id, UserAuth::TYPE_CONTACTS);
            }
            return $this->resultSuccess($baseUserInfo);
        }

        return $this->resultFail(t('保存失败', 'auth'));
    }

    public function stepTwoNew(UserInitRule $rule) {
        $params = $this->getParams();
        $ruleName = UserInitRule::STEP_TWO_NEW;
        if (!$rule->validate($ruleName, $params)) {
            return $this->resultFail($rule->getError());
        }
        $user = $this->identity();
        if ($baseUserInfo = UserInfoServer::server()->initUserInfo($params)) {
            $workInfo = UserInfoServer::server()->createUserWork($params);
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_USER_WORK);
            return $this->resultSuccess($workInfo);
        }
        return $this->resultFail(t('保存失败', 'auth'));
    }

    public function stepThree(UserInitRule $rule) {
        $user = $this->identity();
        $params = $this->getParams();
        $ruleName = UserInitRule::STEP_THREE;
        if (isset($params['contact_fullname1'])) {
            if ($user->merchant_id == \Common\Models\Merchant\Merchant::getId('u4') || $user->merchant_id == 7) {
                $ruleName = UserInitRule::STEP_THREE_U4_NEW;
            } else {
                $ruleName = UserInitRule::STEP_THREE_U4;
            }
            if (substr($params['contact_telephone1'], -10) == substr($params['contact_telephone2'], -10) || substr($params['contact_telephone3'], -10) == substr($params['contact_telephone2'], -10) || substr($params['contact_telephone3'], -10) == substr($params['contact_telephone1'], -10)) {
                return $this->resultFail("Same contact number exists");
            }
        }
        if (!$rule->validate($ruleName, $params)) {
            $error = $rule->getError();
            if (str_contains($error, 'contact_telephone1')) {
                $error .= '---' . $params['contact_telephone1'];
            }
            if (str_contains($error, 'contact_telephone2')) {
                $error .= '---' . $params['contact_telephone2'];
            }
            if (str_contains($error, 'contact_telephone3')) {
                $error .= '---' . $params['contact_telephone3'];
            }
            return $this->resultFail($error);
        }

        if ($baseUserInfo = UserInfoServer::server()->initUserInfo($params)) {
            if (isset($params['loan_reason']) && $params['loan_reason']) {
                $lastOrder = OrderServer::server()->getLastOrder($user);
                if ($lastOrder) {
                    OrderDetailServer::server()->saveKeyVal($lastOrder->id, "loan_reason", $params['loan_reason']);
                }
            }
            if (isset($params['jobPosition'])) {
                $params['work_positon'] = $params['jobPosition'];
            }
            $workInfo = UserInfoServer::server()->createUserWork($params);
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_USER_WORK);
            if (in_array($ruleName, [UserInitRule::STEP_THREE_U4, UserInitRule::STEP_THREE_U4_NEW])) {
                # 保存联系人
                $userModel = $this->identity();
                $userContacts = [
                    [
                        'contactFullname' => $params['contact_fullname1'],
                        'relation' => $params['relation1'],
                        'contactTelephone' => $params['contact_telephone1'],
                    ],
                    [
                        'contactFullname' => $params['contact_fullname2'],
                        'relation' => $params['relation2'],
                        'contactTelephone' => $params['contact_telephone2'],
                    ],
                    [
                        'contactFullname' => $params['contact_fullname3'],
                        'relation' => $params['relation3'],
                        'contactTelephone' => $params['contact_telephone3'],
                    ],
                ];
                UserInfoServer::server()->clearUserContact($userModel->id);
                foreach ($userContacts as $userContact) {
                    UserInfoServer::server()->addUserContact($userContact, Auth::id());
                }
                UserAuthServer::server()->setAuth($userModel->id, UserAuth::TYPE_CONTACTS);
            }
            return $this->resultSuccess($workInfo);
        }
        return $this->resultFail(t('保存失败', 'auth'));
    }

    public function stepFour(UserInfoRule $rule) {
        $user = $this->identity();
        /* 手机号过滤( ) - 处理 */
        $data = $this->request->all();
        if ($contactTelephone1 = array_get($data, 'contactTelephone1')) {
            $data['contactTelephone1'] = StringHelper::formatTelephone($contactTelephone1);
        }
        if ($contactTelephone2 = array_get($data, 'contactTelephone2')) {
            $data['contactTelephone2'] = StringHelper::formatTelephone($contactTelephone2);
        }
        $params = $rule->validateE($rule::SCENARIO_CREATE_USER_CONTACT, $data);
        UserInfoServer::server()->initUserContact($params);
        return $this->resultSuccess();
    }

    private $_indexApr = "20%";
    private $_indexServiceFeeRate = "10%";

    public function initIndex() {
        $params = $this->getParams();
        $user = null;
        $res = [
            'loanCode' => '1001',
            'productCode' => 'A301',
            'level' => 'BRONZE',
            'minLoanTerms' => 7,
            'maxLoanTerms' => 14,
            'minAmount' => '100',
            'maxAmount' => '50000',
            'amountStep' => '50',
            'loanStep' => 7,
            'maxViewTerms' => 365,
            'maxViewAmount' => '50000',
            'loanTerms' => [
                [
                    'loanTerm' => 180,
                    'available' => true,
                ],
                [
                    'loanTerm' => 120,
                    'available' => false
                ],
                [
                    'loanTerm' => 150,
                    'available' => false,
                ],
                [
                    'loanTerm' => 180,
                    'available' => false
                ],
            ],
            "fullLoanTerms" => [
                [
                    'loanTerm' => 90,
                    'available' => true,
                ],
                [
                    'loanTerm' => 120,
                    'available' => false
                ],
                [
                    'loanTerm' => 180,
                    'available' => false,
                ],
                [
                    'loanTerm' => 360,
                    'available' => false
                ],
            ],
            'quality' => 0,
            'reference_no' => null,
            'fullname' => null
        ];
        if (isset($params['token'])) {
            /** @var User $user */
            $user = $this->identity();
            $clmAct = array_merge($res, $this->getClmData($user));
            $lastOrder = OrderServer::server()->getLastOrder($user);
            $res = $this->trialProduct($user, $clmAct);
            $res['apr'] = $this->_indexApr;
            $res['serviceFeeRate'] = $this->_indexServiceFeeRate;
            $res['reference_no'] = $lastOrder->reference_no ?? null;
            $res['dg_pay_lifetime_id'] = isset($user->userInfo->dg_pay_lifetime_id) && $user->userInfo->dg_pay_lifetime_id ? $user->userInfo->dg_pay_lifetime_id : '---';
            $res['withdrawal_service_charge'] = strval($lastOrder ? OrderServer::server()->getWithdrawalServiceCharge($lastOrder) : 0);
            $res['fullname'] = $user->fullname;
            $res['email'] = $user->userInfo->email ?? '---';
            //记录用户选择额度
            if ($user->getIsCompleted()) {
                try {
                    $customer = ClmServer::server()->getOrInitCustomer($user);
                    ClmSelectLog::model()->create([
                        'applyid' => $lastOrder->id,
                        'current_amount' => $res['maxAmount'],
                        'clm_level' => $customer->getLevel(),
                        'total_amount' => $customer->total_amount,
                        'used_amount' => $customer->used_amount,
                        'freeze_amount' => $customer->freeze_amount,
                        'merchant_id' => MerchantHelper::getMerchantId(),
                        'clm_customer_id' => $customer->clm_customer_id
                    ]);
                } catch (\Exception $e) {
                    
                }
            }
            //谷歌审核特定账号处理
            if (LoanMultipleConfigServer::server()->isGoogleAuditUser($user)) {
                $res['loanTerms'] = [
                    [
                        'loanTerm' => 30,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 60,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 90,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 180,
                        'available' => true
                    ]
                ];
                $res['fullLoanTerms'] = [
                    [
                        'loanTerm' => 180,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 180,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 180,
                        'available' => true
                    ],
                    [
                        'loanTerm' => 180,
                        'available' => true
                    ]
                ];
                $res['interest_rate'] = 0.0001;
                $res['serviceFeeRate'] = "2.5%";
            }
            return $this->resultSuccess($res);
        }
        if (!isset($params['type'])) {
            $res = $this->trialProduct($user, $res);
        }
        $res['apr'] = $this->_indexApr;
        $res['serviceFeeRate'] = '0%';
        return $this->resultSuccess($res);
    }

    /**
     * 获取用户CLM配置
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function getClmData($user) {
        if (!$user->getIsCompleted())
            return [];
        $customer = ClmServer::server()->getOrInitCustomer($user);
        $availableAmount = ClmServer::server()->getAvailableAmount($user);
        $loanTerms = LoanMultipleConfigServer::server()->getLoanDaysRange($user);
        $clmData = [
            'loanCode' => '1001',
            'productCode' => 'A301',
            'level' => $customer->getLevelAlias(),
            'minLoanTerms' => intval(min($loanTerms)),
            'maxLoanTerms' => intval(max($loanTerms)),
            'minAmount' => MoneyHelper::round2point(min(100, $availableAmount)),
            'maxAmount' => MoneyHelper::round2point($availableAmount),
            'amountStep' => "50",
            'loanStep' => 7,
            'maxViewTerms' => intval(max($loanTerms)),
            'maxViewAmount' => "50000",
        ];
        foreach ($loanTerms as $loanTerm) {
            $clmData['loanTerms'][] = ['loanTerm' => intval($loanTerm), 'available' => true];
        }
        return $clmData;
    }

    public function trialProduct($user, $clmAct) {
        $loanAmount = $clmAct['maxAmount'];
        $loanDays = $clmAct['maxLoanTerms'];
        $merchantId = $user ? $user->merchant_id : MerchantHelper::getMerchantId();
        $repeatLoanCnt = $user ? $user->getRepeatLoanCnt() : 0;

        $interestRate = LoanMultipleConfig::getConfigByCnt($merchantId, $repeatLoanCnt, LoanMultipleConfig::FIELD_LOAN_DAILY_LOAN_RATE);
        $serviceCharge = OrderServer::server()->getServiceCharge($merchantId, $repeatLoanCnt, $loanDays);
        $res = [];
        $res['loan_amount'] = $loanAmount;
        $res['loan_days'] = strval($loanDays);
        $res['interest_rate'] = $interestRate;
        $res['interest'] = round($loanAmount * ($interestRate / 100) * $loanDays);
        $res['service_charge'] = round($loanAmount * ($serviceCharge / 100));
        $res['period_amount'] = round($loanAmount + $res['interest'] + $res['service_charge']);
        $res['quality'] = $user->quality ?? User::QUALITY_NEW;
        return array_merge($clmAct, $res);
    }

    public function lastLoan() {
        /** @var User $user */
        $user = $this->identity();
        $lastOrder = OrderServer::server()->getLastOrder($user);
        if ($lastOrder) {
            $orderDetail = OrderServer::server()->detail($user, ['order_id' => $lastOrder->id]);
            $repaymentPlans = [];
            ## 未生成还款计划进行试算
            $installmentNum = 1;
            $orderInstallments = OrderDetailServer::server()->getInstallment($lastOrder); //订单绑定的分期配置
            $orderInstallments = ArrayHelper::jsonToArray($orderInstallments);
            $lastLoanDay = 0;
            foreach ($orderInstallments as $orderInstallment) {
                $loanDay = array_get($orderInstallment, 'repay_days'); //分期天数
                $installmentProportion = array_get($orderInstallment, 'repay_proportion'); //本金分期比例
                $installmentPrincipal = $installmentProportion / 100 * $lastOrder->principal; //分期本金
                $repaymentPlans[] = [
                    'repayDate' => Carbon::parse(OrderServer::server()->getAppointmentPaidTime(Carbon::now()->toDateTimeString(), $loanDay + $lastLoanDay))->format('d-m-Y'),
                    'repayAmount' => MoneyHelper::round2point($installmentPrincipal + $installmentPrincipal * $lastOrder->daily_rate * ($installmentNum == 1 ? $loanDay : $lastLoanDay)), //试算 本金+利息
                    'installment_num' => $installmentNum,
                    'svcFee' => $installmentNum == 1 ? $lastOrder->getProcessingFee() : 0,
                    'interest' => $installmentNum == 1 ? $lastOrder->interestFee() : 0
                ];
                $installmentNum++;
                $lastLoanDay = $loanDay;
            }
            ## 已有还款计划对数据进行覆盖
            foreach ($orderDetail->repaymentPlans as $key => $repaymentPlan) {
                $subject = CalcRepaymentSubjectServer::server($repaymentPlan)->getSubject();
                $repaymentPlans[$key] = [
                    'repayDate' => Carbon::parse($repaymentPlan->appointment_paid_time)
                            ->format(in_array($user->merchant_id, [1, 4]) ? 'd-m-Y' : 'd/M/Y'),
                    'repayAmount' => strval($subject->repaymentPaidAmount),
                    'installment_num' => $repaymentPlan->installment_num,
                    'svcFee' => $repaymentPlan->installment_num == 1 ? $lastOrder->getProcessingFee($repaymentPlan) : 0,
                    'interest' => $repaymentPlan->installment_num == 1 ? $lastOrder->interestFee($repaymentPlan) : 0
                ];
            }
            $data = [
                'applyId' => $orderDetail['id'], //申请ID
                'applyStatus' => $orderDetail['api_status'], //状态
                'loanDate' => $orderDetail['paid_time'], //放款日期
                'applyDate' => $orderDetail['signed_time'], //申请时间
                'loanAmount' => $orderDetail['principal'],
                'applyAmount' => $orderDetail['apply_principal'],
                'contractAmount' => $orderDetail['principal'],
                'loanTerm' => $orderDetail['loan_days'],
                'contractNo' => $orderDetail['order_no'],
                'applyStatusName' => $orderDetail['api_status_text'],
                'repaymentPlans' => $repaymentPlans,
                'rejectLastDays' => OrderServer::server()->getRejectLastDays($lastOrder), //订单被拒剩余天数
                'isCompleted' => boolval($user->getIsCompleted()), //资料是否完善
                'withdrawalNumber' => $lastOrder->withdrawalNumber,
            ];
            if (!in_array($user->merchant_id, [1, 4])) {
                if ($data['applyDate'] && $time = strtotime($data['applyDate'])) {
                    $data['applyDate'] = date("d/M/Y", $time);
                }
                if ($data['loanDate'] && $time = strtotime($data['loanDate'])) {
                    $data['loanDate'] = date("d/M/Y", $time);
                }
            }
            return $this->resultSuccess($data);
        } else {
            return $this->resultFail('Please apply again');
        }
    }

    public function trial(OrderRule $rule) {
        /** @var User $user */
        $user = $this->identity();
        $params = $this->getParams();
        if (isset($params['loanDay'])) {
            $params['loanDay'] = str_replace([' '], '', $params['loanDay']);
        }
        if (!$rule->validate(OrderRule::SCENARIO_TRIAL, $params)) {
            return $this->resultFail($rule->getError());
        }
        $loanAmt = $params['loanAmt'];
        $loanDay = $params['loanDay'];
        $order = OrderServer::server()->calculate($user->order, $loanDay, $loanAmt);
        $data = [
            'service_charge' => strval($order->service_charge_fee), //服务费
            'interest' => strval($order->interest_fee), //利息
            'loan_amount' => $loanAmt, //申请金额
            'actual_mount' => strval($order->paid_amount ?: $loanAmt - $order->service_charge_fee - ($order->withdrawal_service_charge ?? 0)), //实际到手金额
            'period_amount' => strval($order->receivable_amount), //应还总金额
            'loan_days' => $loanDay, //贷款期限
            'loanPmtDueDate' => in_array($user->merchant_id, [1, 4]) ? $order->appointment_paid_time : date("d/M/Y", strtotime($order->appointment_paid_time)), //还款日期
            'withdrawalServiceCharge' => strval(OrderServer::server()->getWithdrawalServiceCharge($order)), //线下手续费
        ];
        //优惠券试算
        $couponId = array_get($params, 'coupon_id');
        $discountAmount = 0;
        if ($couponId) {
            $coupon = CouponReceive::model()->getOne($couponId)->coupon;
            switch ($coupon->coupon_type) {
                case 1:
                    $discountAmount = $data['interest'];
                    break;
                case 2:
                    $discountAmount = $coupon->used_amount;
                    break;
            }
        }
        //应还总金额
        $data['period_amount'] = $data['period_amount'] - $discountAmount;
        //优惠金额
        $data['discount_amount'] = $discountAmount;
        //每日还款数
        $data['dalily_cost'] = round($data['period_amount'] / $data['loan_days'], 2);
        if ($order->paid_amount == null) {
            $data['actual_mount'] = strval($data['actual_mount'] - $data['withdrawalServiceCharge']);
        }
        $lastOrder = OrderServer::server()->getLastOrder($user);
        if ($lastOrder == null) {
            $this->resultFail("Please uninstall and install the latest version.");
        }

        $repaymentPlans = [];
        ## 未生成还款计划进行试算
        $installmentNum = 1;
        $orderInstallments = OrderDetailServer::server()->getInstallment($lastOrder); //订单绑定的分期配置
        $orderInstallments = ArrayHelper::jsonToArray($orderInstallments);
        foreach ($orderInstallments as $orderInstallment) {
            $installmentProportion = array_get($orderInstallment, 'repay_proportion'); //本金分期比例
            $installmentPrincipal = $installmentProportion / 100 * $loanAmt; //分期本金
            $repaymentPlans[] = [
                'repayDate' => Carbon::parse(OrderServer::server()->getAppointmentPaidTime(Carbon::now()->toDateTimeString(), $installmentNum == 1 ? $loanDay : \Common\Models\Common\Config::VALUE_MAX_LOAN_DAY))
                        ->format(in_array($user->merchant_id, [1, 4]) ? 'd-m-Y' : 'd/M/Y'),
                'repayAmount' => MoneyHelper::round2point($installmentPrincipal + $installmentPrincipal * $lastOrder->daily_rate * $loanDay), //试算 本金+利息
                'installment_num' => $installmentNum
            ];
            $installmentNum++;
        }
        $data['repaymentPlans'] = $repaymentPlans;
        return $this->resultSuccess($data);
    }

    public function getQuestion() {
        $params = $this->getParams();
        if (isset($params['step']) && intval($params['step'])) {
            if (isset(Detention::QUESTION[$params['step']])) {
                return $this->resultSuccess(Detention::QUESTION[$params['step']]);
            }
        }
        return $this->resultFail('Not Found');
    }

    public function submitQuestion(UserSurveyRule $rule) {
        $user = $this->identity();
        $params = $this->getParams();
        if (!$rule->validate(UserSurveyRule::SCENARIO_CREATE, $params)) {
            return $this->resultFail($rule->getError());
        }
        $attributes = [
            'channel_name' => UserSurvey::CHANNEL_DETENTION,
            'title' => $params['step'],
            'content' => $params['option'],
            'user_id' => $user->id,
        ];
        if ($userSurvey = UserSurvey::model()->createModel($attributes)) {
            return $this->resultSuccess($userSurvey);
        }

        return $this->resultFail('Failed');
    }

    public function replenishInit() {
        $user = $this->identity();
        $res = [
            'card_type' => $user->card_type,
            'id_card_no' => $user->id_card_no
        ];
        return $this->resultSuccess($res);
    }

    /**
     * 返回App步骤
     */
    public function getStep() {
        $user = $this->identity();
        $params = $this->getParams();
        if (isset($params['step_name'])) {
            return $this->resultSuccess(UserInitStep::model()->updateOrCreateModel(['step_name' => $params['step_name']], ['user_id' => $user->id]));
        } else {
            return $this->resultSuccess(UserInitStep::model()->where(['user_id' => $user->id])->first());
        }
    }

    /**
     * 选择取款方式
     * @param UserInitRule $rule
     * @return array
     * @throws \Common\Exceptions\ApiException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createPayment(UserInitRule $rule) {
        $params = $this->getParams();
        if (!$rule->validate(UserInitRule::STEP_SIX, $params)) {
            return $this->resultFail($rule->getError());
        }
        /** @var User $user */
        $user = $this->identity();
        $params['user_id'] = $user->id;
        $params['account_name'] = array_get($params, 'account_name', $user->fullname);
        //保存CompanyID
        if (isset($params['company_id'])) {
            if (!$attributes = UploadServer::moveFile($this->request->file('company_id'))) {
                return $this->resultFail(trans('messages.上传文件保存失败'));
            } else {
                $attributes['type'] = Upload::TYPE_COMPANY_ID;
                if (!$modelFace = UploadServer::create($attributes, $user->id)) {
                    return $this->resultFail(trans('messages.上传文件保存记录失败'));
                }
                UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_COMPANY_ID);
            }
        }
//dd($params);
        if (BankcardPesoServer::server()->create($params)) {
            UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BANKCARD);
            return $this->resultSuccess();
        }
        return $this->resultFail();
    }

    public function sample() {
        return $this->resultSuccess([
                    "loan_amount" => "2,000",
                    "loan_period" => "180",
                    "interest_rate" => "0%",
                    "interest" => "0",
                    "service_fee_rate" => "10%",
                    "service_fee_charge" => "200",
                    "total_repayment" => "2,200",
                    'apr' => '20%',
                    'repyament_method' => 'Repay in two installments'
        ]);
    }

}
