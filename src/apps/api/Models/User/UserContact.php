<?php

namespace Api\Models\User;


use Common\Utils\MerchantHelper;

/**
 * Api\Models\User\UserContact
 *
 * @property int $id 用户紧急联系人自增长ID
 * @property int $user_id 用户ID
 * @property string $contact_fullname 紧急联系人名字
 * @property string $contact_telephone 紧急联系人电话
 * @property string|null $relation 紧急联系人关系(直系亲属,朋友,兄弟姐妹)
 * @property int $status
 * @property string|null $check_status 空号检测结果
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereCheckStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereContactFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @property \Illuminate\Support\Carbon|null $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\UserContact whereUpdatedAt($value)
 * @property int|null $times_contacted 联系人联系次数
 * @property int|null $last_time_contacted 最近联系时间
 * @property string|null $has_phone_number 是否有号码
 * @property int|null $starred 是否收藏
 * @property int|null $contact_last_updated_timestamp 联系人最后编辑时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereContactLastUpdatedTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereHasPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereLastTimeContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereStarred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereTimesContacted($value)
 * @property string|null $manual_call_result 人工呼叫结果
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereManualCallResult($value)
 * @property int|null $is_supplement 是否补充联系人
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereIsSupplement($value)
 */
class UserContact extends \Common\Models\User\UserContact
{

    const SCENARIO_CREATE = 'create';

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'user_id',
                'order_id',
                'contact_fullname',
                'contact_telephone',
                'contact_telephone_10P',
                'relation',
                'times_contacted', //联系人联系次数
                'last_time_contacted', //最近联系时间
                'has_phone_number', //是否有号码
                'starred', //是否收藏
                'contact_last_updated_timestamp',//联系人最后编辑时间
                'contact_created_at', //号码添加时间
                'status' => self::STATUS_ACTIVE,
                'manual_call_result',
                'is_supplement',
            ],
        ];
    }


    public static function isExist($userId, $contactTelephone){
        return self::query()->where(['user_id'=> $userId,'contact_telephone'=>$contactTelephone])
            ->exists();
    }
}
