<?php

namespace Risk\Common\Services\SystemApprove\RuleExecTrait;

use Illuminate\Database\Eloquent\Collection;
use Risk\Common\Helper\CityHelper;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Services\Risk\RiskAppHitServer;
use Risk\Common\Services\Risk\RiskCommonTelephoneHitServer;
use Risk\Common\Services\SystemApprove\RuleData\RuleDataInterface;

/**
 * Trait SystemApproveRuleTrait
 * 此处全为拒绝规则，返回 true 为命中规则，命中规则为拒绝
 * @package Common\Services\SystemApprove
 * @property RuleDataInterface $ruleData
 */
trait SystemApproveRuleTrait {
    /*     * ***********************************************************************************************************
     * Rule start
     * ********************************************************************************************************** */

    /*     * ****************************************** 申请规则 ****************************************************** */

    /**
     * 提交订单时的ip地址不在印度
     * @return bool
     */
    public function ruleApplyIpNotInIndia() {
        $applyIpNotInIndia = $this->ruleData->applyIpNotInIndia();

        $this->addCurrentRuleValue($applyIpNotInIndia);

        return $applyIpNotInIndia;
    }

    /**
     * 提交订单时的GPS地址不在印度
     * @return bool
     */
    public function ruleApplyGpsNotInIndia() {
        $applyGpsNotInIndia = $this->ruleData->applyGpsNotInIndia();

        $this->addCurrentRuleValue($applyGpsNotInIndia);

        return $applyGpsNotInIndia;
    }

    /**
     * 用户本次申请之前app列表中包含小于[n]个[list]列表app
     * @param $n
     * @param $appList
     * @return bool
     */
    public function ruleApplyAllApplicationInTopAppCount($n, $appList) {
        $userAllAppList = $this->ruleData->getAllApplication();

        $intersect = RiskAppHitServer::server()->intersect(
                array_column($userAllAppList, 'app_name'),
                $appList
        );

        $hasCount = count($intersect);

        $this->addCurrentRuleValue($intersect);

        return $hasCount < $n;
    }

    /**
     * 用户在[list]时间段提交订单且婚姻状况为[list]且学历为[list]且性别为[list]
     * @param $timeRanges
     * @param $maritalList
     * @param $educationalList
     * @param $genderList
     * @return bool
     */
    public function ruleApplyTimeRangeMarriageEducationGenderMultiple($timeRanges, $maritalList, $educationalList, $genderList) {
        $orderCreateTime = $this->ruleData->getOrderCreateTime();

        $timeInRangeRes = false;
        foreach ($timeRanges as $timeRange) {
            $timeInRange = $this->_timeInRange($orderCreateTime, $timeRange);
            if ($timeInRange) {
                $timeInRangeRes = true;
            }
        }

        $maritalStatus = $this->ruleData->getMaritalStatus();
        $education = $this->ruleData->getEducation();
        $gender = $this->ruleData->getGender();

        $this->addCurrentRuleValue([
            'timeInRange' => $timeInRangeRes,
            'marital' => $maritalStatus,
            'education' => $education,
            'gender' => $gender,
        ]);

        // 规则必须有不为空的，若设定全为空，则置为false，实际不生效规则
        $listHasNotNull = $maritalList || $educationalList || $genderList;

        $maritalInList = $maritalList ? in_array($maritalStatus, $maritalList) : true;
        $educationInList = $educationalList ? in_array($education, $educationalList) : true;
        $genderInList = $genderList ? in_array($gender, $genderList) : true;

        return $listHasNotNull && $timeInRangeRes && $maritalInList && $educationInList && $genderInList;
    }

    /**
     * 注册渠道ID在列表[list]
     * @param $opt
     * @return bool
     */
    public function ruleApplyChannelIdInList($opt) {
        // TODO 风控迁移，暂不支持此规则
        return false;
        $channel = $this->user->channel;

        $this->addCurrentRuleValue(optional($channel)->id);

        if (!$channel) {
            return false;
        }

        return in_array($channel->id, $opt);
    }

    /**
     * 紧急联系人号码与sim卡抓取的手机号一致
     * @return bool
     */
    public function ruleApplyEmergencyContactsEqualSimTel() {
        $simPhone = $this->ruleData->getUserPhoneSimPhone();
        $emergencyContacts = $this->ruleData->getEmergencyContactsTelephoneInitial();

        $this->addCurrentRuleValue(['simPho' => $simPhone, 'contacts' => $emergencyContacts]);
        if (is_null($simPhone)) {
            return false;
        }
        if (in_array($simPhone, $emergencyContacts)) {
            return true;
        }

        return false;
    }

