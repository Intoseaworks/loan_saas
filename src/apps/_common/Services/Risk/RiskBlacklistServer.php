<?php

namespace Common\Services\Risk;

use Approve\Admin\Services\Rule\ApproveRuleService;
use Cache;
use Common\Models\BankCard\BankCardPeso;
use Common\Models\Order\Order;
use Common\Models\Order\RepaymentPlan;
use Common\Models\Risk\RiskBlacklist;
use Common\Models\User\User;
use Common\Models\User\UserContact;
use Common\Models\UserData\UserPhoneHardware;
use Common\Services\BaseService;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Str;

/**
 * Class RiskBlacklistServer
 * @package Common\Services\Risk
 */
class RiskBlacklistServer extends BaseService {

    /** 系统风控入黑名单
     * @param Order $order
     * @param $blackReason
     * @param bool $isGlobal
     * @param null $relatedInfo
     * @return bool
     */
    public function systemAddBlack($order, $blackReason, $isGlobal = false, $relatedInfo = []) {
        if (empty($order))
            return false;
        $userRelateData = $this->getUserRelateData($order->user);
        /** 系统风控黑名单只需要填入1-10项,剔除线下导入字段 */
        $userRelateData = array_except($userRelateData, RiskBlacklist::KEYWORD_MANUAL);
        MerchantHelper::setMerchantId($order->merchant_id);
        $excludedFromBlack = config("config.excluded_from_black");
        $data = [];
        /** 构建数据集 */
        foreach ((array) $userRelateData as $key => $values) {
            #屏蔽RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID 20210422
            if ($key == RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID) {
                continue;
            }
            $itemData = [
                'keyword' => $key,
                'black_reason' => $blackReason,
                'apply_id' => $order->id,
                'related_info' => ArrayHelper::arrayToJson($relatedInfo),
                'is_global' => $isGlobal ? RiskBlacklist::IS_GLOBAL_YES : RiskBlacklist::IS_GLOBAL_NO,
            ];
            /** 处理多维数组 */
            if (is_array($values)) {
                $values = array_filter($values);
                foreach ($values as $value) {
                    $itemData['value'] = $value;
                    # 剔除入黑
                    //email大小写排除问题
                    if ( 'EMAIL' == $key && in_array(Str::upper($itemData['value']), $excludedFromBlack[$key]) ){
                            continue;
                        }
                    if (isset($excludedFromBlack[$key]) && in_array($itemData['value'], $excludedFromBlack[$key])) {
                        continue;
                    }
                    $data[] = $itemData;
                }
            } else {
                $itemData['value'] = $values;
                # 剔除入黑
                //email大小写排除问题
                if ( 'EMAIL' == $key && in_array(Str::upper($itemData['value']), $excludedFromBlack[$key]) ){
                    continue;
                }
                if (isset($excludedFromBlack[$key]) && in_array($itemData['value'], $excludedFromBlack[$key])) {
                    continue;
                }
                $data[] = $itemData;
            }
            unset($itemData);
        }
        if (!empty($data)) {
            return RiskBlacklist::model()->batchAdd($data);
        }
        return false;
    }

