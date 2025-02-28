<?php

namespace Approve\Admin\Services\Approval;

use Admin\Models\Upload\Upload;
use Api\Models\Order\OrderDetail;
use Api\Services\Order\OrderDetailServer;
use Api\Services\User\UserAuthServer;
use Approve\Admin\Services\CommonService;
use Auth;
use Common\Events\Order\OrderFlowPushEvent;
use Common\Events\Risk\RiskDataSendEvent;
use Common\Models\Approve\ApprovePoolLog;
use Common\Models\Approve\ApproveUserPool;
use Common\Models\Approve\ManualApproveLog;
use Common\Models\Order\Order;
use Common\Models\Order\OrderLog;
use Common\Models\User\UserAuth;
use Common\Services\Config\LoanMultipleConfigServer;
use Common\Services\Order\OrderServer;
use Common\Traits\GetInstance;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\DateHelper;
use Common\Services\NewClm\ClmServer;
use Common\Utils\Data\MoneyHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Models\Approve\ApprovePool;

class SubmitService {

    use GetInstance;

    /**
     * 最大电审次数
     */
    const MAX_CALL_TIME = 3;

    /**
     * @param $orderId
     * @param $approveStatus
     * @param $refusalCode
     * @param $remarkArr
     * @param $userTimeClear
     * @return array
     * @throws \Exception
     */
    public function firstApproveSubmit($orderId, $approveStatus, $refusalCode, $remarkArr, $userTimeClear) {
        $order = Order::getById($orderId);
        $approveResultCode = 0;
        $approveUserStatusCode = 0;
        $originOrderStatus = $order->status;
        $remarkText = $remarkArr['remark'] ?? "";
        $remark = ArrayHelper::arrayToJson($remarkArr);
        $orderServer = OrderServer::server();
        switch ($approveStatus) {
            case FirstApproveService::PASS:
                $approveResultCode = ApprovePoolLog::FIRST_APPROVE_RESULT_PASS;
                //$userApproveConfig = Config::model()->getUserApproveProcess($order->quality);
                //if (in_array(Approve::PROCESS_CALL_APPROVAL, $userApproveConfig)) {
                if ($order->call_check == Order::CALL_CHECK_REQUIRE) {
                    $orderServer->manualToCall($orderId);
                    $this->saveApproveLog(
                            $orderId,
                            $originOrderStatus,
                            ManualApproveLog::APPROVE_TYPE_MANUAL,
                            ManualApproveLog::NAME_MANUAL_PASS,
                            $approveStatus,
                            $remark
                    );
                    # 初审通过后继续原来审批员电审
                    $approveUserStatusCode = ApproveUserPool::STATUS_CHECKING;
                    //推送
                    event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_TO_CALL));
                } else {
                    // 不需要电审,直接通过
                    $orderServer->manualPass($orderId);
                    $this->saveApproveLog(
                            $orderId,
                            $originOrderStatus,
                            ManualApproveLog::APPROVE_TYPE_MANUAL,
                            ManualApproveLog::NAME_PASS,
                            $approveStatus,
                            $remark
                    );
                    $approveUserStatusCode = ApproveUserPool::STATUS_FIRST_PASS;
                    // event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
                }
                break;
            case FirstApproveService::SUPPLEMENTARY:
                /** 补件资料失效处理 */
                switch ($userTimeClear) {
                    case \Api\Models\Order\Order::STATUS_API_REPLENISH_ALL:
                        UserAuthServer::server()->clearAuth($order->user_id, UserAuth::TYPE_ID_FRONT);
                        UserAuthServer::server()->clearAuth($order->user_id, UserAuth::TYPE_FACES);
                        break;
                    case \Api\Models\Order\Order::STATUS_API_REPLENISH_ID:
                        UserAuthServer::server()->clearAuth($order->user_id, UserAuth::TYPE_ID_FRONT);
                        break;
                    case \Api\Models\Order\Order::STATUS_API_REPLENISH_FACE:
                        UserAuthServer::server()->clearAuth($order->user_id, UserAuth::TYPE_FACES);
                        break;
                }
                $orderServer->manualReplenish($orderId);
                $approveUserStatusCode = ApproveUserPool::STATUS_FIRST_RETURN;
                $approveResultCode = ApprovePoolLog::FIRST_APPROVE_RESULT_SUPPLEMENT;
                $this->saveApproveLog(
                        $orderId,
                        $originOrderStatus,
                        ManualApproveLog::APPROVE_TYPE_MANUAL,
                        ManualApproveLog::NAME_MANUAL_REPLENISH,
                        $approveStatus,
                        $userTimeClear
                );
                //待补充推送
                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_REPLENISH));
                break;
            case FirstApproveService::FRAUD:
                $orderServer->manualReject($order, $order->user_id, $refusalCode);
                $approveUserStatusCode = ApproveUserPool::STATUS_FIRST_REJECT;
                $approveResultCode = ApprovePoolLog::FIRST_APPROVE_RESULT_REJECT;
                $this->saveApproveLog(
                        $orderId,
                        $originOrderStatus,
                        ManualApproveLog::APPROVE_TYPE_MANUAL,
                        ManualApproveLog::NAME_MANUAL_REJECT,
                        $approveStatus,
                        $remark
                );
                break;
        }
        $approvePool = \Common\Models\Approve\ApprovePool::model()->where("order_id", $order->id)->first();
        $approvePool->last_remark = $remarkText;
        $approvePool->save();
        event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_APPROVE_FINISH));

        return [
            'originStatus' => $originOrderStatus,
            'modifyStatus' => Order::getById($orderId)->status,
            'approveUserStatus' => $approveUserStatusCode,
            'approveResult' => $approveResultCode,
        ];
    }

    /**
     * @param $orderId
     * @param $approveStatus
     * @param $refusalCode
     * @param $remark
     * @return array
     * @throws \Exception
     */
    public function callApproveSubmit($orderId, $approveStatus, $refusalCode, $remark, $approvePrincipal = 0) {
        $order = Order::getById($orderId);
        $applyPrincipal = $order->principal;
        $originStatus = $order->status;
        $approveUserStatusCode = 0;
        $approveResultCode = 0;
        $remarkText = $remark['remark'] ?? "";
        $remark = ArrayHelper::arrayToJson($remark);
        $orderServer = OrderServer::server();
        switch ($approveStatus) {
            case CallApproveService::GAVE_UP:
                /** 取消订单 */
                $orderServer->manualCancel($order, $order->status);
                $approveUserStatusCode = ApproveUserPool::STATUS_CALL_RETURN;
                $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_CANCEL;
                $this->saveApproveLog(
                        $orderId,
                        $originStatus,
                        ManualApproveLog::APPROVE_TYPE_CALL,
                        ManualApproveLog::NAME_CALL_USER_GIVE_UP,
                        $approveStatus,
                        $remark
                );
                break;
            case CallApproveService::FAILURE_CHECK:
                /** 拒绝订单 */
                $orderServer->manualReject($order, $order->user_id, $refusalCode);
                $approveUserStatusCode = ApproveUserPool::STATUS_CALL_REJECT;
                $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_REJECT;
                $this->saveApproveLog(
                        $orderId,
                        $originStatus,
                        ManualApproveLog::APPROVE_TYPE_CALL,
                        ManualApproveLog::NAME_CALL_REJECT,
                        $approveStatus,
                        $remark
                );
                break;
            case CallApproveService::PASS:
                $orderServer->callToPass($orderId);
                $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_PASS;
                $this->saveApproveLog(
                        $orderId,
                        $originStatus,
                        ManualApproveLog::APPROVE_TYPE_CALL,
                        ManualApproveLog::NAME_PASS,
                        $approveStatus,
                        $remark,
                        $approvePrincipal,
                        $applyPrincipal
                );
                $approveUserStatusCode = ApproveUserPool::STATUS_CALL_PASS;
                if ($approvePrincipal > 0) {
                    $order = Order::model()->getOne($orderId);
                    if ($order->principal > $approvePrincipal) {
                        \DB::transaction(function () use ($order, $approvePrincipal) {
                            ClmServer::server()->getOrInitCustomer($order->user)->unfreeze($order->principal - $approvePrincipal, false);
                            $order->principal = $approvePrincipal;
                            $order->save();
                            dispatch(new \Common\Jobs\Order\BuildContractJob($order->id));
                        });
                        //服务费重新计算
                        $order->refresh();
                        $serviceChargeRate = LoanMultipleConfigServer::server()->getServiceChargeRate($order->user, $order->loan_days);
                        /** CLM等级对应手续费费率百分比 */
                        $serviceChargeDiscount = ClmServer::server()->getInterestDiscount($order->user);
                        $serviceChargeDiscountPercent = $serviceChargeDiscount/100;
                        $order->service_charge = strval(MoneyHelper::round2point($order->principal * $serviceChargeRate * (1 - $serviceChargeDiscountPercent))); //服务费
                        $order->save();
                        OrderDetailServer::server()->saveKeyVal($order->id, OrderDetail::KEY_SERVICE_CHARGE_DISCOUNT,$serviceChargeDiscount);
                    }
                }
                //审批通过推送
//                event(new OrderFlowPushEvent($order, OrderFlowPushEvent::TYPE_APPROVE_PASS));
                break;
            case CallApproveService::NO_ANSWER:
                /** 当案件在三个不同的工作日均被点击为占线无人接听时，在第三个工作日凌晨自动拒绝，拒绝码为R02OMPNA */
                # Tracy 去掉这个逻辑 20211027
                $callNum = OrderLog::model()->getTwiceCallNum($orderId);
//                if ($callNum >= (static::MAX_CALL_TIME - 1)) {
//                    /** 拒绝订单 */
//                    $orderServer->manualReject($order, $order->user_id, 'R02OMPNA');
//                    $approveUserStatusCode = ApproveUserPool::STATUS_CALL_REJECT;
//                    $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_REJECT;
//                    $this->saveApproveLog(
//                            $orderId,
//                            $originStatus,
//                            ManualApproveLog::APPROVE_TYPE_CALL,
//                            ManualApproveLog::NAME_CALL_REJECT,
//                            $approveStatus,
//                            $remark
//                    );
//                } else {
                    /** 加入电二审池 */
                    $orderServer->manualSecondCall($orderId);
                    $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_TWICE_ONE;
                    // 电二审二次
                    if ($callNum == 1) {
                        $approveResultCode = ApprovePoolLog::CALL_APPROVE_RESULT_TWICE_TWO;
                    }
                    $approveUserStatusCode = ApproveUserPool::STATUS_NO_ANSWER;
                    $approvePool = \Common\Models\Approve\ApprovePool::model()->where("order_id", $orderId)->first();
                    # 二次呼叫失败转移到待电审
                    if($approvePool && $approvePool->auto_call_status == ApprovePool::AUTO_CALL_STATUS_TWICE_FAIL){
                        $approvePool->status = ApprovePool::STATUS_WAITING;
                        $approvePool->order_status = \Common\Utils\Code\OrderStatus::CALL_APPROVAL;
                        $userPool = ApproveUserPool::model()->where("approve_pool_id", $approvePool->id)->orderByDesc("id")->first();
                        if ($userPool) {
                            $userPool->status = ApproveUserPool::STATUS_STOP_WORK;
                            $userPool->save();
                        }
                        OrderServer::server()->changeStatus($orderId, [Order::STATUS_WAIT_TWICE_CALL_APPROVE], Order::STATUS_WAIT_CALL_APPROVE);
                    }else{
                        if ($approvePool) {
                            if (!$approvePool->tags) {
                                $approvePool->tags = ApprovePool::TAGS_FIRST_TWICE;
                                $approvePool->save();
                            } else {
                                if ($approvePool->tags == ApprovePool::TAGS_FIRST_TWICE) {
                                    $approvePool->tags = ApprovePool::TAGS_SECOND_TWICE;
                                    $approvePool->save();
                                }
                            }
                            if (!$approvePool->auto_call_status || in_array($approvePool->auto_call_status, ApprovePool::AUTO_CALL_STATUS_PASS)) {
                                $approvePool->auto_call_status = ApprovePool::AUTO_CALL_STATUS_FIRST_WAIT;
                                $approvePool->auto_call_time = date("Y-m-d H:i:s");
                                $approvePool->save();
                            }
                        }
                        $this->saveApproveLog(
                                $orderId,
                                $originStatus,
                                ManualApproveLog::APPROVE_TYPE_CALL,
                                ManualApproveLog::NAME_CALL_SECOND,
                                $approveStatus,
                                $remark
                        );
                    }
//                }
                break;
        }

        $approvePool = \Common\Models\Approve\ApprovePool::model()->where("order_id", $order->id)->first();
        $approvePool->last_remark = $remarkText;
        $approvePool->save();
        event(new RiskDataSendEvent($order->user_id, RiskDataSendEvent::NODE_APPROVE_FINISH));

        return [
            'originStatus' => $originStatus,
            'modifyStatus' => Order::getById($orderId)->status,
            'approveUserStatus' => $approveUserStatusCode,
            'approveResult' => $approveResultCode,
        ];
    }

    /**
     * 审批日志保存
     *
     * @param $orderId
     * @param $originOrderStatus
     * @param $approveType
     * @param $name
     * @param $result
     * @param string $otherRemark
     * @return bool|ManualApproveLog
     */
    protected function saveApproveLog($orderId, $originOrderStatus, $approveType, $name, $result, $otherRemark = '', $approvePrincipal = 0, $applyPrincipal = 0) {
        $order = Order::getById($orderId);
        $data = [
            'admin_id' => Auth::user()->id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'approve_type' => $approveType,
            'name' => $name,
            'from_order_status' => $originOrderStatus,
            'to_order_status' => $order->status,
            'result' => $result,
            'remark' => $otherRemark,
            'apply_principal' => $applyPrincipal == 0 ? "" : $applyPrincipal, #申请金额
            'approve_principal' => $approvePrincipal == 0 ? "" : $approvePrincipal #审批金额
        ];
        return ManualApproveLog::model(ManualApproveLog::SCENARIO_CREATE)
                        ->saveModel($data);
    }

    /**
     * @param Order $order
     * @param $data
     */
    public static function updateUserInfo($order, $data) {
        //更新clm证件类型
        $customer = ClmServer::server()->getOrInitCustomer($order->user);
        \DB::beginTransaction();
        if (!empty($data['id_card_type'])) {
            //同步更新上传图片类型
            $old_card_type = $order->user->card_type;
            $new_card_type_code = null;
            $old_card_type_code = null;
            foreach (Upload::TYPE_BUK as $k => $v) {
                if ($v == $old_card_type) {
                    $old_card_type_code = $k;
                }
                if ($v == $data['id_card_type']) {
                    $new_card_type_code = $k;
                }
            }
            $uploads = (new \Admin\Models\Upload\Upload())->getUploadsByUserIdAndType($order->user->id, [$old_card_type_code]);
            //更改过类型才作废证件照
            if ($new_card_type_code != $old_card_type_code) {
                $uploads_new_card_type = (new \Admin\Models\Upload\Upload())->getUploadsByUserIdAndType($order->user->id, [$new_card_type_code]);
                //要作废已存在的证件类型照
                if ($uploads_new_card_type) {
                    foreach ($uploads_new_card_type as $upload) {
                        $upload->status = Upload::STATUS_CLEAR;
                        $upload->save();
                    }
                }
            }
            foreach ($uploads as $upload) {
                $upload->type = $new_card_type_code;
                $upload->save();
            }
            $customer->idcard_type = $data['id_card_type'];
            $customer->idcard = $data['id_card_no'] ?? null;
            $order->user->card_type = $data['id_card_type'];
        }

        if (!empty($data['firstname'])) {
            $order->user->firstname = $data['firstname'];
        }
        
        if (!empty($data['fullname'])) {
            $order->user->fullname = $data['fullname'];
        }

        if (!empty($data['middlename'])) {
            $order->user->middlename = $data['middlename'];
        }

        if (!empty($data['lastname'])) {
            $order->user->lastname = $data['lastname'];
        }

        if (!empty($data['firstname']) && !empty($data['lastname'])) {
            $order->user->fullname = $data['firstname'] . (isset($data['middlename']) && $data['middlename'] ? ' ' . $data['middlename'] : "") . ' ' . $data['lastname'];
            $customer->name = $order->user->fullname;
        }

        if (!empty($data['id_card_no'])) {
            $order->user->id_card_no = $data['id_card_no'];
        }

        if (!empty($data['first_name'])) {
            $order->user->fullname = $data['first_name'];
            $customer->name = $order->user->fullname;
        }

        if (!empty($data['father_name'])) {
            $order->userInfo->father_name = $data['father_name'];
        }

        if (!empty($data['gender'])) {
            $order->userInfo->gender = ucfirst(strtolower($data['gender']));
        }

        if (!empty($data['birthday'])) {
            $order->userInfo->birthday = DateHelper::timestampToString($data['birthday'], "d/m/Y");
            $customer->birthday = date('Y-m-d H:i:s', $data['birthday']);
        }

        try {
            //审批通过才更改clm
//            if ( isset($data['base_detail_status']) && $data['base_detail_status']==FirstApproveService::PASS ){
            if ($customer->isDirty()) {
                $customer->save();
            }
        } catch (\Exception $exception) {
//                throw new \Exception('Card number update failed, failed reason: already exist');
            DingHelper::notice($exception->getMessage() . json_encode($customer), '初审保存clmCustomer异常');
            $req_data = json_encode($customer->toArray());
            \Log::error('初审保存clmCustomer异常------' . $order->order_no . '------' . $req_data);
            \DB::rollBack();
            $error_date = date('Y-m-d H:i:s');
            \DB::table('new_clm_customer_exist_error')->insert(
                ['clm_customer_id' => $customer->clm_customer_id, 'order_no' => $order->order_no,
                    'created_at'=>$error_date,'updated_at'=>$error_date,'req_data'=>$req_data,
                    'idcard_type_nospace'=>$customer->idcard_type_nospace,'idcard'=>$customer->idcard,'name_nospace'=>$customer->name_nospace,
                    'phone'=>$customer->phone,'birthday'=>$customer->birthday]
            );
            return 'Card number update failed, failed reason: already exist';
        }

        if (!empty($data['permanent_address'])) {
            $order->userInfo->permanent_address = $data['permanent_address'];
        }

        if (!empty($data['permanent_pin_code'])) {
            $address = CommonService::getInstance()->getAddressDetail($data['permanent_pin_code']);
            $order->userInfo->permanent_pincode = $data['permanent_pin_code'];
            if (!empty($address['statename'])) {
                $order->userInfo->permanent_province = $address['statename'];
            }
            if (!empty($address['districtname'])) {
                $order->userInfo->permanent_city = $address['districtname'];
            }
        }

        if (!empty($data['address'])) {
            $order->userInfo->address = $data['address'];
        }

        if (!empty($data['current_address'])) {
            $order->userInfo->address = $data['current_address'];
        }

        if (!empty($data['current_pin_code'])) {
            $address = CommonService::getInstance()->getAddressDetail($data['current_pin_code']);
            $order->userInfo->pincode = $data['current_pin_code'];
            if (!empty($address['statename'])) {
                $order->userInfo->province = $address['statename'];
            }
            if (!empty($address['districtname'])) {
                $order->userInfo->city = $address['districtname'];
            }
        }

        /** 确认收入 */
        if (!empty($data['confirm_month_income'])) {
            $order->userInfo->confirm_month_income = $data['confirm_month_income'];
        } else {
            if (isset($data['confirm_month_income']) && $data['confirm_month_income'] == "0") {
                $order->userInfo->confirm_month_income = 0;
            }
        }

        /** 社交账号信息 */
        if (!empty($data['social_info'])) {
            if (isset($data['whatsapp']) && $data['whatsapp']) {
                $data['social_info']['whatsapp'] = $data['whatsapp'];
            }
            if (isset($data['wechat']) && $data['wechat']) {
                $data['social_info']['wechat'] = $data['wechat'];
            }
            $order->userInfo->social_info = ArrayHelper::arrayToJson($data['social_info']);
        }

        if ($order->userInfo->exists) {
            $order->userInfo->save();
        }

        if ($order->user->exists) {
            $order->user->save();
        }
        \DB::commit();
    }

}