    /**
     * 用户绑定放款银行卡在被拒列表[list]【拒绝】
     * @param array $rejectedList
     * @return bool
     */
    public function ruleApplyBankInRejectedList(array $rejectedList) {
        $bankName = optional($this->ruleData->getUserBankCard())->bank_name;

        $this->addCurrentRuleValue($bankName);

        if (!$bankName) {
            return false;
        }
        $bankName = str_replace(' ', '', strtolower(trim($bankName)));
        $rejectedList = array_map(function ($item) {
            return str_replace(' ', '', strtolower(trim($item)));
        }, $rejectedList);

        return in_array($bankName, $rejectedList);
    }

    /**
     * 本次申请采集的剔重设备数>[count] 拒绝
     * @param $count
     * @return int|null
     */
    public function ruleApplyDistinctDeviceCount($count) {
        return $this->ruleData->deviceApplyUniqueCount() > $count;
    }

    /**
     * OCR读取姓名比对(pancard&aadhaar)
     * @return bool
     */
    public function ruleAuthOcrNameComparison() {
        $pancardNameComparisonIsTrue = $this->ruleData->getPancardNameComparisonResult();
        $pancardOcrVerfiy = $this->ruleData->getPancardOcrNameVerfiy();

        $this->addCurrentRuleValue(['pancardIsTrue' => $pancardNameComparisonIsTrue, 'pancardOcrIsTrue' => $pancardOcrVerfiy]);

        return !$pancardNameComparisonIsTrue || !$pancardOcrVerfiy;
    }

    public function ruleContactsHitCommonTelephoneCnt($cnt) {
        $telephones = $this->ruleData->getContactsTelephone('contact_telephone');

        $instantLoan = RiskCommonTelephoneHitServer::server()->comparisonPhoneList($telephones, RiskCommonTelephoneHitServer::TYPE_INSTANT_LOAN);
        $personal = RiskCommonTelephoneHitServer::server()->comparisonPhoneList($telephones, RiskCommonTelephoneHitServer::TYPE_PERSONAL);
        $finance = RiskCommonTelephoneHitServer::server()->comparisonPhoneList($telephones, RiskCommonTelephoneHitServer::TYPE_FINANCE);
        $collection = RiskCommonTelephoneHitServer::server()->comparisonPhoneList($telephones, RiskCommonTelephoneHitServer::TYPE_COLLECTION);
        $loanapp = RiskCommonTelephoneHitServer::server()->comparisonPhoneList($telephones, RiskCommonTelephoneHitServer::TYPE_LOANAPP);

        $this->addCurrentRuleValue([
            'instantLoan' => count($instantLoan),
            'personal' => count($personal),
            'finance' => count($finance),
            'collection' => count($collection),
            'loanapp' => count($loanapp),
        ]);

        // 规则未明确上线
        return false;
    }

    /**
     * 年龄不符合要求[min,max]，包含边界值
     * @param array $ageRange
     * @return bool
     */
    protected function ruleApplyAgeUnqualified(array $ageRange) {
        $age = $this->ruleData->getAge();

        $this->addCurrentRuleValue($age);

        return !$age || ($age < $ageRange[0] || $age > $ageRange[1]);
    }

    /**
     * 有小于[day]天内被审批拒绝订单
     * @param $day
     * @return bool
     */
    protected function ruleApplyHasRejectedOrders($day) {
        $res = $this->ruleData->hasRejectedOrders($day);

        $this->addCurrentRuleValue($res);

        return $res;
    }

    /**
     * 学历在被拒列表[option1,option2,option3]
     * @param array $option
     * @return bool
     */
    protected function ruleApplyEducationUnqualified(array $option) {
        $education = $this->ruleData->getEducation();

        $this->addCurrentRuleValue($education);

        return in_array($education, $option);
    }

    /**
     * 职业类型在被拒列表[option1,option2,option3]
     * @param array $option
     * @return bool
     */
    protected function ruleApplyCareerTypeUnqualified(array $option) {
        $careerType = $this->ruleData->getCareerType();

        $this->addCurrentRuleValue($careerType);

        return in_array($careerType, $option);
    }

    /**
     * 首次申请贷款资料填写时间小于[minute]分钟
     * @param $minute
     * @return bool
     */
    protected function ruleApplyFirstLoanInformationFillingTime($minute) {
        $firstLoanInformationFillingTime = $this->ruleData->getFirstLoanInformationFillingTime();

        $this->addCurrentRuleValue($firstLoanInformationFillingTime);

        return $firstLoanInformationFillingTime && ($firstLoanInformationFillingTime < $minute);
    }