    /** 系统风控入黑名单只入主订单id
     * @param Order $order
     * @param $blackReason
     * @param bool $isGlobal
     * @param null $relatedInfo
     * @return bool
     */
    public function systemAddBlackUseMainOrderId($mainOrder,$order, $blackReason, $isGlobal = false, $relatedInfo = []) {
        if (empty($order))
            return false;
        $userRelateData = $this->getUserRelateData($order->user);
        /** 系统风控黑名单只需要填入1-10项,剔除线下导入字段 */
        $userRelateData = array_except($userRelateData, RiskBlacklist::KEYWORD_MANUAL);
        MerchantHelper::setMerchantId($order->merchant_id);
        $excludedFromBlack = config("config.excluded_from_black");
        $data = [];
        /** 构建数据集 */
        foreach ((array) $userRelateData as $key => $values) {
            #屏蔽RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID 20210422
            if ($key == RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID) {
                continue;
            }
            $itemData = [
                'keyword' => $key,
                'black_reason' => $blackReason,
                'apply_id' => $mainOrder->id,
                'related_info' => ArrayHelper::arrayToJson($relatedInfo),
                'is_global' => $isGlobal ? RiskBlacklist::IS_GLOBAL_YES : RiskBlacklist::IS_GLOBAL_NO,
            ];
            /** 处理多维数组 */
            if (is_array($values)) {
                $values = array_filter($values);
                foreach ($values as $value) {
                    $itemData['value'] = $value;
                    # 剔除入黑
                    if (isset($excludedFromBlack[$key]) && in_array($itemData['value'], $excludedFromBlack[$key])) {
                        continue;
                    }
                    $data[] = $itemData;
                }
            } else {
                $itemData['value'] = $values;
                # 剔除入黑
                if (isset($excludedFromBlack[$key]) && in_array($itemData['value'], $excludedFromBlack[$key])) {
                    continue;
                }
                $data[] = $itemData;
            }
            unset($itemData);
        }
        if (!empty($data)) {
            return RiskBlacklist::model()->batchAdd($data);
        }
        return false;
    }

    /**
     * 线下导入数据保存 分条插入
     * @param $data
     * @return bool|RiskBlacklist
     */
    public function manualAddBlack($data) {
        list($keyword, $value, $blackReason, $merchantId, $isGlobal) = $data;
        $params = [
            'keyword' => $keyword,
            'value' => $value,
            'black_reason' => $blackReason,
            'apply_id' => null,
            'related_info' => null,
            'merchant_id' => $merchantId,
            'is_global' => $isGlobal,
        ];
        return RiskBlacklist::model()->batchAdd([$params]);
    }

    /**
     * 逾期入黑
     * 相同证件号码/手机号码在所有租户下前两次贷款逾期超过20天 全局Y
     * 该用户贷款逾期超过20天 全局N
     */
    public function overdueAddBlacklist() {
        /** 1期还款计划超过20天未还款 */
        $query = RepaymentPlan::query()
            ->whereInstallmentNum(1)->where('overdue_days', '>', '20')->groupBy('user_id')->select(\DB::raw('order_id,merchant_id,count(1) as count'))->withoutGlobalScopes();
        $repaymentPlans = $query->get()->toArray();
        foreach ((array) $repaymentPlans as $repaymentPlan) {
            $orderId = array_get($repaymentPlan, 'order_id');
            $count = array_get($repaymentPlan, 'count');
            $merchantId = array_get($repaymentPlan, 'merchant_id');
            if ($orderId && $count > 0) {
                MerchantHelper::setMerchantId($merchantId);
                /** 所有租户下前两次贷款逾期超过20天 全局Y */
                $isGlobal = $count >= 2;
                $order = Order::getById($orderId);
                //贷后入黑，入黑的apply_id要取当前逾期的order_id
                if ( '2000-01-01' < $order->paid_time && !in_array($order->status,Order::FINISH_STATUS) ){
                    $blackOrder = $order;
                }else{
                    $blackOrder = Order::whereUserId($order->user_id)->whereNotIn('status',Order::FINISH_STATUS)->where('paid_time','>','2000-01-01')->orderByDesc('id')->first();
                }
                if ( $blackOrder && $blackOrder->lastRepaymentPlan && $blackOrder->lastRepaymentPlan->overdue_days > 20 ){
                    $this->systemAddBlack($blackOrder, RiskBlacklist::TYPE_OVERDUE, $isGlobal);
                }
            }
        }
        /**新规则20210918*/
        $this->blacklistDisable();
    }

