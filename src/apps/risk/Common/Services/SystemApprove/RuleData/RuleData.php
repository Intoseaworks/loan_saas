<?php

namespace Risk\Common\Services\SystemApprove\RuleData;

use Api\Services\Third\AirudderServer;
use Api\Services\Third\WhatsappServer;
use Carbon\Carbon;
use Common\Utils\Data\StringHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Risk\Common\Models\Business\BankCard\BankCard;
use Risk\Common\Models\Business\Order\Order;
use Risk\Common\Models\Business\Order\OrderDetail;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserAuth;
use Risk\Common\Models\Business\User\UserBlack;
use Risk\Common\Models\Business\User\UserBlacklist;
use Risk\Common\Models\Business\UserData\UserApplication;
use Risk\Common\Models\Business\UserData\UserContactsTelephone;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\Business\UserData\UserPosition;
use Risk\Common\Models\Common\Pincode;
use Risk\Common\Models\Third\ThirdReport;
use Risk\Common\Models\UserAssociated\UserAssociatedInfo;
use Risk\Common\Services\Risk\RiskAppHitServer;
use Risk\Common\Services\Risk\RiskOrderServer;
use Risk\Common\Services\Risk\RiskThirdDataServer;
use Risk\Common\Services\Risk\RiskUserAppServer;
use Risk\Common\Services\Risk\RiskUserServer;
use Risk\Common\Services\SystemApproveData\OrderCheckServer;
use Risk\Common\Services\SystemApproveData\UserThirdDataServer;
use Zhuzhichao\IpLocationZh\Ip;

class RuleData implements RuleDataInterface
{

    protected $order;
    protected $user;
    protected $applistData; //nio.wangapp列表预结算数据
    protected $defaultCacheDriver = 'file';
    protected $originalCacheDriver;
    protected $warringNoticeLock;

    public function __construct(Order $order) {
        $this->order = (new Order)->getOne($order->id);
        $this->user = $order->user;

        $this->originalCacheDriver = Cache::getDefaultDriver();
        Cache::setDefaultDriver($this->defaultCacheDriver);
    }

    public function __destruct() {
        Cache::setDefaultDriver($this->originalCacheDriver);
    }

    /**
     * 获取年龄
     * @return int|null
     */
    public function getAge(): ?int {
        $userInfo = $this->getUserInfo();

        $age = null;
        if (isset($userInfo)) {
            $dob = str_replace('/', '-', $userInfo->birthday);
            if (strtotime($dob) !== false) {
                $y1 = date("Y", strtotime($dob));
                $y2 = date("Y", time());

                $age = $y2 - $y1;
            }
        }

        return $age;
    }

    /**
     * @return mixed
     */
    protected function getUserInfo() {
        return $this->user->userInfo;
    }

    /**
     * 在 $day 天内（<）是否有拒批订单
     * @param $day
     * @return bool
     */
    public function hasRejectedOrders($day): bool {
        $orders = $this->getHisOrders();

        $deadline = date('Y-m-d', strtotime("-{$day}days", strtotime($this->order->created_at)));

        $res = $orders->whereIn('status', Order::APPROVAL_REJECT_STATUS)
            ->where('created_at', '>', $deadline)
            ->isNotEmpty();

        return $res;
    }

    protected function getHisOrders() {
        return $this->user->orders
            ->where('id', '!=', $this->order->id)
            ->where('created_at', '<', $this->order->created_at);
    }

    /**
     * 是否命中黑名单
     * @return bool
     */
    public function onBlackList(): bool {
        $exists = UserBlack::query()
            ->withoutGlobalScope(UserBlack::$bootScopeMerchant)
            ->whereTelephone($this->user->telephone)
            ->whereAppId(0)
            ->whereStatus(UserBlack::STATUS_NORMAL)
            ->exists();

        return $exists;
    }

    /**
     * 获取学历
     * @return string|null
     */
    public function getEducation(): ?string {
        return optional($this->getUserInfo())->education_level;
    }

    /**
     * 获取婚姻状况
     * @return string|null
     */
    public function getMaritalStatus() {
        return optional($this->getUserInfo())->marital_status;
    }

    /**
     * 获取宗教
     * @return mixed
     */
    public function getReligion() {
        return optional($this->getUserInfo())->religion;
    }

    /**
     * 获取性别
     * @return string|null
     */
    public function getGender() {
        return optional($this->getUserInfo())->gender;
    }

    /**
     * 获取地址信息
     * @return array
     */
    public function getAddressStateProbableValue(): array {
        $state = $this->getStateByPincode();

        $gpsState = optional($this->getPosition())->province;

        return [
            'state' => $state,
            'gpsState' => $gpsState,
        ];
    }

    /**
     * 获取 永久住址
     * @return mixed|string
     */
    public function getStateByPincode() {
        $pincode = optional($this->getUserInfo())->pincode ?? '';

        $pincodeModel = Pincode::query()->where('pincode', $pincode)->first();

        if (!$pincodeModel) {
            return '';
        }
        return $pincodeModel->statename;
    }