    /**
     * 申请时间在范围[H:i-H:i]，且当日已创建大于等于[n]笔订单
     * @param $timeRange
     * @param $cnt
     * @return bool
     */
    protected function ruleApplyCreateTime($timeRange, $cnt) {
        $orderCreateTime = $this->ruleData->getOrderCreateTime();

        $this->addCurrentRuleValue($orderCreateTime);

        $res = $this->_timeInRange($orderCreateTime, $timeRange);

        // 时间在被拒区间
        $count = false;
        if ($res) {
            $count = $this->ruleData->getDailyCreateOrderCount();
            $res = $count >= $cnt;
        }

        $this->addCurrentRuleValue(['created_time' => $orderCreateTime, 'count' => $count]);

        return $res;
    }

    private function _timeInRange($time, array $timeRange, bool $nullDefault = true) {
        $time = strtotime($time);
        if (!$time) {
            return $nullDefault;
        }

        $date = date('Y-m-d ', $time);

        $startTime = strtotime($date . $timeRange[0]);
        $endTime = strtotime($date . $timeRange[1]);

        return $time >= $startTime && $time <= $endTime;
    }

    /*     * ****************************************** 设备验证 ****************************************************** */

    /**
     * 相关住址在被拒列表[option1,option2,option3].
     * 地址指GPS地址或永久住址.
     * @param array $option
     * @return bool
     * @see CityHelper 区域划分
     */
    protected function ruleApplyAddressUnqualified(array $option) {
        $stateProbableValue = $this->ruleData->getAddressStateProbableValue();

        $this->addCurrentRuleValue($stateProbableValue);

        if (!$option) {
            return false;
        }

        return $this->_stateHaveSameValue($option, $stateProbableValue);
    }

    /*     * ***********************************************************************************************************
     *
     * ********************************************************************************************************** */

    private function _stateHaveSameValue(array $optionState, array $states) {
        $stateScope = [];
        foreach ($optionState as $item) {
            $stateScope = array_merge($stateScope, CityHelper::helper()->getStateMoreName($item) ?? []);
        }

        $strdispose = function ($str) {
            return str_replace([" ", "　"], '', mb_convert_case($str, MB_CASE_UPPER, "UTF-8"));
        };

        $res = false;
        foreach ($stateScope as $item) {
            $item = $strdispose($item);
            foreach ($states as $state) {
                $state = $strdispose($state);
                if (strpos($state, $item) !== false) {
                    return true;
                }
            }
        }

        return $res;
    }

    /**
     * 小于等于[num]天内，半径[num]内有大于等于[num]笔申请订单
     * @param $day
     * @param $m
     * @param $cnt
     * @return bool
     */
    protected function ruleApplyGpsRadiusLimit($day, $m, $cnt) {
        $count = $this->ruleData->hasApplyWithinRadiusCount($day, $m);

        $this->addCurrentRuleValue($count);

        return $count >= $cnt;
    }

    /**
     * 开始验证流程的时间在[min,max]内(取基础信息认证完成时间)
     * @param $timeRange
     * @return bool
     */
    protected function ruleApplyStartVerifyTime($timeRange) {
        $startVerifyTime = (string) $this->ruleData->getUserStartVerifyTime();

        $this->addCurrentRuleValue($startVerifyTime);

        return $this->_timeInRange($startVerifyTime, $timeRange, false);
    }

    /**
     * 同一设备申请次数大于[count]
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameApplyCount($count) {
        $applyCount = $this->ruleData->deviceSameApplyCount();

        $this->addCurrentRuleValue($applyCount);

        return $applyCount > $count;
    }

    /**
     * 同一设备指定天数小于等于[day]内申请次数大于[count]
     * @param $day
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameTimeRangeApplyCount($day, $count) {
        $applyCount = $this->ruleData->deviceSameTimeRangeApplyCount($day);

        $this->addCurrentRuleValue($applyCount);

        return $applyCount > $count;
    }

    /**
     * 同一设备当前正在申请(处于审批阶段)的笔数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameInApproveCount($count) {
        $inApproveCount = $this->ruleData->deviceSameInApproveCount();

        $this->addCurrentRuleValue($inApproveCount);

        return $inApproveCount >= $count;
    }

    /**
     * 同一设备逾期大于等于[m]天的次数大于等于[n]
     * @param $day
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameOverdueCount($day, $count) {
        $overdueCount = $this->ruleData->deviceSameOverdueCount($day);

        $this->addCurrentRuleValue($overdueCount);

        return $overdueCount >= $count;
    }

    /**
     * 同一设备匹配 身份标识 个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameMatchingIdentity($count) {
        // PAN CARD
        $matchingPancard = $this->ruleData->deviceSameMatchingPancard();
        $panCardRes = $matchingPancard >= $count;

        // Addhaar Card
        $matchingAddhaar = $this->ruleData->deviceSameMatchingAddhaar();
        $addhaarCardRes = $matchingAddhaar >= $count;

        $this->addCurrentRuleValue("pancard:{$matchingPancard},addhaar:{$matchingAddhaar}");

        return $panCardRes || $addhaarCardRes;
    }

    /**
     * 同一设备匹配手机号个数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleDeviceSameMatchingTelephone($count) {
        $matchingTelephone = $this->ruleData->deviceSameMatchingTelephone();

        $this->addCurrentRuleValue($matchingTelephone);

        return $matchingTelephone >= $count;
    }

    /*     * ****************************************** 身份验证 ****************************************************** */