    /**
     * 入黑失效
     */
    public function blacklistDisable() {
        //电话入黑超过一年出黑
        $query = \DB::table('risk_blacklist')->where('keyword','TELEPHONE')
            ->where('merchant_id',4)
            ->where('created_at','<',date('Y-m-d H:i:s',strtotime("-1 year")))
            ->whereIn(\DB::raw("RIGHT(apply_id,1)"),[0,1,2])
            ->update(['status' => RiskBlacklist::STATUS_DISABLE]);
        /** 1期还款计划超过20天未还款 */
        //关联入黑失效
        //获取关联如黑失效记录
        //黑名单失效范围,risk_blacklist表中，merchant_id=4，且apply_id的最后一位数值为 0、1、2的黑名单
        $disableRelateBlackRecords = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->where('black_reason', RiskBlacklist::TYPE_RELATE)
            ->whereMerchantId(4)
            ->whereIn(\DB::raw("RIGHT(apply_id,1)"),[0,1,2])->get()->toArray();
//        $disableRelateBlackRecords = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->where('black_reason', RiskBlacklist::TYPE_RELATE)->get()->toArray();
        foreach ( (array)$disableRelateBlackRecords as $disableRelateBlackRecord ){
            if ($disableRelateBlackRecord['related_info'] && json_decode($disableRelateBlackRecord['related_info'])){
                $this->relateDelBlack($disableRelateBlackRecord);
            }
        }
//        $query = RepaymentPlan::query()->whereInstallmentNum(1)->where('overdue_days', '>', '20')
//            ->join('order','repayment_plan.order_id','=','order.id')
////            ->where('repayment_plan.order_id',911650)
////            ->where('order.quality',0)
//            ->where('order.paid_time','>','2000-01-01')
////            ->whereNotIn('order.status',Order::FINISH_STATUS)
//            ->select(\DB::raw('order_id,repayment_plan.merchant_id,repayment_plan.overdue_days,order.paid_time'))->withoutGlobalScopes()
//            ->orderBy('order.paid_time');
//        $repaymentPlans = $query->get()->toArray();
//        foreach ((array) $repaymentPlans as $repaymentPlan) {
//            $orderId = array_get($repaymentPlan, 'order_id');
//            $merchantId = array_get($repaymentPlan, 'merchant_id');
//            if ($orderId) {
//                MerchantHelper::setMerchantId($merchantId);
//                /** 所有租户下前两次贷款逾期超过20天 全局Y */
//                $order = Order::getById($orderId);
//                /** 所有租户下前两次贷款逾期超过20天 本品牌Y */
//                $users = $this->getUsersByClmRules($order->user);
//                //有多个用户才做比较
//                if (count($users) < 2){
//                    continue;
//                }
////                                        \DB::enableQueryLog();
////                $repaymentPlanFirst = RepaymentPlan::query()->whereInstallmentNum(1)
//////                    ->join('order','repayment_plan.order_id','=','order.id')
//////                    ->whereIn('repayment_plan.user_id',$users)
//////                    ->where('order.paid_time','>','2000-01-01')
//////                    ->select(\DB::raw('order_id,repayment_plan.merchant_id,repayment_plan.overdue_days,order.paid_time'))->withoutGlobalScopes()
//////                    ->orderBy('order.paid_time')->first();
//                $repaymentPlanFirst = Order::query()->whereIn('user_id',$users)
//                    ->where('order.paid_time','>','2000-01-01')
//                    ->select(\DB::raw('id,merchant_id,order.paid_time,status,user_id'))->withoutGlobalScopes()
//                    ->orderBy('order.paid_time')->first();
////                                        print_r(\DB::getQueryLog());
////                        print_r($repaymentPlanFirst);
////                        exit;
//                //第一笔订单是逾期且逾期超过20天入全局黑名单
//                if ( $repaymentPlanFirst && $repaymentPlan['order_id'] == $repaymentPlanFirst->id ){
//                    //关联用户入黑,clm匹配的
//                    $orders = Order::query()->whereIn('user_id',$users)
//                        ->where('order.paid_time','>','2000-01-01')
//                        ->where('order.user_id','!=',$order->user_id)
//                        ->select(\DB::raw('id,merchant_id,order.paid_time,status,user_id'))->withoutGlobalScopes()
//                        ->orderBy('order.paid_time')->groupBy('order.user_id')->get();
//                    foreach ( $orders as $o){
//                        $this->systemAddBlackUseMainOrderId($order,$o, RiskBlacklist::TYPE_OVERDUE, true);
//                    }
//                    $this->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE, true);
//                }
//                //第一笔订单不逾期入本品牌黑名单
//                if ( ($repaymentPlanFirst && $repaymentPlan['order_id'] != $repaymentPlanFirst->id) && $repaymentPlanFirst->status != Order::STATUS_OVERDUE ){
//                    $this->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE);
//                }
//                //第一笔订单逾期但不超过20天,入本品牌黑名单
//                if ( ($repaymentPlanFirst && $repaymentPlan['order_id'] != $repaymentPlanFirst->id) && $repaymentPlanFirst->status == Order::STATUS_OVERDUE && $repaymentPlanFirst->lastRepaymentPlan->overdue_days <= 20 ){
//                    $this->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE);
//                }
//            }
//        }
//        $this->overdueAddBlacklistNewMerchant();
    }