    /**
     * @return UserPosition|null
     */
    protected function getPosition() {
        $position = Cache::remember($this->getCacheKey('position', $this->user->id), 5, function () {
            $userPosition = UserPosition::getLastPosition($this->user->id);

            return $userPosition;
        });

        return $position;
    }

    protected function getCacheKey(...$keys) {
        array_unshift($keys, __CLASS__);

        if ($merchantId = MerchantHelper::getMerchantId()) {
            array_unshift($keys, $merchantId);
        }

        return implode(':', $keys);
    }

    /**
     * 获取职业类型
     * @return string|null
     */
    public function getCareerType(): ?string {
        return optional($this->getUserWork())->employment_type;
    }

    protected function getUserWork() {
        return $this->user->userWork;
    }

    /**
     * 根据 workplace_pincode 获取state
     * @return string
     */
    public function getStateByWorkplacePincode() {
        $pincode = optional($this->getUserWork())->workplace_pincode;

        $pincodeModel = (new Pincode())->getPincodeData($pincode);

        if (!$pincodeModel) {
            return '';
        }
        return $pincodeModel->statename;
    }

    /**
     * 首次申请贷款资料填写时间(分钟)
     * 取 用户注册时间 到首笔订单创建时间
     * @return int|null
     */
    public function getFirstLoanInformationFillingTime(): ?int {
        $firstOrder = $this->getHisOrders()->sortBy('id')->first();

        if (!$firstOrder) {
            $firstOrder = $this->order;
        }

        $userRegisterTime = strtotime($this->user->created_at);
        $firstOrderCreateTime = strtotime($firstOrder->created_at);

        $diffMinutes = (int) (($firstOrderCreateTime - $userRegisterTime) / 60);

        return $diffMinutes;
    }

    /**
     * 获取订单创建时间
     * @return string|null
     */
    public function getOrderCreateTime(): ?string {
        return $this->order->created_at;
    }

    /**
     * 获取同一设备订单申请数
     * @return int|null
     */
    public function deviceSameApplyCount(): ?int {
        $orders = $this->getOrderBySameDevice();

        $count = $orders->count();

        return $count;
    }

    /**
     * 获取 相同设备号对应的订单
     * @return Order[]|Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    protected function getOrderBySameDevice() {
        $imei = $this->getImei();

        $data = Cache::remember($this->getCacheKey('order_by_same_device', $imei), 5, function () use ($imei) {
            $orders = Order::query()->whereHas('orderDetails', function (Builder $query) use ($imei) {
                $query->where('key', OrderDetail::KEY_IMEI)
                    ->where('value', $imei);
            })
                ->where('created_at', '<=', $this->order->created_at)
                ->get();

            return $orders;
        });

        return $data;
    }

    protected function getImei() {
        $imei = Cache::remember($this->getCacheKey('imei', $this->user->id), 5, function () {
            $imei = OrderDetail::model()->getImei($this->order);

            if (!$imei) {
                $hardware = $this->getUserPhoneHardware();
                if ($hardware) {
                    $imei = $hardware->imei;
                }
            }

            return $imei;
        });

        return $imei;
    }

    protected function getUserPhoneHardware() {
        return Cache::remember($this->getCacheKey('userPhoneHardware', $this->user->id), 5, function () {
            return UserPhoneHardware::query()->where('user_id', $this->user->id)->latest('id')->first();
        });
    }

    /**
     * 获取小于等于[day]内 同一设备订单申请数
     * @param $day
     * @return int|null
     */
    public function deviceSameTimeRangeApplyCount($day): ?int {
        $orders = $this->getOrderBySameDevice();

        $deadline = date('Y-m-d', strtotime("-{$day}days", strtotime($this->order->created_at)));

        $count = $orders->where('created_at', '>', $deadline)
            ->count();

        return $count;
    }

    /**
     * 获取 同一设备正在申请(处于审批阶段)的笔数
     * @return int|null
     */
    public function deviceSameInApproveCount(): ?int {
        $orders = $this->getOrderBySameDevice();

        $count = $orders->whereIn('status', Order::APPROVAL_PENDING_STATUS)
            ->count();

        return $count;
    }

    /**
     * 获取 同一设备逾期次数
     * @param $day
     * @return int|null
     */
    public function deviceSameOverdueCount($day): ?int {
        $orders = $this->getOrderBySameDevice();

        $count = $orders->whereIn('status', [Order::STATUS_OVERDUE, Order::STATUS_OVERDUE_FINISH])
            ->filter(function ($item) use ($day) {
                if (!isset($item->lastRepaymentPlan) || !$item->lastRepaymentPlan->exists) {
                    return false;
                }
                return $item->lastRepaymentPlan->overdue_days >= $day;
            })
            ->count();

        return $count;
    }

    /**
     * 同一设备匹配 Pan card 个数
     * @return int|null
     */
    public function deviceSameMatchingPancard(): ?int {
        $users = $this->getUserBySameDevice();

        $count = $users->pluck('userInfo')->unique('pan_card_no')->count();

        return $count;
    }