    /**
     * 设备类型验证
     * 暂时只有安卓设备，给全过
     * @return bool
     */
    protected function ruleDeviceType() {
        $deviceType = $this->ruleData->getDeviceType();

        $this->addCurrentRuleValue('');

        // 暂时只有安卓设备，不命中(给全过)
        return false;
    }

    /**
     * 手机内存小于[count]M
     * @param $limit
     * @return bool
     */
    protected function ruleDevicePhoneMemory($limit) {
        $memory = $this->ruleData->getUserPhoneMemory();

        $this->addCurrentRuleValue($memory);

        if (is_null($memory)) {
            return false;
        }

        return $memory < $limit;
    }

    /**
     * 手机剩余内存小于等于[count]M
     * @param $limit
     * @return bool
     */
    protected function ruleDevicePhoneFreeMemory($limit) {
        $memory = $this->ruleData->getUserPhoneMemory('free_rom');

        $this->addCurrentRuleValue($memory);

        if (is_null($memory)) {
            return false;
        }

        return $memory <= $limit;
    }

    /**
     * 申请设备号变更(本次申请设备号与上次申请设备号不相同)
     * @return bool
     */
    protected function ruleDeviceUniqueNumberChange() {
        $isChange = $this->ruleData->deviceUniqueNumberIsChange();

        $this->addCurrentRuleValue($isChange);

        return $isChange;
    }

    /**
     * 同一银行卡匹配 身份标识 个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD
     * @param $count
     * @return bool
     */
    protected function ruleAuthSameBankCardMatchingIdentity($count) {
        // PAN CARD
        $matchingPanCard = $this->ruleData->authSameBankCardMatchingPanCard();
        $panCardRes = $matchingPanCard >= $count;

        // Addhaar Card
        $matchingAddhaar = $this->ruleData->authSameBankCardMatchingAddhaar();
        $addhaarCardRes = $matchingAddhaar >= $count;

        $this->addCurrentRuleValue("pan:{$matchingPanCard},addhaar:{$matchingAddhaar}");

        return $panCardRes || $addhaarCardRes;
    }

    /**
     * 同一银行卡匹配手机号个数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleAuthSameBankCardMatchingTelephone($count) {
        $matchingTelephone = $this->ruleData->authSameBankCardMatchingTelephone();

        $this->addCurrentRuleValue($matchingTelephone);

        return $matchingTelephone >= $count;
    }

    /**
     * 同一银行卡[m]天内在其他商户有过超过[n]次成功放款记录
     * @param $day
     * @param $cnt
     * @return bool
     */
    protected function ruleAuthSameBankCardMatchingMerchantPaidOrder($day, $cnt) {
        $paidCount = $this->ruleData->getBankCardMatchingMerchantPaidOrder($day);

        $this->addCurrentRuleValue($paidCount);

        return $paidCount > $cnt;
    }

    /**
     * 同一 身份标识 匹配手机号个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD
     * @param $count
     * @return bool
     */
    protected function ruleAuthSameIdentityMatchingTelephone($count) {
        $matchingTelephone = $this->ruleData->authSameIdentityMatchingTelephone();

        $this->addCurrentRuleValue($matchingTelephone);

        return $matchingTelephone >= $count;
    }

    /**
     * 同一 身份标识 匹配设备号个数大于等于[count]. 身份标识 = Addhaar Card 或 PAN CARD
     * @param $count
     * @return bool
     */
    protected function ruleAuthSameIdentityMatchingDevice($count) {
        $matchingDevice = $this->ruleData->authSameIdentityMatchingDevice();

        $this->addCurrentRuleValue($matchingDevice);

        return $matchingDevice >= $count;
    }

    /*     * ****************************************** 通讯录验证 ****************************************************** */

    /**
     * 关联账户有小于[day]内被审批拒绝的订单
     * @param $day
     * @return bool
     */
    protected function ruleAuthRelateAccountHasRejectedOrders($day) {
        $res = $this->ruleData->authRelateAccountHasRejectedOrders($day);

        $this->addCurrentRuleValue($res);

        return $res;
    }

