<?php

namespace Common\Models\User;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserContact
 *
 * @property int $user_contact_id 用户紧急联系人自增长ID
 * @property int $user_id 用户ID
 * @property string $contact_fullname 紧急联系人名字
 * @property string $contact_telephone 紧急联系人电话
 * @property string|null $relation 紧急联系人关系(直系亲属,朋友,兄弟姐妹)
 * @property int $status
 * @property string|null $check_status 空号检测结果
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereCheckStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereContactFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereUserContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereUserId($value)
 * @mixin \Eloquent
 * @property int $id 用户紧急联系人自增长ID
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @property \Illuminate\Support\Carbon|null $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserContact whereUpdatedAt($value)
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
class UserContact extends Model
{
    use StaticModel;

    const IS_SUPPLEMENT = 1; // 补充联系人
    const IS_NOT_SUPPLEMENT = 0; // 非补充联系人

    /**
     * @var string
     */
    protected $table = 'user_contact';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    const RELATION_FATHER = 'FATHER';
    const RELATION_MOTHER = 'MOTHER';
    const RELATION_BROTHERS = 'BROTHERS';
    const RELATION_SISTERS = 'SISTERS';
    const RELATION_SON = 'SON';
    const RELATION_DAUGHTER = 'DAUGHTER';
    const RELATION_WIFE = 'WIFE';
    const RELATION_HUSBAND = 'HUSBAND';
    const RELATION_OTHER = 'OTHER';

    const RELATION_FAMILY_MEMBER = [
        self::RELATION_FATHER,
        self::RELATION_MOTHER,
        self::RELATION_BROTHERS,
        self::RELATION_SISTERS,
        self::RELATION_SON,
        self::RELATION_DAUGHTER,
        self::RELATION_WIFE,
        self::RELATION_HUSBAND,
        self::RELATION_OTHER,
    ];

    // 男性 relation
    const MALE_RELATION = [
        self::RELATION_FATHER,
        self::RELATION_BROTHERS,
        self::RELATION_SON,
        self::RELATION_HUSBAND,
    ];

    // 女性 relation
    const FEMALE_RELATION = [
        self::RELATION_MOTHER,
        self::RELATION_SISTERS,
        self::RELATION_DAUGHTER,
        self::RELATION_WIFE,
    ];

    /**
     * 可用状态
     */
    const STATUS_ACTIVE = 1;
    const STATUS_DELETE = 0;

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * 清理紧急联系人
     * @param $userId
     * @return int
     */
    public function clear($userId)
    {
        return $this->whereUserId($userId)->whereStatus(self::STATUS_ACTIVE)->whereIsSupplement(self::IS_NOT_SUPPLEMENT)->update(['status' => UserContact::STATUS_DELETE]);
    }

    /**
     * 检查是否进行空号检测
     * @param $userId
     * @return bool
     */
    public function hasCheckNull($userId)
    {
        return $this->whereUserId($userId)->whereStatus(self::STATUS_ACTIVE)->where('check_status', '!=', '')->exists();
    }
}
