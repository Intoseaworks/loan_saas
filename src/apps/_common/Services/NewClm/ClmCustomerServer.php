<?php

namespace Common\Services\NewClm;

use Common\Models\BankCard\BankCardPeso;
use Common\Models\NewClm\ClmAmount;
use Common\Models\NewClm\ClmCustomer;
use Common\Models\NewClm\ClmCustomerExt;
use Common\Models\NewClm\ClmInitLevel;
use Common\Models\NewClm\ClmWhiteList;
use Common\Models\User\User;
use Common\Services\BaseService;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClmCustomerServer extends BaseService {

    /**
     * 根据用户信息匹配客户
     * 1、证件类型+证件号
     * 2、手机号+姓名
     * 3、姓名+出生日期
     *
     * todo:可能不能唯一匹配到用户，会更新到其他客户的等级信息，看需求是否需要优化
     *
     * @param User $user
     *
     * @return ClmCustomer|null
     * @throws \Exception
     */
    public function getCustomer(User $user): ?ClmCustomer
    {
        MerchantHelper::setMerchantId($user->merchant_id);
        $birthday = $user->userInfo ? $user->userInfo->birthday : null;
        $birthday = $birthday ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $user->userInfo->birthday))) : null;

//        $cardType = $user->card_type;
        $cardType = str_replace(" ","",$user->card_type);
