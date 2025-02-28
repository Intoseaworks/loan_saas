<?php

namespace Api\Models\User;

use Common\Utils\MerchantHelper;

/**
 * Admin\Models\User\UserAuthBlack
 *
 * @property int $id
 * @property string $receive
 * @property string $type 类型
 * @property string $remark
 * @property int $status 状态
 * @property int $black_time 黑名单生效时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereBlackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\User\UserAuthBlack whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuthBlack orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id 商户ID
 * @property int $user_id 用户id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuthBlack whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuthBlack whereReceive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuthBlack whereUserId($value)
 */
class UserAuthBlack extends \Common\Models\User\UserAuthBlack
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'type',
                'receive',
                'black_time',
                'remark',
                'status' => self::STATUS_NORMAL,
            ],
            self::SCENARIO_UPDATE => [
                'status',
            ],
        ];
    }

    public function add($param)
    {
        return UserAuthBlack::model(UserAuthBlack::SCENARIO_CREATE)->saveModel($param);
    }
}