    /**
     * 以clm匹配规则为原则，全品牌第2笔及以上放款的订单，状态为逾期未结清且逾期天数>20，则该订单对应的品牌的入黑信息，加入本品牌黑名单（is_global=N）。若本品牌信息已是全局黑名单，则不需要再次添加到黑名单。
     */
    public function overdueAddBlacklistNewMerchant() {
        /** 1期还款计划超过20天未还款 */
        $query = RepaymentPlan::query()->whereInstallmentNum(1)->where('overdue_days', '>', '20')
            ->join('order','repayment_plan.order_id','=','order.id')
            ->where('order.quality',1)->whereNotIn('order.status',Order::FINISH_STATUS)
            ->where('order.paid_time','>','2000-01-01')
            ->select(\DB::raw('order_id,repayment_plan.merchant_id,repayment_plan.overdue_days,order.paid_time'))->withoutGlobalScopes()
            ->orderBy('order.paid_time');
        $repaymentPlans = $query->get()->toArray();
        foreach ((array) $repaymentPlans as $repaymentPlan) {
            $orderId = array_get($repaymentPlan, 'order_id');
            $merchantId = array_get($repaymentPlan, 'merchant_id');
            if ($orderId) {
                MerchantHelper::setMerchantId($merchantId);
                $order = Order::getById($orderId);
                /** 所有租户下前两次贷款逾期超过20天 本品牌Y */
                $users = $this->getUsersByClmRules($order->user);
                //有多个用户才做比较
                if (count($users) < 2){
                    continue;
                }
                $repaymentPlanFirst = RepaymentPlan::query()->whereInstallmentNum(1)
                    ->join('order','repayment_plan.order_id','=','order.id')
                    ->whereNotIn('order.status',Order::FINISH_STATUS)
                    ->whereIn('repayment_plan.user_id',$users)
                    ->where('order.paid_time','>','2000-01-01')
                    ->select(\DB::raw('order_id,repayment_plan.merchant_id,repayment_plan.overdue_days,order.paid_time'))->withoutGlobalScopes()
                    ->orderBy('order.paid_time')->first();
                if ($repaymentPlanFirst && $repaymentPlan['order_id'] == $repaymentPlanFirst->order_id && $repaymentPlan['paid_time'] < $repaymentPlanFirst->paid_time){
                    $this->systemAddBlack($order, RiskBlacklist::TYPE_OVERDUE, 'N');
                }
            }
        }
    }

