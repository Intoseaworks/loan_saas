<?php

namespace Api\Models\User;

/**
 * Api\Models\User\UserAuth
 *
 * @property int $id 用户信息扩展自增长ID
 * @property int $user_id 用户id
 * @property string $type 用户数据类型
 * @property int $time 用户数据时间
 * @property int $status 状态
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $auth_status
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereAuthStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserAuth orderByCustom($column = null, $direction = 'asc')
 * @property int $quality 新老用户 0新用户 1老用户
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereQuality($value)
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserAuth whereMerchantId($value)
 */
class UserAuth extends \Common\Models\User\UserAuth
{

    public function getAuth($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
            'status' => self::STATUS_VALID,
        ];
        return self::model()->where($where)->first();
    }

}