    /**
     * 关联账户有进行中(未完结&审批中)的订单
     * @return bool
     */
    protected function ruleAuthRelateAccountHasUnderwayOrder() {
        $res = $this->ruleData->authRelateAccountHasUnderwayOrder();

        $this->addCurrentRuleValue($res);

        return $res;
    }

    /**
     * 人脸匹配低于[count]拒绝
     * @return bool
     */
    protected function ruleAuthFaceMatch($score) {
        // 允许后台设置为0，表示这项认证总是通过
        if ($score == 0) {
            return false;
        }

        $faceScore = $this->ruleData->getUserFaceMatchScore();

        $this->addCurrentRuleValue($faceScore);

        return $faceScore < $score;
    }

    /**
     * 通讯录内正在申请(处于审批阶段)贷款的人数大于[count]
     * @return bool
     */
    protected function ruleContactsInApproveCount($count) {
        $inApproveCount = $this->ruleData->contactsInApproveCount();

        $this->addCurrentRuleValue($inApproveCount);

        return $inApproveCount > $count;
    }

    /**
     * 通讯录内当前处于逾期且天数大于等于[m]天贷款人数超过[n]
     * @param $day
     * @param $cnt
     * @return bool
     */
    protected function ruleContactsInOverdueCount($day, $cnt) {
        $inOverdueCount = $this->ruleData->contactsInOverdueCount($day);

        $this->addCurrentRuleValue($inOverdueCount);

        return $inOverdueCount > $cnt;
    }

    /**
     * 通讯录人数小于[count]
     * @param $count
     * @return bool
     */
    protected function ruleContactsCount($count) {
        $contactsCount = $this->ruleData->contactsCount();

        $this->addCurrentRuleValue($contactsCount);

        // 2020/02/29 未避免误杀，通讯录未0暂定通过
        if (!$contactsCount) {
            return false;
        }

        return $contactsCount < $count;
    }

    /*     * ****************************************** 借款行为 ****************************************************** */

    /**
     * 用户通讯录中有大于等于[n]个号码发生过注册行为（跨商户）
     * @param $cnt
     * @return bool
     */
    protected function ruleContactsRegisterCountAcrossMerchant($cnt) {
        $count = $this->ruleData->userByContactsTelephone(function ($users) {
            return $users->count();
        }, true);

        $this->addCurrentRuleValue($count);

        return $count >= $cnt;
    }

    /**
     * 用户通讯录中有大于等于[n]个号码发生过申请行为（跨商户）
     * @param $cnt
     * @return bool
     */
    protected function ruleContactsHasApplyCountAcrossMerchant($cnt) {
        $count = $this->ruleData->userByContactsTelephone(function (Collection $users) {
            return $users->load('order')->filter(function ($item) {
                        return (bool) $item->order;
                    })->count();
        }, true);

        $this->addCurrentRuleValue($count);

        return $count >= $cnt;
    }

    /**
     * 历史被拒次数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleBehaviorRejectCount($count) {
        $rejectCount = $this->ruleData->behaviorRejectCount();

        $this->addCurrentRuleValue($rejectCount);

        return $rejectCount >= $count;
    }

    /**
     * 历史逾期天数大于等于[m]天的次数大于等于[n]
     * @param $day
     * @param $count
     * @return bool
     */
    protected function ruleBehaviorOverdueCount($day, $count) {
        $overdueCount = $this->ruleData->behaviorOverdueCount($day);

        $this->addCurrentRuleValue($overdueCount);

        return $overdueCount >= $count;
    }

    /**
     * 历史最大逾期天数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleBehaviorMaxOverdueDays($count) {
        $maxOverdueDays = $this->ruleData->behaviorMaxOverdueDays();

        $this->addCurrentRuleValue($maxOverdueDays);

        return $maxOverdueDays >= $count;
    }

    /*     * ****************************************** 其他规则 ****************************************************** */

    /**
     * 上一笔订单逾期天数大于等于[count]
     * @param $count
     * @return bool
     */
    protected function ruleBehaviorLatelyOverdueDays($count) {
        $overdueDays = $this->ruleData->behaviorLatelyOverdueDays();

        $this->addCurrentRuleValue($overdueDays);

        return $overdueDays >= $count;
    }

    /**
     * 上一笔订单催收电话次数大于等于[count]. (只计已逾期订单的本人和紧急联系人的催记)
     * @param $count
     * @return bool
     */
    protected function ruleBehaviorLatelyCollectionTelephoneCount($count) {
        $collectionTelephoneCount = $this->ruleData->behaviorLatelyCollectionTelephoneCount();

        $this->addCurrentRuleValue($collectionTelephoneCount);

        return $collectionTelephoneCount >= $count;
    }