    /**
     * 以clm匹配规则为原则，获取认为是同一客户的客户集
     */
    public function getUsersByClmRules($user) {
        /** 关联数据缓存1天 */
        $cacheKey = 'UserRelateUsers:' . $user->id;

        //测试,不缓存先
        MerchantHelper::setMerchantId($user->merchant_id);
        $birthday = $user->userInfo ? $user->userInfo->birthday : null;
        $birthday = $birthday ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;

//        $cardType = $user->card_type;
        $cardType = str_replace(" ","",$user->card_type);
//        $idCardNo = $user->id_card_no;
        $idCardNo = str_replace(" ","",$user->id_card_no);

        $telephone = $user->telephone;
        $fullname = str_replace(" ","",$user->fullname);

        $query = User::query()->join('user_info','user.id','=','user_info.user_id')->withoutGlobalScopes();

        $isConditionEmpty = true;
        // 1、证件类型+证件号
        if ($cardType && $idCardNo) {
            $query->orWhere(function ($query) use ($cardType, $idCardNo) {
//                $query->where('idcard_type', $cardType)
                $query->where(\DB::raw("REPLACE(`card_type`,' ','')"), $cardType)
                    ->where(\DB::raw("REPLACE(`id_card_no`,' ','')"), $idCardNo);
//                        ->where('idcard', $idCardNo);
            });
            $isConditionEmpty = false;
        }

        // 2、手机号+姓名
        if ($telephone && $fullname) {
            $query->orWhere(function ($query) use ($telephone, $fullname) {
                $query->where('telephone', $telephone)
                    ->where(\DB::raw("REPLACE(`fullname`,' ','')"), $fullname);
            });
            $isConditionEmpty = false;
        }

        // 3、姓名+出生日期年份--新需求202109
        if ($fullname && $birthday) {
            $query->orWhere(function ($query) use ($fullname, $birthday) {
                $query->where(\DB::raw("REPLACE(`fullname`,' ','')"), $fullname)
//                        ->where('birthday', $birthday);
                    ->where(\DB::raw("RIGHT(`user_info`.birthday,4)"), date('Y',strtotime($birthday)));
            });
            $isConditionEmpty = false;
        }
        // 4、 电话+出生日期的年-- 202109
        if ($telephone && $birthday) {
            $query->orWhere(function ($query) use ($telephone, $birthday) {
                $query->where('telephone', $telephone)
                    ->where(\DB::raw("RIGHT(`user_info`.birthday,4)"), date('Y', strtotime($birthday)));
            });
            $isConditionEmpty = false;
        }

        if ($isConditionEmpty) {
            throw new \Exception('用户不满足匹配条件');
        }

        return $query->distinct()->pluck('user.id')->toArray();

        return Cache::remember($cacheKey, 24 * 60, function () use ($user) {
            MerchantHelper::setMerchantId($user->merchant_id);
            $birthday = $user->userInfo ? $user->userInfo->birthday : null;
            $birthday = $birthday ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;

//        $cardType = $user->card_type;
            $cardType = str_replace(" ","",$user->card_type);
//        $idCardNo = $user->id_card_no;
            $idCardNo = str_replace(" ","",$user->id_card_no);

            $telephone = $user->telephone;
            $fullname = str_replace(" ","",$user->fullname);

            $query = User::query()->join('user_info','user.id','=','user_info.user_id')->withoutGlobalScopes();

            $isConditionEmpty = true;
            // 1、证件类型+证件号
            if ($cardType && $idCardNo) {
                $query->orWhere(function ($query) use ($cardType, $idCardNo) {
//                $query->where('idcard_type', $cardType)
                    $query->where(\DB::raw("REPLACE(`card_type`,' ','')"), $cardType)
                        ->where(\DB::raw("REPLACE(`id_card_no`,' ','')"), $idCardNo);
//                        ->where('idcard', $idCardNo);
                });
                $isConditionEmpty = false;
            }

            // 2、手机号+姓名
            if ($telephone && $fullname) {
                $query->orWhere(function ($query) use ($telephone, $fullname) {
                    $query->where('telephone', $telephone)
                        ->where(\DB::raw("REPLACE(`fullname`,' ','')"), $fullname);
                });
                $isConditionEmpty = false;
            }

            // 3、姓名+出生日期年份--新需求202109
            if ($fullname && $birthday) {
                $query->orWhere(function ($query) use ($fullname, $birthday) {
                    $query->where(\DB::raw("REPLACE(`fullname`,' ','')"), $fullname)
//                        ->where('birthday', $birthday);
                        ->where(\DB::raw("RIGHT(`user_info`.birthday,4)"), date('Y',strtotime($birthday)));
                });
                $isConditionEmpty = false;
            }
            // 4、 电话+出生日期的年-- 202109
            if ($telephone && $birthday) {
                $query->orWhere(function ($query) use ($telephone, $birthday) {
                    $query->where('telephone', $telephone)
                        ->where(\DB::raw("RIGHT(`user_info`.birthday,4)"), date('Y', strtotime($birthday)));
                });
                $isConditionEmpty = false;
            }

            if ($isConditionEmpty) {
                throw new \Exception('用户不满足匹配条件');
            }

            return $query->distinct()->pluck('user.id')->toArray();
        });
    }

