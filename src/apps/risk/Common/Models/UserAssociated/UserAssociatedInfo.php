<?php

namespace Risk\Common\Models\UserAssociated;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Risk\Common\Helper\LockRedisHelper;
use Risk\Common\Models\RiskBaseModel;
use Risk\Common\Services\UserAssociated\UserAssociatedRecordServer;

/**
 * Risk\Common\Models\UserAssociated\UserAssociatedInfo
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property string $type 特征值类型
 * @property string $value 特征值
 * @property \Illuminate\Support\Carbon $created_at 添加时间
 * @method static Builder|UserAssociatedInfo newModelQuery()
 * @method static Builder|UserAssociatedInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static Builder|UserAssociatedInfo query()
 * @method static Builder|UserAssociatedInfo whereAppId($value)
 * @method static Builder|UserAssociatedInfo whereCreatedAt($value)
 * @method static Builder|UserAssociatedInfo whereId($value)
 * @method static Builder|UserAssociatedInfo whereType($value)
 * @method static Builder|UserAssociatedInfo whereUserId($value)
 * @method static Builder|UserAssociatedInfo whereValue($value)
 * @mixin \Eloquent
 */
class UserAssociatedInfo extends RiskBaseModel
{
    /** 类型：手机号 */
    const TYPE_TELEPHONE = 'telephone';
    /** 类型：pan card no */
    const TYPE_PAN_CARD_NO = 'pan_card_no';
    /** 类型：aadhaar card no */
    const TYPE_AADHAAR_CARD_NO = 'aadhaar_card_no';
    /** 类型：选民证ID */
    const TYPE_VOTER_ID = 'voter_id';
    /** 类型：护照NO */
    const TYPE_PASSPORT_NO = 'passport_no';
    /** 类型：银行卡号 */
    const TYPE_BANK_CARD_NO = 'bank_card_no';
    /** 类型：紧急联系人 */
    const TYPE_CONTACT = 'contact';
    /** 类型：手机硬件-UUID */
    const TYPE_UUID = 'UUID';
    /** 类型：手机硬件-IMEI */
    const TYPE_IMEI = 'IMEI';
    /** 类型：手机硬件-sim手机号 */
    const TYPE_SIM_PHONE_NUM = 'native_phone';
    const CORE_TYPE = [
        self::TYPE_TELEPHONE,
        self::TYPE_IMEI,
        self::TYPE_AADHAAR_CARD_NO,
        self::TYPE_PAN_CARD_NO,
    ];
    /** @var array 禁用的关联类型 */
    const DISABLE_TYPE = [
        self::TYPE_UUID,
    ];
    const ALL_TYPE = [
        self::TYPE_TELEPHONE,
        self::TYPE_PAN_CARD_NO,
        self::TYPE_AADHAAR_CARD_NO,
        self::TYPE_VOTER_ID,
        self::TYPE_PASSPORT_NO,
        self::TYPE_BANK_CARD_NO,
        self::TYPE_CONTACT,
        self::TYPE_UUID,
        self::TYPE_IMEI,
        self::TYPE_SIM_PHONE_NUM,
    ];
    const UPDATED_AT = null;
    /**
     * @var string
     */
    protected $table = 'user_associated_info';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    protected $guarded = [];
    /**
     * @var array
     */
    protected $hidden = [];

    public static function coverAdd($data, $type)
    {
        $insertDatas = [];
        $createdAt = date('Y-m-d H:i:s');

        foreach ($data as $item) {
            if (!isset($item['value']) || !$item['value'] || !isset($item['app_id'])) {
                continue;
            }

            $insertDatas[$item['app_id']][] = [
                'app_id' => $item['app_id'],
                'user_id' => $item['user_id'],
                'type' => $type,
                'value' => $item['value'],
                'created_at' => $createdAt,
            ];
        }

        foreach ($insertDatas as $appId => $insertData) {
            DB::connection((new static())->getConnectionName())->transaction(function () use ($insertData, $type, $appId) {
                // 避免delete时有不存在的数据，导致发生IX锁，进而导致死锁，此处先查询出存在的记录，再进行删除
                $userId = self::getUserIdByType($appId, array_column($insertData, 'user_id'), $type);
                self::deleteByType($appId, $userId, $type);

                $res = self::insert($insertData);
                return $res;
            });
        }
        return true;
    }

    public static function getUserIdByType($appId, $userIds, $type)
    {
        return self::query()->where(['app_id' => $appId, 'type' => $type])
            ->whereIn('user_id', (array)$userIds)
            ->pluck('user_id')
            ->toArray();
    }

    public static function deleteByType($appId, $userIds, $type)
    {
        return self::query()
            ->where('app_id', $appId)
            ->whereIn('user_id', (array)$userIds)
            ->where('type', $type)
            ->delete();
    }

    /**
     * @param $userId
     * @param array $type
     * @param bool $withoutMerchantScope
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAssociatedByUserId($userId, array $type = null, bool $withoutMerchantScope = false)
    {
        // 更新userId对应用户的关联数据
        if (LockRedisHelper::helper()->updateUserAssociatedRecord($userId)) {
            UserAssociatedRecordServer::server($userId)->handle();
        }
        // 获取userId对应用户的关联数据
        $infoGroups = self::getValueByUserId($userId, $type)->groupBy('type');

        if ($infoGroups->isEmpty()) {
            return collect();
        }

        $query = self::query()->where(function (Builder $query) use ($infoGroups) {
            foreach ($infoGroups as $type => $infoGroup) {
                $values = $infoGroup->pluck('value')->toArray();

                $query->orWhere(function (Builder $query) use ($type, $values) {
                    $query->where('type', $type)
                        ->whereIn('value', $values);
                });
            }
        });

        if ($withoutMerchantScope) {
            $query->withoutGlobalScope(self::$bootScopeMerchant);
        }

        $res = $query->where('user_id', '!=', $userId)->get();

        return $res;
    }

    /**
     * @param $userId
     * @param array $type
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getValueByUserId($userId, array $type = null)
    {
        $where = [
            'user_id' => $userId
        ];

        $query = self::query()->select(['type', 'value'])->where($where);

        if (isset($type) && $type) {
            $query->whereIn('type', $type);
        } else {
            $query->whereNotIn('type', self::DISABLE_TYPE);
        }

        $result = $query->get();

        return $result;
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [
        ];
    }
}
