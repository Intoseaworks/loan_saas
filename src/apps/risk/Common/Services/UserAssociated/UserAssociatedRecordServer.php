<?php

namespace Risk\Common\Services\UserAssociated;

use Common\Services\BaseService;
use Common\Utils\Data\ArrayHelper;
use Risk\Common\Models\Business\BankCard\BankCard;
use Risk\Common\Models\Business\User\User;
use Risk\Common\Models\Business\User\UserContact;
use Risk\Common\Models\Business\User\UserInfo;
use Risk\Common\Models\Business\UserData\UserPhoneHardware;
use Risk\Common\Models\UserAssociated\UserAssociatedInfo;

class UserAssociatedRecordServer extends BaseService
{
    protected $userId;
    protected $startTime;

    public function __construct($userId = null, $startTime = null)
    {
        $this->userId = $userId;
        $this->startTime = $startTime;
    }

    public function handle()
    {
        $this->recordUser();

        $this->recordUserInfo();

        $this->recordBankCardInfo();

        $this->recordContactInfo();

        $this->recordPhoneHardware();
    }

    protected function recordUser()
    {
        $query = User::query()->select(['id', 'app_id', 'telephone']);

        if (isset($this->startTime)) {
            $query->where('sync_time', '>=', $this->startTime);
        }
        if (isset($this->userId)) {
            $query->where('id', $this->userId);
        }

        $users = $query->get();

        $telephoneData = [];
        foreach ($users as $user) {
            $data = [
                'app_id' => $user->app_id,
                'user_id' => $user->id,
            ];

            $telephoneData[] = $data + [
                    'value' => $user->telephone,
                ];
        }

        $telephoneData && UserAssociatedInfo::coverAdd($telephoneData, UserAssociatedInfo::TYPE_TELEPHONE);
    }

    /**
     * 记录用户 手机号、身份证 关联信息
     */
    protected function recordUserInfo()
    {
        $query = UserInfo::query();

        if (isset($this->startTime)) {
            $query->where('sync_time', '>=', $this->startTime);
        }
        if (isset($this->userId)) {
            $query->where('user_id', $this->userId);
        }

        $userInfos = $query->get();

        $panCardNoData = [];
        $aadhaarCardNoData = [];
        $passportNoData = [];
        $voterIdData = [];
        foreach ($userInfos as $userInfo) {
            $data = [
                'app_id' => $userInfo->app_id,
                'user_id' => $userInfo->user_id,
            ];

            optional($userInfo)->pan_card_no && $panCardNoData[] = $data + [
                    'value' => $userInfo->pan_card_no,
                ];
            optional($userInfo)->aadhaar_card_no && $aadhaarCardNoData[] = $data + [
                    'value' => $userInfo->aadhaar_card_no,
                ];
            optional($userInfo)->passport_no && $passportNoData[] = $data + [
                    'value' => $userInfo->passport_no,
                ];
            optional($userInfo)->voter_id_card_no && $voterIdData[] = $data + [
                    'value' => $userInfo->voter_id_card_no,
                ];
        }

        $panCardNoData && UserAssociatedInfo::coverAdd($panCardNoData, UserAssociatedInfo::TYPE_PAN_CARD_NO);
        $aadhaarCardNoData && UserAssociatedInfo::coverAdd($aadhaarCardNoData, UserAssociatedInfo::TYPE_AADHAAR_CARD_NO);
        $passportNoData && UserAssociatedInfo::coverAdd($passportNoData, UserAssociatedInfo::TYPE_PASSPORT_NO);
        $voterIdData && UserAssociatedInfo::coverAdd($voterIdData, UserAssociatedInfo::TYPE_VOTER_ID);
    }

    /**
     * 记录 银行卡 关联信息
     */
    protected function recordBankCardInfo()
    {
        $query = BankCard::with('user')
            ->select(['user_id', 'no'])
            ->where('status', BankCard::STATUS_ACTIVE);

        if (isset($this->startTime)) {
            $query->where('sync_time', '>=', $this->startTime);
        }
        if (isset($this->userId)) {
            $query->where('user_id', $this->userId);
        }

        $bankCards = $query->get();

        $bankCardData = [];
        foreach ($bankCards as $bankCard) {
            if (!$bankCard->user || !$bankCard->account_no) {
                continue;
            }

            $bankCardData[] = [
                'app_id' => $bankCard->user->app_id,
                'user_id' => $bankCard->user_id,
                'value' => $bankCard->account_no,
            ];
        }

        $bankCardData && UserAssociatedInfo::coverAdd($bankCardData, UserAssociatedInfo::TYPE_BANK_CARD_NO);
    }

    /**
     * 记录 紧急联系人 关联信息
     */
    protected function recordContactInfo()
    {
        $query = UserContact::query()
            ->select(['app_id', 'user_id', 'contact_telephone'])
            ->where('status', UserContact::STATUS_ACTIVE);

        if (isset($this->startTime)) {
            $query->where('sync_time', '>=', $this->startTime);
        }
        if (isset($this->userId)) {
            $query->where('user_id', $this->userId);
        }

        $userContacts = $query->get();

        $contactData = [];
        foreach ($userContacts as $userContact) {
            $userContact->contact_telephone && $contactData[] = [
                'app_id' => $userContact->app_id,
                'user_id' => $userContact->user_id,
                'value' => $userContact->contact_telephone,
            ];
        }

        $contactData && UserAssociatedInfo::coverAdd($contactData, UserAssociatedInfo::TYPE_CONTACT);
    }

    /**
     * 记录 手机硬件信息(UUID、IMEI) 关联信息
     */
    protected function recordPhoneHardware()
    {
        $query = UserPhoneHardware::query();

        if (isset($this->startTime)) {
            $query->where('sync_time', '>=', $this->startTime);
        }
        if (isset($this->userId)) {
            $query->where('user_id', $this->userId);
        }

        $userPhoneHardware = $query->get();

        $users = User::query()->whereIn('id', $userPhoneHardware->pluck('user_id'))
            ->get()->keyBy('id');

        $imeiData = [];
        $uuidData = [];
        $simData = [];
        foreach ($userPhoneHardware as $item) {
            $user = $users->get($item->user_id);
            if (!isset($user)) {
                continue;
            }

            $item->imei && $imeiData[] = [
                'app_id' => $user->app_id,
                'user_id' => $item->user_id,
                'value' => $item->imei,
            ];

            $extInfo = json_decode($item->ext_info, true);
            if ($extInfo && isset($extInfo['uuid']) && $extInfo['uuid']) {
                $uuidData[] = [
                    'app_id' => $user->app_id,
                    'user_id' => $item->user_id,
                    'value' => $extInfo['uuid'],
                ];
            }

            $item->native_phone && $simData[] = [
                'app_id' => $user->app_id,
                'user_id' => $item->user_id,
                'value' => $item->native_phone,
            ];
        }

        $imeiData && UserAssociatedInfo::coverAdd(ArrayHelper::arrayUnique($imeiData), UserAssociatedInfo::TYPE_IMEI);
        $uuidData && UserAssociatedInfo::coverAdd(ArrayHelper::arrayUnique($uuidData), UserAssociatedInfo::TYPE_UUID);
        $simData && UserAssociatedInfo::coverAdd(ArrayHelper::arrayUnique($simData), UserAssociatedInfo::TYPE_SIM_PHONE_NUM);
    }
}