//        $idCardNo = $user->id_card_no;
        $idCardNo = str_replace(" ","",$user->id_card_no);

        $telephone = $user->telephone;
        $fullname = str_replace(" ","",$user->fullname);

        $query = ClmCustomer::query();

        $isConditionEmpty = true;
        // 1、证件类型+证件号
        if ($cardType && $idCardNo) {
            $query->orWhere(function (Builder $query) use ($cardType, $idCardNo) {
//                $query->where('idcard_type', $cardType)
                $query->where("idcard_type_nospace", $cardType)
                    ->where("idcard_nospace", $idCardNo);
//                        ->where('idcard', $idCardNo);
            });
            $isConditionEmpty = false;
        }

        // 2、手机号+姓名
        if ($telephone && $fullname) {
            $query->orWhere(function (Builder $query) use ($telephone, $fullname) {
                $query->where('phone', $telephone)
                    ->where(\DB::raw("REPLACE(`name`,' ','')"), $fullname);
            });
            $isConditionEmpty = false;
        }

        // 3、姓名+出生日期年份--新需求202106
        if ($fullname && $birthday) {
            $query->orWhere(function (Builder $query) use ($fullname, $birthday) {
                $query->where("name_nospace", $fullname)
//                        ->where('birthday', $birthday);
                    ->where(\DB::raw("YEAR(`birthday`)"), date('Y',strtotime($birthday)));
            });
            $isConditionEmpty = false;
        }
        // 4、 电话+出生日期的年-- 20210628
        if ($telephone && $birthday) {
            $query->orWhere(function (Builder $query) use ($telephone, $birthday) {
                $query->where('phone', $telephone)
                    ->where(\DB::raw("YEAR(`birthday`)"), date('Y', strtotime($birthday)));
            });
            $isConditionEmpty = false;
        }

        if ($isConditionEmpty) {
            throw new \Exception('用户不满足匹配条件');
        }

        return $query->first();
    }

    /**
     * 获取或初始化客户
     *
     * @param User $user
     *
     * @return ClmCustomer
     * @throws \Exception
     */
    public function getOrInitCustomer(User $user): ClmCustomer {
        $customer = MerchantHelper::callbackOnce(function () use ($user) {
            MerchantHelper::setMerchantId($user->merchant_id);

            $customer = $this->getCustomer($user);

            if ($customer && $customer->ext) {
                return $customer;
            }

            if ($customer) {
                // 客户存在，但本商户未有子表
                $initLevel = $this->getInitLevel($user);
                $this->createCustomerExt($customer, $user->merchant_id, $initLevel);
            } else {
                // 不存在客户
                $customer = $this->createCustomer($user);
            }

            return $customer->load('ext');
        });

        return $customer;
    }

    /**
     * 创建客户
     *
     * @param User $user
     *
     * @return ClmCustomer
     * @throws \Exception
     */
    protected function createCustomer(User $user): ClmCustomer
    {
        $initLevel = $this->getInitLevel($user);
        $initClmAmount = ClmAmount::getAmountByLevel($initLevel);

        $data = [
            // 基本信息
            'idcard_type' => $user->card_type,
            'idcard' => $user->id_card_no,
            'name' => $user->fullname,
            'phone' => $user->telephone,
            'birthday' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $user->userInfo->birthday))),
            'sex' => $user->userInfo->gender,
            // clm信息
            'clm_customer_id' => ClmCustomer::generateCustomerId(),
            'max_level' => $initLevel,
            'total_amount' => $initClmAmount,
            'used_amount' => 0,
            'freeze_amount' => 0,
            "name_nospace" => str_replace(" ", "", $user->fullname),
            "idcard_type_nospace" => str_replace(" ", "", $user->card_type),
            "idcard_nospace" => str_replace(" ", "", $user->id_card_no),
        ];

        # 20210918 tracy 初始化时total_amount写死10000
        //原来是当客户第一次在我们平台生成clm等级时，new_clm_customer.total_amount=10000,现在需要将这个值改为12000，max_level=19,20211014
        $data['total_amount'] = 3300;
        $data['max_level'] = 30;
        DB::beginTransaction();
        try {
            # 处理重复记录
            if($data['idcard_type'] && $data['idcard']){
                ClmCustomer::model()->updateOrCreateModel($data, [
                    "idcard_type" => $data['idcard_type'],
                    "idcard" => $data['idcard'],
                ]);
                $customer = ClmCustomer::model()->where("idcard_type", $data['idcard_type'])->where("idcard", $data['idcard'])->first();
            } else {
                $customer = ClmCustomer::create($data);
            }

            $this->createCustomerExt($customer, $user->merchant_id, $initLevel);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new $e;
        }

        return $customer;
    }

    /**
     * 创建客户子表
     *
     * @param ClmCustomer $customer
     * @param int $merchantId
     * @param int $level
     *
     * @return ClmCustomerExt
     */
    protected function createCustomerExt(ClmCustomer $customer, int $merchantId, int $level): ClmCustomerExt {
        return ClmCustomerExt::firstOrCreate([
            'merchant_id' => $merchantId,
            'clm_customer_id' => $customer->clm_customer_id,
        ], [
            'current_level' => $level,
            'status' => ClmCustomerExt::STATUS_NORMAL,
        ]);
    }

    /**
     * 获取默认等级
     *
     * @param User $user
     *
     * @return int
     */
    protected function getInitLevel(User $user): int {
//        $paymentIsCard = optional($user->bankCard)->payment_type == BankCardPeso::PAYMENT_TYPE_BANK || optional($user->bankCard)->payment_type == BankCardPeso::PAYMENT_TYPE_OTHER;
        $paymentIsCard = optional($user->bankCard)->payment_type == BankCardPeso::PAYMENT_TYPE_BANK;

        // 外部购买的优质名单，这个会在下一期增加这个功能，把名单导入到系统里。当期默认false
//        $inWhiteList = false;
        $inWhiteList = \Common\Services\Crm\WhiteListServer::server()->check($user);

//        $query = ClmInitLevel::query()->where('merchant_id', $user->merchant_id);

//        $level = 0;
//        switch (true) {
//            case $inWhiteList && $paymentIsCard: // 白名单 & 有卡
//                $level = $query->whereWhitelistAndIsCard()->value('clm_level');
//                break;
//            case $inWhiteList && !$paymentIsCard: // 白名单 & 无卡
//                $level = $query->whereWhitelistAndNonCard()->value('clm_level');
//                break;
//            case!$inWhiteList && $paymentIsCard: // 非白名单 & 有卡
//                $level = $query->whereNonWhitelistAndIsCard()->value('clm_level');
//                break;
//            case!$inWhiteList && !$paymentIsCard: // 非白名单 & 无卡
//                $level = $query->whereNonWhitelistAndNonCard()->value('clm_level');
//                break;
//        }

        $level = $this->getLevelNew($user,$paymentIsCard,$inWhiteList);

        $level = ClmAmount::disposeLevel($level ?? 0);

        return $level;
    }

    /**
     * 获取客户等级初审level(新)
     *
     * @param User $user
     * @param bool $merchantId
     * @param bool $level
     * @return int
     */
    private function getLevelNew(User $user, bool $paymentIsCard, bool $inWhiteList): int {
        //新需求,表new_clm_init_level，payment_type需要加多一项，专门针对电子钱包的。把现在的有卡留给银行卡，新增一个电子钱包的。电子钱包类型设置等级后，用户取款方式是电子钱包时，需要初始化为对应额度对应的金额。
        $isEwallet = optional($user->bankCard)->payment_type == BankCardPeso::PAYMENT_TYPE_OTHER;
        //历史老客户
        $filter = [['white_id','=',ClmWhiteList::MERCHANTS1],['telephone','=',$user->telephone],['status','=',ClmWhiteList::STATUS_NORMAL]];
        $isOldCustomer = ClmWhiteList::query()->where($filter)->exists();
        //风控跨品牌白名单
        $filter = [['white_id','=',ClmWhiteList::MERCHANTGLOBAL],['telephone','=',$user->telephone],['status','=',ClmWhiteList::STATUS_NORMAL]];
        $isWholeWhiteList = ClmWhiteList::query()->where($filter)->exists();
        //新增一个表记录这些历史老客户和跨品牌白名单，当客户申请时可以自动匹配new_clm_init_level对应的额度。这个表由风控不定时更新数据。
        $paymentTypes = [];
        if ( $paymentIsCard ){
            $paymentTypes[] = ClmInitLevel::PAYMENT_TYPE_BANKCARD;
        }else{
            $paymentTypes[] = ClmInitLevel::PAYMENT_TYPE_NON_BANKCARD;
        }
        if ( $isEwallet ){
            $paymentTypes[] = ClmInitLevel::PAYMENT_TYPE_EWALLET;
        }
        $userTypes = [];
        if ( $inWhiteList ){
            $userTypes[] = ClmInitLevel::USER_TYPE_WHITELIST;
        }else{
            $userTypes[] = ClmInitLevel::USER_TYPE_NON_WHITELIST;
        }
        if ( $isOldCustomer ){
            $userTypes[] = ClmInitLevel::USER_TYPE_OLD_CUSTOMER_S1;
        }
        if ( $isWholeWhiteList ){
            $userTypes[] = ClmInitLevel::USER_TYPE_GLOBAL_MERCHANT;
        }
        $levelinit = 0;
        $query = ClmInitLevel::query()->where('merchant_id', $user->merchant_id);
        foreach ($paymentTypes as $paymentType){
            foreach ($userTypes as $userType){
                $queryNew = clone $query;
                $level = $queryNew->wherePaymentType($paymentType)->whereUserType($userType)->value('clm_level');
                //取最大level
                if ($level > $levelinit){
                    $levelinit = $level;
                }
            }
        }
        return $levelinit;
    }

    public function getCustomerByParam($params) {
        $birthday = $params['birthday'] ?? '';
        $cardType = $params['card_type'] ?? '';
        $idCardNo = $params['id_card_no'] ?? '';
        $telephone = $params['telephone'] ?? '';
        $fullname = $params['fullname'] ?? '';

        $query = ClmCustomer::query();

        $isConditionEmpty = true;
        // 1、证件类型+证件号
        if ($cardType && $idCardNo) {
            $query->orWhere(function (Builder $query) use ($cardType, $idCardNo) {
                $query->where('idcard_type', $cardType)
                    ->where('idcard', $idCardNo);
            });
            $isConditionEmpty = false;
        }

        // 2、手机号+姓名
        if ($telephone && $fullname) {
            $query->orWhere(function (Builder $query) use ($telephone, $fullname) {
                $query->where('phone', $telephone)
                    ->where('name', $fullname);
            });
            $isConditionEmpty = false;
        }

        // 3、姓名+出生日期
        if ($fullname && $birthday) {
            $query->orWhere(function (Builder $query) use ($fullname, $birthday) {
                $query->where('name', $fullname)
                    ->where('birthday', $birthday);
            });
            $isConditionEmpty = false;
        }

        if ($isConditionEmpty) {
//            throw new \Exception('用户不满足匹配条件');
        }

        return $query->first();
    }

}