    /**
     * 根据相同设备号获取用户
     * @return User[]|Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    protected function getUserBySameDevice() {
        $imei = $this->getImei();

        $user = Cache::remember($this->getCacheKey('user_by_same_device', $imei), 5, function () use ($imei) {
            $userIds = UserPhoneHardware::query()->distinct()
                ->where('imei', $imei)
                ->pluck('user_id');

            $user = User::query()->with('userInfo')->whereIn('id', $userIds)
                ->where('created_at', '<', $this->order->created_at)
                ->get();

            return $user;
        });

        return $user;
    }

    /**
     * 同一设备匹配 Addhaar Card 个数
     * @return int|null
     */
    public function deviceSameMatchingAddhaar(): ?int {
        $users = $this->getUserBySameDevice();

        $count = $users->pluck('userInfo')->unique('aadhaar_card_no')->count();

        return $count;
    }

    /**
     * 获取 同一设备匹配手机号个数
     * @return int|null
     */
    public function deviceSameMatchingTelephone(): ?int {
        $users = $this->getUserBySameDevice();

        $count = $users->unique('telephone')->count();

        return $count;
    }

    /**
     * 获取 同一设备指定时间段[2.00-3.00](包含边界值)内，申请次数
     * @param $timeRange
     * @return int|null
     */
    public function deviceSameTimePeriodApplyCount($timeRange): ?int {
        $orders = $this->getOrderBySameDevice();

        $count = $orders->each(function ($item) {
            $item->created_time = date('H:i:s', strtotime($item->created_at));
        })
            ->where('created_time', '>=', reset($timeRange))
            ->where('created_time', '<=', end($timeRange))
            ->count();

        return $count;
    }

    /**
     * 获取设备类型
     * @return string|null
     */
    public function getDeviceType(): ?string {
        return 'android';
    }

    /**
     * 获取 同一银行卡匹配 Pan card 个数
     * 由于订单关联银行卡写入在 机审 之后的签约处进行，所以此时订单还没有写入银行卡信息。
     * 老用户暂取用户银行卡
     * @return int|null
     */
    public function authSameBankCardMatchingPanCard(): ?int {
        $matchingUsers = $this->getSameBankCardMatchingUsers();

        $idCardNoCount = $matchingUsers->pluck('userInfo')->unique('pan_card_no')->count();

        return $idCardNoCount;
    }

    /**
     * 获取同一银行卡匹配的用户
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null
     */
    protected function getSameBankCardMatchingUsers() {
        $bankCardNo = optional($this->getUserBankCard())->account_no;

        if (!$bankCardNo) {
            return collect();
        }

        $users = Cache::remember($this->getCacheKey('bankcard_matching_users', $bankCardNo), 5, function () use ($bankCardNo) {
            // 获取绑定成功过的银行卡记录
            $matchingBankCard = BankCard::query()->where('no', $bankCardNo)
                ->whereIn('status', BankCard::STATUS_ONCE_ACTIVE)
                ->get();

            $users = User::query()->with('userInfo')
                ->whereIn('id', $matchingBankCard->pluck('user_id'))
                ->where('created_at', '<=', $this->order->created_at)
                ->get();

            return $users;
        });


        return $users;
    }

    public function getUserBankCard() {
        return $this->user->bankCard;
    }

    /**
     * 获取 同一银行卡匹配 Addhaar card 个数
     * 由于订单关联银行卡写入在 机审 之后的签约处进行，所以此时订单还没有写入银行卡信息。
     * 老用户暂取用户银行卡
     * @return int|null
     */
    public function authSameBankCardMatchingAddhaar(): ?int {
        $matchingUsers = $this->getSameBankCardMatchingUsers();

        $idCardNoCount = $matchingUsers->pluck('userInfo')->unique('aadhaar_card_no')->count();

        return $idCardNoCount;
    }

    /**
     * 获取 同一银行卡匹配手机号个数
     * 由于订单关联银行卡写入在 机审 之后的签约处进行，所以此时订单还没有写入银行卡信息。
     * 老用户暂取用户银行卡
     * @return int|null
     */
    public function authSameBankCardMatchingTelephone(): ?int {
        $matchingUsers = $this->getSameBankCardMatchingUsers();

        $telephoneCount = $matchingUsers->unique('telephone')->count();

        return $telephoneCount;
    }

    /**
     * 获取 同一 身份标识(pan card || addhaar card)匹配手机号个数
     * @return int|null
     */
    public function authSameIdentityMatchingTelephone(): ?int {
        $matchingUsers = $this->getSameIdentityMatchingUsers();

        $telephoneCount = $matchingUsers->unique('telephone')->count();

        return $telephoneCount;
    }