    /**
     * 关联入黑
     * 1、当客户1-10项信息命中该部分黑名单时，该客户当前的1-10项信息加入对应黑名单
     * 2、当客户单位电话命中单位电话、本人手机号码或者联系人手机号码黑名单时，客户的1-10项信息加入对应黑名单
     * 3、当客户IP或者银行号段命中黑名单时，客户的1-10项信息加入对应黑名单
     * @param Order $order
     * @return bool true命中关联黑名单 false没命中
     */
    public function relateAddBlack($order) {
        if (empty($order))
            throw new \Exception('关联入黑 - 订单不存在');
        $userRelateData = $this->getUserRelateData($order->user);
        $merchantId = $order->merchant_id;

        /** 1、当客户1-10项信息命中该部分黑名单时，该客户当前的1-10项信息加入对应黑名单 */
        $relateCheckItems = ArrayHelper::filterArray(array_only($userRelateData, RiskBlacklist::KEYWORD_SYSTEM));
        $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->where(function ($query) use ($merchantId) {
                    $query->orWhere('is_global', RiskBlacklist::IS_GLOBAL_YES)->orWhere('merchant_id', $merchantId);
                })->whereIn('keyword', RiskBlacklist::KEYWORD_SYSTEM);
        $filterValue = [];
        foreach ((array) $relateCheckItems as $checkKey => $checkItem) {
            if (is_array($checkItem)) {
                if (empty($checkItem))
                    continue;
                $filterValue = array_merge($filterValue, $checkItem);
            } else {
                $filterValue[] = $checkItem;
            }
        }
        $query->whereIn('value', $filterValue);
        if ($query->exists()) {
            $relatedInfo = $query->pluck('value', 'keyword')->toArray();
            $this->systemAddBlack($order, RiskBlacklist::TYPE_RELATE, false, $relatedInfo);
            return true;
        }

        /** 2、当客户单位电话命中单位电话、本人手机号码或者联系人手机号码黑名单时，客户的1-10项信息加入对应黑名单 */
        $workPhone = array_get($userRelateData, RiskBlacklist::KEYWORD_WORK_TELEPHONE);
        if ($workPhone) {
            $needleKeyword = [RiskBlacklist::KEYWORD_WORK_TELEPHONE, RiskBlacklist::KEYWORD_TELEPHONE, RiskBlacklist::KEYWORD_CONTACT_TELEPHONE];
            $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->where(function ($query) use ($merchantId) {
                        $query->orWhere('is_global', RiskBlacklist::IS_GLOBAL_YES)->orWhere('merchant_id', $merchantId);
                    })->whereIn('keyword', $needleKeyword)->whereValue($workPhone);
            if ($query->exists()) {
                $relatedInfo = [RiskBlacklist::KEYWORD_WORK_TELEPHONE => $workPhone];
                $this->systemAddBlack($order, RiskBlacklist::TYPE_RELATE, false, $relatedInfo);
                return true;
            }
        }

        /** 3、当客户IP或者银行号段命中黑名单时，客户的1-10项信息加入对应黑名单 */
        $iPs = array_get($userRelateData, RiskBlacklist::KEYWORD_IP, []);
        $bankCardCut3 = array_get($userRelateData, RiskBlacklist::KEYWORD_BANKCARD_CUT3, []);
        $bankCardCut4 = array_get($userRelateData, RiskBlacklist::KEYWORD_BANKCARD_CUT4, []);
        $bankCardCut5 = array_get($userRelateData, RiskBlacklist::KEYWORD_BANKCARD_CUT5, []);
        $bankCardCut6 = array_get($userRelateData, RiskBlacklist::KEYWORD_BANKCARD_CUT6, []);
        $bankParts = array_merge($bankCardCut3, $bankCardCut4, $bankCardCut5, $bankCardCut6);
        if ($iPs || $bankParts) {
            $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->where(function ($query) use ($merchantId) {
                        $query->orWhere('is_global', RiskBlacklist::IS_GLOBAL_YES)->orWhere('merchant_id', $merchantId);
                    })->where(function ($query) use ($iPs, $bankParts) {
                $iPs && $query->orWhere(function ($query) use ($iPs) {
                            $query->whereIn('value', $iPs)->where('keyword', RiskBlacklist::KEYWORD_IP);
                        });
                $bankParts && $query->orWhere(function ($query) use ($bankParts) {
                            $query->whereIn('value', $bankParts)->whereIn('keyword', RiskBlacklist::KEYWORD_BANKCARD_GROUP);
                        });
            });
            if ($query->exists()) {
                $relatedInfo = $query->pluck('value', 'keyword')->toArray();
                $this->systemAddBlack($order, RiskBlacklist::TYPE_RELATE, false, $relatedInfo);
                return true;
            }
        }
        return false;
    }