    /**
     * APP列表中贷款产品数大于[count]
     * @param $count
     * @return bool
     */
    protected function ruleOtherHasLoanAppCount($count) {
        $hasLoanAppCount = $this->ruleData->getHasLoanAppCount();

        $this->addCurrentRuleValue($hasLoanAppCount);

        return $hasLoanAppCount > $count;
    }

    /**
     * 安装时间距离申请时间[day]天内贷款产品APP数大于[count]
     * @param $day
     * @param $count
     * @return bool
     */
    protected function ruleOtherRecentInstallLoanAppCount($day, $count) {
        $recentInstallLoanAppCount = $this->ruleData->getRecentInstallLoanAppCount($day);

        $this->addCurrentRuleValue($recentInstallLoanAppCount);

        return $recentInstallLoanAppCount > $count;
    }

    /**
     * 关联账号逾期小于等于[day]天并结清的的订单数大于等于[cnt]个【拒绝】
     * @param $day
     * @param $cnt
     * @return bool
     */
    protected function ruleOtherRelateOverdueDaysFinishOrderCnt($day, $cnt) {
        $orders = $this->ruleData->getAssociatedOrderWithoutMerchantScope();

        $count = $orders->whereIn('status', [Order::STATUS_OVERDUE_FINISH])
                ->filter(function ($item) use ($day) {
                    if (!isset($item->lastRepaymentPlan) || !$item->lastRepaymentPlan->exists) {
                        return false;
                    }
                    return $item->lastRepaymentPlan->overdue_days <= $day;
                })
                ->count();

        $this->addCurrentRuleValue($count);

        return $count >= $cnt;
    }

    /*     * ***********************************************************************************************************
     * Rule end
     * ********************************************************************************************************** */

    /**
     * 全量高危app击中数大于等于[n]个【拒绝】
     * @param $cnt
     * @return bool
     */
    protected function ruleOtherAllHighRiskAppHitCnt($cnt) {
        $userAllAppList = $this->ruleData->getAllApplication();

        $userListAndHighRiskIntCnt = count(RiskAppHitServer::server()->comparisonHighRiskAppList(array_column($userAllAppList, 'app_name')));

        $this->addCurrentRuleValue($userListAndHighRiskIntCnt);

        return $userListAndHighRiskIntCnt >= $cnt;
    }

    /**
     * 最早高危app安装时间距离申请时间间隔（按天）小于等于[n]天【拒绝】
     * @param $d
     * @return bool
     */
    protected function ruleOtherFirstInstallTimeAndOrderCreateTimeDiffDays($d) {
        $userAllAppList = $this->ruleData->getAllApplication();

        $timeList = [];
        foreach ($userAllAppList as $item) {
            $inHighList = RiskAppHitServer::server()->comparisonHighRiskAppList(array_get($item, 'app_name'));
            if ($inHighList && isset($item['installed_time']) && $item['installed_time']) {
                $timeList[] = strlen($item['installed_time']) > 10 ? $item['installed_time'] / 1000 : $item['installed_time'];
            }
        }

        if (!$timeList) {
            return false;
        }

        $minTime = min($timeList);
        $diffDays = (time() - $minTime) / 86400;

        $this->addCurrentRuleValue(['minTime' => $minTime, 'diffDays' => $diffDays]);

        return $diffDays <= $d;
    }

    /**
     * 提交订单时的GPS地址不在印度
     * @return bool
     */
    public function ruleApplyInBlacklist() {
        $applyInBlacklist = $this->ruleData->applyInBlacklist();

        $this->addCurrentRuleValue($applyInBlacklist);

        return is_array($applyInBlacklist) ? true : false;
    }

    /*     * ****************自建规则 应用列表***************** */