    /**
     * 获取同一身份标识(pan card || addhaar card)匹配的用户
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null
     */
    protected function getSameIdentityMatchingUsers() {
        $panCard = $this->user->userInfo->pan_card_no;
        $addhaarCard = $this->user->userInfo->aadhaar_card_no;

        /** @var Collection $usersByPanCard */
        $usersByPanCard = Cache::remember($this->getCacheKey('pandcard_matching_users', $panCard), 5, function () use ($panCard) {
            return User::query()->whereHas('userInfo', function (Builder $query) use ($panCard) {
                $query->where('pan_card_no', $panCard)
                    ->where('pan_card_no', '!=', '');
            })->get();
        });

        /** @var Collection $usersByAddhaarCard */
        $usersByAddhaarCard = Cache::remember($this->getCacheKey('addhaar_matching_users', $addhaarCard), 5, function () use ($addhaarCard) {
            return User::query()->whereHas('userInfo', function (Builder $query) use ($addhaarCard) {
                $query->where('aadhaar_card_no', $addhaarCard)
                    ->where('aadhaar_card_no', '!=', '');
            })->get();
        });

        $users = $usersByPanCard->merge($usersByAddhaarCard)
            ->where('created_at', '<', $this->order->created_at);

        return $users;
    }

    /**
     * 获取 同一 身份标识(pan card || addhaar card)匹配设备号个数
     * @return int|null
     */
    public function authSameIdentityMatchingDevice(): ?int {
        $matchingUsers = $this->getSameIdentityMatchingUsers();

        if ($matchingUsers->count() <= 1) {
            return null;
        }

        $count = UserPhoneHardware::query()->distinct()
            ->whereIn('user_id', $matchingUsers->pluck('id'))
            ->where('created_at', '<=', $this->order->created_at)
            ->count('imei');

        return $count;
    }

    /**
     * 关联账户是否有小于[days]内被审批拒绝的订单
     * @param $day
     * @return bool
     */
    public function authRelateAccountHasRejectedOrders($day): bool {
        $orders = $this->getAssociatedOrder();

        $deadline = date('Y-m-d', strtotime("-{$day} days", strtotime($this->order->created_at)));

        $count = $orders->whereIn('status', Order::APPROVAL_REJECT_STATUS)
            ->where('created_at', '>', $deadline)
            ->count();

        return $count;
    }

    /**
     * 获取关联账户的 订单
     * @return Order[]|Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    protected function getAssociatedOrder() {
        $data = Cache::remember($this->getCacheKey('associated_order', $this->user->id), 15, function () {
            $userIds = $this->getAssociatedRecord()
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $orders = Order::query()
                ->whereIn('user_id', $userIds)
                ->where('created_at', '<', $this->order->created_at)
                ->get();

            return $orders;
        });

        return $data;
    }

    protected function getAssociatedRecord(array $type = null, bool $withoutMerchantScope = false) {
        /** @var Builder[]|Collection $associatedRecord */
        $associatedRecord = UserAssociatedInfo::getAssociatedByUserId($this->user->id, $type, $withoutMerchantScope);