    /**
     * 关联入黑失效
     * 针对关联入黑，先对关联信息（related_info）进行拆分：
    1）如果关联信息对应的黑名单都已失效（status=2），则此关联入黑记录也失效，即status改为2
    2）如果关联信息对应的黑名单有未失效的记录（status=1），则不做任何处理
     * @param Order $order
     * @return bool true命中关联黑名单 false没命中
     */
    public function relateDelBlack($riskBlacklist) {
        if (empty($riskBlacklist))
            throw new \Exception('关联入黑失效 - 记录不存在');
        $related_info = json_decode($riskBlacklist['related_info'],true);
        if (count($related_info)){
            $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE);
//                ->where('merchant_id',4);
            foreach ($related_info as $k=>$v){
                $queryCopy = clone $query;
//               if (str_contains($k,'BANKCARD')){
//                   $queryCopy->where('keyword','BANKCARD')->where('value','like',$v.'%');
//               }else{
                   $queryCopy->where('keyword',$k)->where('value',$v);
//               }
               if ($queryCopy->exists()) {
                  return;
                }
            }
            \DB::table('risk_blacklist')->where('apply_id', $riskBlacklist['apply_id'])
                ->update(['status' => RiskBlacklist::STATUS_DISABLE]);
        }
    }

    /**
     * 检查用户是否命中黑名单
     * 先全局后单品牌黑名单，全局或者单品牌内部按照：证件>银行卡>银行卡号段>ip>设备类ID>本人电话号码>本人单位电话>联系人号码>email>姓名出生日期组合顺序
     * @param Order $order
     */
    public function hitBlacklist($order) {
        if (empty($order))
            throw new \Exception('关联入黑 - 订单不存在');
        if ($order->quality == Order::QUALITY_OLD) {
            return $this->outputError('复贷不走黑名单');
        }
        $userRelateData = $this->getUserRelateData($order->user);
        $merchantId = $order->merchant_id;
        /** 严格按照顺序执行 */
        $needCheckKeyword = array_keys(RiskBlacklist::KEYWORD_ALIAS);
        /** 全局匹配 */
        foreach ($needCheckKeyword as $keyword) {
            $isGlobal = RiskBlacklist::IS_GLOBAL_YES;
            /** 不存在校验集合跳过 */
            if (!$checkValues = array_get($userRelateData, $keyword))
                continue;
            $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->whereIsGlobal($isGlobal)
                            ->whereKeyword($keyword)->whereIn('value', (array) $checkValues);
            if ($query->exists()) {
                return $this->outputSuccess("命中黑名单", array_values([
                            'refusal_code' => ApproveRuleService::server()->getCodeByBlacklistKeyword($keyword, $isGlobal),
                            'hit_keyword' => $keyword,
                            'hit_values' => $query->pluck('value')->toArray()
                ]));
            }
        }
        /** 单品牌匹配 */
        foreach ($needCheckKeyword as $keyword) {
            $isGlobal = RiskBlacklist::IS_GLOBAL_NO;
            /** 不存在校验集合跳过 */
            if (!$checkValues = array_get($userRelateData, $keyword))
                continue;
            $query = RiskBlacklist::query()->withoutGlobalScopes()->whereStatus(RiskBlacklist::STATUS_ACTIVE)->whereMerchantId($merchantId)
                            ->whereKeyword($keyword)->whereIn('value', (array) $checkValues);
            if ($query->exists()) {
                return $this->outputSuccess("命中黑名单", array_values([
                            'refusal_code' => ApproveRuleService::server()->getCodeByBlacklistKeyword($keyword, $isGlobal),
                            'hit_keyword' => $keyword,
                            'hit_values' => $query->pluck('value')->toArray()
                ]));
            }
        }
        return $this->outputError('未命中黑名单');
    }

    /**
     * 获取用户关联信息
     * @param User $user
     * @return array
     */
    private function getUserRelateData($user) {
        /** 关联数据缓存1天 */
        $cacheKey = 'UserRelateData:0922:' . $user->id;
        return Cache::remember($cacheKey, 24 * 60, function () use ($user) {
                    /** 所取数据均包含历史失效过期数据 & 去重处理 */
                    $userContacts = UserContact::whereUserId($user->id)->whereNotIn("contact_telephone", ['9876543211', '9876543212', '9876543213'])->distinct()->pluck('contact_telephone')->toArray();
                    $bankCards = BankCardPeso::whereUserId($user->id)->distinct()->pluck('account_no')->toArray();
                    $deviceIds = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('imei')->toArray();
                    $persistentDeviceIds = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('persistent_device_id')->toArray();
                    $cookieIds = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('cookie_id')->toArray();
                    $advertisingIds = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('advertising_id')->toArray();
                    $wifiIps = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('wifi_ip')->toArray();
                    $ipv6Ips = UserPhoneHardware::whereUserId($user->id)->distinct()->pluck('request_ip')->toArray();
                    /** 处理银行号段 */
                    $bankCardCut3 = $bankCardCut4 = $bankCardCut5 = $bankCardCut6 = [];
                    foreach ((array) $bankCards as $bankCard) {
                        $bankCardCut3[] = substr($bankCard, 0, strlen($bankCard) - 3); //银行卡去掉后3位
                        $bankCardCut4[] = substr($bankCard, 0, strlen($bankCard) - 4); //银行卡去掉后4位
                        $bankCardCut5[] = substr($bankCard, 0, strlen($bankCard) - 5); //银行卡去掉后5位
                        $bankCardCut6[] = substr($bankCard, 0, strlen($bankCard) - 6); //银行卡去掉后6位
                    }
                    /** 剔除空值 */
                    return ArrayHelper::filterArray([
                                RiskBlacklist::KEYWORD_TELEPHONE => $user->telephone, //本人手机号
                                RiskBlacklist::KEYWORD_CONTACT_TELEPHONE => $userContacts, //联系人手机号码
                                RiskBlacklist::KEYWORD_ID_CARD_NO => $user->id_card_no, //证件号
                                RiskBlacklist::KEYWORD_EMAIL => $user->userInfo->email ?? '', //email
                                RiskBlacklist::KEYWORD_BANKCARD => $bankCards, //银行卡
                                RiskBlacklist::KEYWORD_BANKCARD_CUT3 => $bankCardCut3, //银行卡
                                RiskBlacklist::KEYWORD_BANKCARD_CUT4 => $bankCardCut4, //银行卡
                                RiskBlacklist::KEYWORD_BANKCARD_CUT5 => $bankCardCut5, //银行卡
                                RiskBlacklist::KEYWORD_BANKCARD_CUT6 => $bankCardCut6, //银行卡
                                #停止如黑,这几类字段app有默认值不能入黑
                                #RiskBlacklist::KEYWORD_DEVICE_ID => $deviceIds, //deviceId
                                #RiskBlacklist::KEYWORD_COOKIE_ID => $cookieIds, //cookieid
                                #RiskBlacklist::KEYWORD_ADVERTISING_ID => $advertisingIds, //AdvertisingId
                                #RiskBlacklist::KEYWORD_PERSISTENT_DEVICE_ID => $persistentDeviceIds, //persistentDeviceId
                                RiskBlacklist::KEYWORD_NAME_BIRTHDAY => isset($user->fullname) && isset($user->userInfo->birthday) ? "{$user->fullname}|{$user->userInfo->birthday}" : '', // 姓名|生日 组合
                                RiskBlacklist::KEYWORD_IP => array_merge($wifiIps, $ipv6Ips), //IP
                                RiskBlacklist::KEYWORD_WORK_TELEPHONE => $user->userWork->work_phone ?? '', //单位电话
                    ]);
                });
    }

}