    /**
     * app_antifraud_rule_0001
     */
    public function ruleAppAntifraudRule0001($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["new_bad_loan1_rate"]);
        return floatval($appListData["new_bad_loan1_rate"]) >= floatval($param);
    }

    /**
     * app_antifraud_rule_0002
     */
    public function ruleAppAntifraudRule0002($param) {
        $appListData = $this->ruleData->appListCount();

        $this->addCurrentRuleValue($appListData["new_bad_loan1_rate"]);
        return intval($appListData["install_loan_app1_l7d"]) >= intval($param);
    }

    /**
     * app_antifraud_rule_0003
     */
    public function ruleAppAntifraudRule0003($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["new_bad_loan2_loanrate"]);
        return floatval($appListData["new_bad_loan2_loanrate"]) >= floatval($param);
    }

    /**
     * app_antifraud_rule_0004
     */
    public function ruleAppAntifraudRule0004($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["name_cnt"]);
        return floatval($appListData["name_cnt"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0005
     */
    public function ruleAppAntifraudRule0005($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_loan_app1_l1d_lr"]);
        return floatval($appListData["install_loan_app1_l1d_lr"]) >= floatval($param);
    }

    /**
     * app_antifraud_rule_0005
     */
    public function ruleAppAntifraudRule0006($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_loan_app1_l3d_lr"]);
        return floatval($appListData["install_loan_app1_l3d_lr"]) >= floatval($param);
    }

    /**
     * app_antifraud_rule_0007
     */
    public function ruleAppAntifraudRule0007($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["loan_app1"]);
        return floatval($appListData["loan_app1"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0008
     */
    public function ruleAppAntifraudRule0008($param1, $param2, $param3) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue([
            "install_loan_app1_l1d_lr" => $appListData['install_loan_app1_l1d_lr'],
            "install_loan_app1_l1d" => $appListData['install_loan_app1_l1d']]);
        return floatval($appListData["install_loan_app1_l1d_lr"]) < floatval($param1) && floatval($appListData["install_loan_app1_l1d_lr"]) >= floatval($param2) && floatval($appListData["install_loan_app1_l1d"]) >= floatval($param3);
    }

    /**
     * app_antifraud_rule_0009
     */
    public function ruleAppAntifraudRule0009($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["new_bad_loan1_loanrate"]);
        return floatval($appListData["new_bad_loan1_loanrate"]) >= floatval($param);
    }

    /**
     * app_antifraud_rule_0010
     */
    public function ruleAppAntifraudRule0010($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_l1d"]);
        return floatval($appListData["install_l1d"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0011
     */
    public function ruleAppAntifraudRule0011($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_l3d"]);
        return floatval($appListData["install_l3d"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0012
     */
    public function ruleAppAntifraudRule0012($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_l7d"]);
        return floatval($appListData["install_l7d"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0013
     */
    public function ruleAppAntifraudRule0013($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_loan_app1_l1d"]);
        return floatval($appListData["install_loan_app1_l1d"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0014
     */
    public function ruleAppAntifraudRule0014($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_loan_app1_l1d_lr"]);
        return floatval($appListData["install_loan_app1_l1d_lr"]) <= floatval($param);
    }

    /**
     *
     */
    public function ruleThirdDataWhatsapp($param) {
        $res = $this->ruleData->thirdDataWhatsapp();
        $this->addCurrentRuleValue($res);
        return $res == '2';
    }

    /**
     * app_antifraud_rule_0015
     */
    public function ruleAppAntifraudRule0015($param) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue($appListData["install_loan_app1_l3d"]);
        return floatval($appListData["install_loan_app1_l3d"]) <= floatval($param);
    }

    /**
     * app_antifraud_rule_0016
     */
    public function ruleAppAntifraudRule0016($param1, $param2) {
        $appListData = $this->ruleData->appListCount();
        $this->addCurrentRuleValue(["install_loan_app1_l3d_lr" => $appListData["install_loan_app1_l3d_lr"]]);
        return floatval($appListData["install_loan_app1_l3d_lr"]) <= floatval($param1) || floatval($appListData["install_loan_app1_l3d_lr"]) >= floatval($param2);
    }

    /**
     * kudos 风控数据
     */
    public function ruleKudosCoverPan($param1, $param2) {
        $kudosRiskData = $this->ruleData->kudosRiskPan();
        return false;
    }

    /**
     * Kreditone 评分
     * @param type $param
     * @return boolean
     */
    public function ruleKreditoneScore($param) {
        $score = $this->ruleData->kreditoneScore();
        if (false === $score) {
            return false;
        } else {
            $this->addCurrentRuleValue($score);
            return $score < $param;
        }
    }

    public function ruleCheckOpenOrder($param) {
        $res = $this->ruleData->checkOpenOrders();
        $this->addCurrentRuleValue($res);
        if (false === $res) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *  新续贷规则**
     * 女性，且上一笔逾期天数超过N天，则拒绝；
     */
    public function ruleWomenLastLoanOverdueDays($param) {
        $gender = $this->ruleData->getGender();
        $overdueDays = $this->ruleData->behaviorLatelyOverdueDays();
        $this->addCurrentRuleValue($overdueDays);
        if ("Female" == $gender) {
            return $overdueDays > $param;
        }
        return false;
    }

    /**
     * 男性，且上一笔逾期天数超过N天，则拒绝；
     * @param type $param
     */
    public function ruleManLastLoanOverdueDays($param) {
        $gender = $this->ruleData->getGender();
        $overdueDays = $this->ruleData->behaviorLatelyOverdueDays();
        $this->addCurrentRuleValue($overdueDays);

        if ("Male" == $gender) {
            return $overdueDays > $param;
        }
        return false;
    }

    /**
     * 女性，历史逾期次数>5，申请金额>=3000，则拒绝；
     * @param type $param1
     * @param type $param2
     */
    public function ruleWomenHisOverdueDays($param1, $param2) {
        $gender = $this->ruleData->getGender();
        $overdueCount = $this->ruleData->behaviorOverdueCount(1);
        $principal = $this->ruleData->getOrderPrincipal();
        $this->addCurrentRuleValue(["overdueCount" => $overdueCount, "principal" => $principal]);
        if ("Female" == $gender) {
            return $overdueCount > $param1 && $principal >= $param2;
        }
        return false;
    }

    /**
     * 男性，历史逾期次数>3，申请金额>=3000，则拒绝；
     * @param type $param1
     * @param type $param2
     */
    public function ruleManHisOverdueDays($param1, $param2) {
        $gender = $this->ruleData->getGender();
        $overdueCount = $this->ruleData->behaviorOverdueCount(1);
        $principal = $this->ruleData->getOrderPrincipal();
        $this->addCurrentRuleValue(["overdueCount" => $overdueCount, "principal" => $principal]);
        if ("Male" == $gender) {
            return $overdueCount > $param1 && $principal >= $param2;
        }
        return false;
    }

    /**
     * 女性，education_level=other，历史逾期次数>4，申请金额>=3000，则拒绝；
     * @param type $param1
     * @param type $param2
     * @param type $param3
     */
    public function ruleWomenEduHisOverdueDays($param1, $param2, $param3) {
        $education = $this->ruleData->getEducation();
        $gender = $this->ruleData->getGender();
        $overdueCount = $this->ruleData->behaviorOverdueCount(1);
        $principal = $this->ruleData->getOrderPrincipal();
        $this->addCurrentRuleValue(["overdueCount" => $overdueCount, "principal" => $principal]);
        if ("Female" == $gender) {
            return $param1 == $education && $overdueCount > $param2 && $principal >= $param3;
        }
        return false;
    }

    /**
     * 男性，education_level=other，历史逾期次数>3，申请金额>=3000，则拒绝；
     * @param type $param1
     * @param type $param2
     * @param type $param3
     */
    public function ruleManEduHisOverdueDays($param1, $param2, $param3) {
        $education = $this->ruleData->getEducation();
        $gender = $this->ruleData->getGender();
        $overdueCount = $this->ruleData->behaviorOverdueCount(1);
        $principal = $this->ruleData->getOrderPrincipal();
        if ("Male" == $gender) {
            return $param1 == $education && $overdueCount > $param2 && $principal >= $param3;
        }
        return false;
    }

    public function ruleKudosUserScoreLow($param) {
        $kudosData = $this->ruleData->kudosRiskPan();
        $userScore = $this->ruleData->userSaasScore();
        $this->addCurrentRuleValue(["kudosData" => json_encode($kudosData), "userScore" => $userScore]);
        if ($kudosData['app_count'] != "" && $kudosData['loan_count'] != "") {
            return $kudosData['app_count'] >= $kudosData['loan_count'] && $kudosData['app_count'] != 0 && $userScore <= $param;
        }
        return false;
    }

    public function ruleKudosUserScoreKreditoneLow($param1, $param2) {
        $kudosData = $this->ruleData->kudosRiskPan();
        $userScore = $this->ruleData->userSaasScore();
        $kreditoneScore = $this->ruleData->kreditoneScore();
        $this->addCurrentRuleValue(["kudosData" => json_encode($kudosData), "userScore" => $userScore, "kreditoneScore" => $kreditoneScore]);
        if ($kudosData['app_count'] != '' && $kreditoneScore) {
            return $kudosData['app_count'] == 0 && $userScore <= $param1 && $kreditoneScore < $param2;
        }
        return false;
    }

    public function ruleKudosNull($param) {
        $kudosData = $this->ruleData->kudosRiskPan();
        $userScore = $this->ruleData->userSaasScore();
        $this->addCurrentRuleValue(["kudosData" => json_encode($kudosData), "userScore" => $userScore]);
        return $kudosData['app_count'] === '' && $userScore <= $param;
    }

    public function ruleNoWhatsappUserScoreLow($param) {
        $whatsapp = $this->ruleData->thirdDataWhatsapp();
        $userScore = $this->ruleData->userSaasScore();
        $this->addCurrentRuleValue(["whatsapp" => $whatsapp, "userScore" => $userScore]);
        return $whatsapp == '2' && $userScore <= $param;
    }

    /**
     *
     */
    public function ruleThirdDataAirudder($param) {
        $res = $this->ruleData->thirdDataAirudder();
        $this->addCurrentRuleValue($res);
        return $res !== false;
    }
}