        return $associatedRecord;
    }

    /**
     * 关联账户是否有进行中(未完结&审批中)的订单
     * @return bool|null
     */
    public function authRelateAccountHasUnderwayOrder(): bool {
        $orders = $this->getAssociatedOrder();

        $count = $orders->whereIn('status', Order::APPROVAL_PENDING_STATUS)
            ->count();

        return $count;
    }

    /**
     * 获取人脸比对分数
     * @return float
     */
    public function getUserFaceMatchScore(): float {
        $score = UserThirdDataServer::server()->getFaceMatchScore($this->user->id, true);

        if (!is_numeric($score)) {
            return (float) 0;
        }

        return $score;
    }

    /**
     * 获取 通讯录内正在申请(处于审批阶段)贷款的人数
     * @return int|null
     */
    public function contactsInApproveCount(): ?int {
        $users = $this->getUserByContactsTelephone();

        $orders = $users->pluck('order');

        $count = $orders->whereIn('status', Order::APPROVAL_PENDING_STATUS)->count();

        return $count;
    }

    /**
     * 获取通讯录对应的用户
     * @param bool $acrossMerchant 是否跨商户获取用户&对应的order
     * @return User[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    protected function getUserByContactsTelephone($acrossMerchant = false) {
        $userId = $this->user->id;
        $telephones = $this->getContactsTelephone('contact_telephone');

        $data = Cache::remember($this->getCacheKey('user_by_contacts_telephone:' . $acrossMerchant, $userId), 5, function () use ($userId, $telephones, $acrossMerchant) {

            return MerchantHelper::callbackOnce(function () use ($telephones, $acrossMerchant) {
                if ($acrossMerchant) {
                    MerchantHelper::clearMerchantId();
                }

                $query = User::query()->with('order')->whereIn('telephone', $telephones)
                    ->where('created_at', '<=', $this->order->created_at);

                return $query->get();
            });
        });

        return $data;
    }

    /**
     * 获取用户通讯录数据
     * @param $field
     * @return mixed
     */
    public function getContactsTelephone($field = null) {
        $userId = $this->user->id;

        $data = Cache::remember($this->getCacheKey('contacts_telephone', $userId), 5, function () use ($userId) {
            return UserContactsTelephone::query()->where('user_id', $userId)
                ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('+30 min', strtotime($this->order->created_at))))
                ->get()->toArray();
        });

        if (isset($field)) {
            return array_column($data, $field);
        }

        return $data;
    }

    /**
     * 获取 通讯录内当前处于逾期状态的人数
     * @param $day
     * @return int|null
     */
    public function contactsInOverdueCount($day): ?int {
        $users = $this->getUserByContactsTelephone();

        $orders = $users->pluck('order');

        $count = $orders->where('status', Order::STATUS_OVERDUE)
            ->filter(function ($item) use ($day) {
                if (!isset($item->lastRepaymentPlan) || !$item->lastRepaymentPlan->exists) {
                    return false;
                }
                return $item->lastRepaymentPlan->overdue_days >= $day;
            })
            ->count();

        return $count;
    }

    /**
     * 获取通讯录人数
     * @return int|null
     */
    public function contactsCount(): ?int {
        $count = count($this->getContactsTelephone());

        return $count;
    }

    /**
     * 获取通讯录手机号对应的用户并执行callback
     * @param callable|null $function
     * @param bool $acrossMerchant
     * @return User[]|Builder[]|Collection|mixed
     */
    public function userByContactsTelephone(callable $function = null, $acrossMerchant = false) {
        $users = $this->getUserByContactsTelephone($acrossMerchant);

        if (isset($function)) {
            return $function($users);
        }
        return $users;
    }

    /**
     * 获取历史被拒次数
     * @return int|null
     */
    public function behaviorRejectCount(): ?int {
        $count = $this->getHisOrders()
            ->whereIn('status', Order::APPROVAL_REJECT_STATUS)
            ->count();

        return $count;
    }

    /**
     * 获取历史逾期次数
     * @param $day
     * @return int|null
     */
    public function behaviorOverdueCount($day): ?int {
        $count = $this->getHisOrders()
            ->whereIn('status', [Order::STATUS_OVERDUE, Order::STATUS_OVERDUE_FINISH])
            ->filter(function ($item) use ($day) {
                if (!isset($item->lastRepaymentPlan) || !$item->lastRepaymentPlan->exists) {
                    return false;
                }
                return $item->lastRepaymentPlan->overdue_days >= $day;
            })
            ->count();

        return $count;
    }

    /**
     * 获取历史最大逾期天数
     * @return int|null
     */
    public function behaviorMaxOverdueDays(): ?int {
        $maxDay = $this->getHisOrders()->pluck('lastRepaymentPlan')->max('overdue_days');

        return $maxDay;
    }

    /**
     * 获取 上一笔订单逾期天数
     * @return int|null
     */
    public function behaviorLatelyOverdueDays(): ?int {
        $order = $this->getHisOrders()->sortByDesc('id')->first();

        if (!$order) {
            return null;
        }

        return optional($order->lastRepaymentPlan)->overdue_days;
    }

    /**
     * 获取 上一笔订单催收电话次数
     * (只计已逾期订单的本人和紧急联系人的催记)
     * @return int|null
     */
    public function behaviorLatelyCollectionTelephoneCount(): ?int {
        /** @var Order $order */
        $order = $this->getHisOrders()->sortByDesc('id')->first();

        if (!$order) {
            return null;
        }

        $telArr = array_merge([$this->user->telephone,], $this->getEmergencyContactsTelephone());

        $count = $order->collectionRecords->where('overdue_days', '>', 0)
            ->whereIn('contact', $telArr)
            ->count();

        return $count;
    }

    public function getEmergencyContactsTelephone($onlyTel = true) {
        $userContacts = $this->user->userContacts;

        if (!$onlyTel) {
            return $userContacts;
        }

        if (!$userContacts) {
            return [];
        }

        return $userContacts->pluck('contact_telephone')->toArray();
    }

    /**
     * 获取手机内存
     * ·     * @param string $type
     * @return float|null
     * @throws \Exception
     */
    public function getUserPhoneMemory($type = 'total_rom') {
        $userPhoneHardware = $this->getUserPhoneHardware();

        if (!$userPhoneHardware) {
            return null;
        }

        if (!in_array($type, ['total_rom', 'used_rom', 'free_rom'])) {
            throw new \Exception('获取手机内存传参错误');
        }

        $memory = (float) current(explode(' ', trim($userPhoneHardware->$type)));

        if (!$memory) {
            $this->warringNotice("手机内存解析错误：{$userPhoneHardware->$type},result:{$memory}");
            return null;
        }

        return (float) $memory;
    }

    protected function warringNotice($content, $title = '机审规则执行Warring', $lockKey = null) {
        if (isset($lockKey) && isset($this->warringNoticeLock[$lockKey])) {
            return true;
        }

        $res = DingHelper::notice($content, $title, DingHelper::AT_SOLIANG);

        isset($lockKey) && $this->warringNoticeLock[$lockKey] = true;

        return $res;
    }

    /**
     * 获取用户申请订单的IMEI数量(去重)
     * @return int|null
     */
    public function deviceApplyUniqueCount(): ?int {
        $userId = $this->user->id;

        $data = Cache::remember($this->getCacheKey('unique_device_apply_cnt', $userId), 5, function () use ($userId) {
            $orderIds = (array) Order::query()->where('user_id', $userId)->value('id');
            return (int) count(array_unique(OrderDetail::model()->getAllImei($orderIds)));
        });
        return $data;
    }

    /**
     * 获取用户sim手机号
     * @return string|null
     */
    public function getUserPhoneSimPhone() {
        $userPhoneHardware = $this->getUserPhoneHardware();

        if (!$userPhoneHardware) {
            return null;
        }

        return StringHelper::formatTelephone($userPhoneHardware->native_phone);
    }

    /**
     * 获取用户完成基础信息认证时间
     * @return mixed
     */
    public function getUserStartVerifyTime() {
        $userBaseInfoAuth = (new UserAuth())->getAuthDetail($this->user->app_id, $this->user->id, UserAuth::TYPE_BASE_INFO);

        return optional($userBaseInfoAuth)->created_at;
    }

    public function getPancardNameComparisonResult() {
        $res = UserThirdDataServer::server()->getPancardNameCheckRes($this->user->id);

        return $res;
    }

    public function getPancardOcrNameVerfiy() {
        $res = UserThirdDataServer::server()->getPancardOcrVerfiyNameCheck($this->user->id);

        return $res;
    }

    /**
     * 获取app列表中贷款app数
     * @return int
     */
    public function getHasLoanAppCount() {
        $appList = $this->getLastVersionApplication();

        $hitCount = RiskAppHitServer::server()->hitLoanAppCount(array_column($appList, 'app_name'));

        return count($hitCount);
    }

    /*     * ************************************** 数据获取 *************************************************************** */

    /**
     * 获取用户最后一个批次的App应用数据
     * @return array
     */
    public function getLastVersionApplication() {
        $data = Cache::remember($this->getCacheKey('user_application', $this->user->id), 5, function () {
            return (new UserApplication())->getLastVersionData($this->user->id);
        });

        return $data;
    }

    /**
     * [day]天内安装的app列表中贷款app数
     * @param $day
     * @return int
     */
    public function getRecentInstallLoanAppCount($day) {
        $appList = $this->getLastVersionApplication();

        $millisecondTimestamp = strtotime(date('Y-m-d', strtotime("- {$day}day", strtotime($this->order->created_at)))) * 1000;

        $filterList = array_where($appList, function ($item) use ($millisecondTimestamp) {
            return isset($item['installed_time']) ? ($item['installed_time'] > $millisecondTimestamp) : false;
        });

        $hitCount = RiskAppHitServer::server()->hitLoanAppCount(array_column($filterList, 'app_name'));

        return count($hitCount);
    }

    /**
     * 获取历史结清订单
     * @return Order[]|Collection
     */
    public function getHisFinishOrder() {
        $orders = $this->getHisOrders();
        return $orders->whereIn('status', Order::FINISH_STATUS);
    }

    public function getRelatePutawayOrderCount($day) {
        $orders = $this->getAssociatedOrderWithoutMerchantScope(
            array_diff(UserAssociatedInfo::ALL_TYPE, [UserAssociatedInfo::TYPE_CONTACT]),
            null,
            ['app']
        );

        $count = $orders->where('app_id', '!=', $this->order->app_id)
            ->filter(function ($item) use ($day) {
                $putawayTime = optional($item->app)->putaway_time;
                $createdTime = $item->created_at;

                if (!$putawayTime) {
                    return false;
                }
                $diffTime = (strtotime($createdTime) - strtotime($putawayTime)) / 86400;
                if ($diffTime > $day) {
                    return false;
                }
                return true;
            })
            ->count();

        return $count;
    }

    /**
     * 获取关联账户的 订单，不区分商户
     * @param array|null $type
     * @param array|null $params
     * @param array $with
     * @return Order[]|Builder[]|Collection
     */
    public function getAssociatedOrderWithoutMerchantScope(array $type = null, array $params = null, array $with = []) {
        return MerchantHelper::callbackOnce(function () use ($type, $params, $with) {
            MerchantHelper::clearMerchantId();

            $userIds = $this->getAssociatedRecord($type, true)
                ->pluck('user_id')
                ->unique()
                ->toArray();

            $orderQuery = Order::query()->whereIn('user_id', $userIds)
                ->with($with)
                ->where('id', '<', $this->order->id);

            if ($createdAt = array_get($params, 'created_at')) {
                is_array($createdAt) && $orderQuery->whereBetween('created_at', $createdAt);
            }

            if ($paidTime = array_get($params, 'paid_time')) {
                is_array($paidTime) && $orderQuery->whereBetween('paid_time', $paidTime);
            }

            return $orderQuery->get();
        });
    }

    /**
     * 获取订单创建日申请的订单数,不包含当前订单
     * @return int
     */
    public function getDailyCreateOrderCount() {
        $date = date('Y-m-d', strtotime($this->order->created_at));

        $query = OrderCheckServer::server()->getDailyCreateOrderCount(null, $date, true);

        $count = $query->where('id', '!=', $this->order->id)
            ->where('created_at', '<', $this->order->created_at)
            ->count();

        return $count;
    }

    /**
     * 获取银行卡在其他商户的放款订单数
     * @param $day
     * @return int
     */
    public function getBankCardMatchingMerchantPaidOrder($day) {
        $startDate = Carbon::parse($this->order->created_at)->subDays($day)->toDateString();
        $endTime = Carbon::parse($this->order->created_at)->addHour()->toDateTimeString();
        $paidTime = [$startDate, $endTime];

        $orders = $this->getAssociatedOrderWithoutMerchantScope([UserAssociatedInfo::TYPE_BANK_CARD_NO], ['paid_time' => $paidTime]);

        $count = $orders->whereIn('status', Order::CONTRACT_STATUS)
            ->where('app_id', '!=', $this->order->app_id)
            ->count();

        return $count;
    }

    /**
     * @return bool
     */
    public function applyIpNotInIndia(): bool {
        $ip = (new OrderDetail)->getIp($this->order);

        if (!$ip) {
            return false;
        }

        try {
            $res = Ip::find($ip);
            if (!is_array($res) || !isset($res[0])) {
                throw new \Exception('IP不正确');
            }
        } catch (\Exception $e) {
            DingHelper::notice("IP:{$ip} \n orderId:{$this->order->id}", '机审IP地址不正确！', DingHelper::AT_SOLIANG);
            return true;
        }

        if ($res[0] == '印度') {
            return false;
        }

        return true;
    }

    /**
     * 判断申请GPS是否不在印度
     * @return bool
     */
    public function applyGpsNotInIndia(): bool {
        $location = (new OrderDetail())->getLocation($this->order);

        if (!$location) {
            return false;
        }

        return !str_contains($location, 'India');
    }

    /**
     * 判断申请设备号是否变更
     * @return bool
     */
    public function deviceUniqueNumberIsChange() {
        if (!$orderImei = (new OrderDetail)->getImei($this->order)) {
            return false;
        }

        $orders = $this->getHisOrders();

        $lastImei = $orders->pluck('orderDetails')
            ->flatten()
            ->where('key', OrderDetail::KEY_IMEI)
            ->where('value', '!=', '')
            ->sortByDesc('id')
            ->first();

        if (!$lastImei) {
            return false;
        }

        return $orderImei != $lastImei->value;
    }

    /**
     * 获取紧急联系人手机号首位列表
     */
    public function getEmergencyContactsTelephoneInitial() {
        $contactsTelephone = $this->getEmergencyContactsTelephone();
        $data = [];
        foreach ($contactsTelephone as $item) {
            $tel = StringHelper::formatTelephone($item);
//            $tel = substr($item, -10);
            if ($tel) {
                $data[] = $tel;
            }
        }
        return $data;
    }

    public function getEmergencyContacts() {
        $userContacts = $this->user->userContacts->toArray();
        $userContacts = array_map(function ($item) {
            $item['contact_telephone'] = StringHelper::formatTelephone($item['contact_telephone']);
            return $item;
        }, $userContacts);
        return $userContacts;
    }

    /**
     * 获取HM报告中最后一次还款时间
     * @return mixed|null
     */
    public function getHmCreditReportLastPaymentTime() {
        $lastPaymentDateArr = $this->getHmCreditReportResponseLoanDetail('LAST-PAYMENT-DATE');

        $lastPaymentDateArr = array_filter($lastPaymentDateArr);
        if (!$lastPaymentDateArr) {
            return null;
        }

        $timeArr = [];
        foreach ($lastPaymentDateArr as $item) {
            $time = strtotime($item);
            $time && $timeArr[] = $time;
        }
        return max($timeArr);
    }

    /**
     * 获取HM征信报告 RESPONSES.RESPONSE 的 LOAN-DETAILS.ACCOUNT-STATUS 数组
     * @param $field
     * @return array|bool
     */
    public function getHmCreditReportResponseLoanDetail($field = null) {
        $creditReport = $this->getHmCreditReport();
        $responses = array_get($creditReport, 'RESPONSES.RESPONSE');

        // 值为空 || 不是数组
        if (!$creditReport || is_null($responses) || !is_array($responses)) {
            return false;
        }

        if (isset($responses['LOAN-DETAILS'])) {
            $loanDetails = array_values($responses);
        } else {
            $loanDetails = array_pluck($responses, 'LOAN-DETAILS');
        }

        if (is_null($field)) {
            return $loanDetails;
        }

        $resArr = [];
        foreach ($loanDetails as $item) {
            $item && $resArr[] = array_get($item, $field);
        }

        return $resArr;
    }

    /**
     * 获取HM征信报告
     * @param bool $parse
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getHmCreditReport() {
        $report = Cache::remember($this->getCacheKey('hm_credit_report', $this->user->id), 5, function () {
            $report = ThirdReport::getHmReportBody($this->user->id);

            if (is_null($report)) {
                DingHelper::notice(['order_id' => $this->order->id], 'HM征信报告获取为空', DingHelper::AT_SOLIANG);
                return null;
            }

            return $report;
        });

        return $report;
    }

    /**
     * 获取用户所有App应用数据
     * 截止时间到订单创建后..
     * @return mixed
     */
    public function getAllApplication() {
        $data = Cache::remember($this->getCacheKey('user_all_application', $this->user->id), 5, function () {
            $deadline = date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($this->order->created_at)));

            return (new UserApplication())->getAllData($this->order->app_id, $this->user->id, $deadline);
        });

        return $data;
    }

    /*     * ***************nio.wang自建规则********************** */

    /**
     * 判断申请用户是否在黑名单内
     * @return bool
     */
    public function applyInBlacklist() {
        $userInfo = $this->getUserInfo();
        //初始化
        $data = [
            'adaahaarNumber' => '',
            'bankCard' => '',
            'email' => '',
            'nameBirth' => '',
            'panCard' => '',
            'telephone' => '',
            'deviceId' => '',
            'bank_card_no_cut3' => '',
            'bank_card_no_cut4' => '',
            'bank_card_no_cut5' => '',
        ];
        $data['adaahaarNumber'] = $userInfo->aadhaar_card_no;
        $data['bankCard'] = $bankCardNo = optional($this->getUserBankCard())->account_no;
        $data['email'] = $userInfo->email;
        $data['nameBirth'] = $this->user->fullname . "|" . date("Y-m-d", strtotime(str_replace('/', '-', $userInfo->birthday)));
        $data['panCard'] = $userInfo->pan_card_no;
        $data['telephone'] = $this->user->telephone;
        $data['deviceId'] = $this->getImei();
        $data['bank_card_no_cut3'] = substr($bankCardNo, 0, strlen($bankCardNo) - 3);
        $data['bank_card_no_cut4'] = substr($bankCardNo, 0, strlen($bankCardNo) - 4);
        $data['bank_card_no_cut5'] = substr($bankCardNo, 0, strlen($bankCardNo) - 5);
        $userBlacklist = new UserBlacklist();
        $hitList = [];
        foreach ($data as $key => $value) {
            $res = $userBlacklist->inBlackMenu($value, $key);
            if ($res) {
                $hitList[] = ["type" => $key, "value" => $value];
            }
        }
        if (count($hitList)) {
            return $hitList;
        } else {
            return false;
        }
    }

    /**
     * 获取应用列表计数
     * @return type
     */
    public function appListCount() {
        $res = Cache::remember($this->getCacheKey('appListCount', $this->user->id), 5, function () {
            return $this->applistData = RiskUserAppServer::server()->getAppListIndex($this->order);
        });
        return $res;
    }

    /**
     * What's 用户验证
     */
    public function thirdDataWhatsapp() {
        $res = Cache::remember($this->getCacheKey('thirdDataWhatsapp', $this->user->id), 5, function () {
            return WhatsappServer::server()->check($this->user->telephone);
        });
        return $res;
    }

    /* kudos 风控 */

    public function kudosRiskPan() {
        $res = Cache::remember($this->getCacheKey('kudosRiskPan', $this->user->id), 5, function () {
            return RiskThirdDataServer::server()->getKudosLoanByOrder($this->order);
        });
        return $res;
    }

    /* kreditone积分 */

    public function kreditoneScore() {
        $res = Cache::remember($this->getCacheKey('kreditoneScore', $this->user->id), 5, function () {
            return RiskThirdDataServer::server()->getKreditOneScore($this->order);
        });
        return $res;
    }

    public function userSaasScore() {
        $res = Cache::remember($this->getCacheKey('userSaasScore', $this->user->id), 5, function () {
            return RiskUserServer::server()->getScore($this->order);
        });
        return $res;
    }

    /**
     * 多維度確認未完成訂單
     */
    public function checkOpenOrders() {
        $panCard = $this->user->userInfo->pan_card_no;
        $aadhaarCard = $this->user->userInfo->aadhaar_card_no;
        $contactsTelephone = $this->getEmergencyContactsTelephone();
        $bankCardNo = optional($this->getUserBankCard())->account_no;
        $telephone = $this->user->telephone;
        $server = RiskOrderServer::server();

        if (false !== $res = $server->checkAadhaarCardOrder($aadhaarCard)) {
            return "A卡{$aadhaarCard}存在未完成订单:" . $res;
        }
        if (false !== $res = $server->checkPanCardOrder($panCard)) {
            return "Pan卡{$panCard}存在未完成订单:" . $res;
        }
        if (false !== $res = $server->checkBankcardOrder($bankCardNo)) {
            return "银行卡{$bankCardNo}存在未完成订单:" . $res;
        }

        if (false !== $res = $server->checkTelephoneOrder($telephone)) {
            return "手机号{$telephone}存在未完成订单:" . $res;
        }
        foreach ($contactsTelephone as $contact) {
            if (false !== $res = $server->checkTelephoneOrder($contact)) {
                return "紧急联系人手机号{$telephone}存在未完成订单:" . $res;
            }
        }
        return false;
    }

    public function getOrderPrincipal() {
        return $this->order->principal;
    }

    /**
     * What's 用户验证
     */
    public function thirdDataAirudder() {
        $res = Cache::remember($this->getCacheKey('thirdDataAirudder', $this->user->id), 5, function () {
            return AirudderServer::server()->checkUserAndContact($this->user);
        });
        return $res;
    }
}
