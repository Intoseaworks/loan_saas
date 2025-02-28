<?php

namespace Risk\Common\Models\Business\User;

use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\User\UserContact
 *
 * @property int $id 用户紧急联系人自增长ID
 * @property int $app_id merchant_id
 * @property int $user_id 用户ID
 * @property string $contact_fullname 紧急联系人名字
 * @property string $contact_telephone 紧急联系人电话
 * @property string|null $relation 紧急联系人关系(直系亲属,朋友,兄弟姐妹)
 * @property int $status
 * @property string|null $check_status 空号检测结果
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 修改时间
 * @property int|null $times_contacted 联系人联系次数
 * @property int|null $last_time_contacted 最近联系时间
 * @property string|null $has_phone_number 是否有号码
 * @property int|null $starred 是否收藏
 * @property int|null $contact_last_updated_timestamp 联系人最后编辑时间
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereCheckStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereContactFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereContactLastUpdatedTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereHasPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereLastTimeContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereRelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereStarred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereTimesContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContact whereUserId($value)
 * @mixin \Eloquent
 */
class UserContact extends BusinessBaseModel
{
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
    public static $validate = [
        'data' => 'required|array',
        'data.*.id' => 'required|numeric', // 紧急联系人记录列ID
        'data.*.contact_fullname' => 'required|string', // 紧急联系人名字
        'data.*.contact_telephone' => 'required|string', // 紧急联系人电话
        'data.*.relation' => 'required|string', // 紧急联系人关系 FATHER MOTHER BROTHERS SISTERS SON DAUGHTER WIFE HUSBAND OTHER
        'data.*.status' => 'required|integer', // 状态 正常:1  弃用:0
        'data.*.created_at' => 'required|date', // 记录创建时间
        'data.*.updated_at' => 'required|date', // 记录最后修改时间
        'data.*.times_contacted' => 'nullable|integer', // 联系次数
        'data.*.last_time_contacted' => 'nullable|integer', // 最近联系时间 毫秒级时间戳
        'data.*.starred' => 'nullable|integer', // 是否收藏 是:1  否:0
        'data.*.contact_last_updated_timestamp' => 'nullable|integer', // 联系人最后编辑时间 毫秒级时间戳
        'data.*.has_phone_number' => 'nullable|integer', // 手机联系人下手机号的数量
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_user_contact';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'contact_fullname',
        'contact_telephone',
        'relation',
        'status',
        'check_status',
        'created_at',
        'updated_at',
        'times_contacted',
        'last_time_contacted',
        'has_phone_number',
        'starred',
        'contact_last_updated_timestamp',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function batchAddRiskData($userId, $data)
    {
        // 统一将用户下的数据修改为失效
        if (MerchantHelper::getMerchantId() && $userId) {
            static::query()->where([
                'app_id' => MerchantHelper::getMerchantId(),
                'user_id' => $userId,
            ])->update([
                'status' => self::STATUS_DELETE,
            ]);
        }

        return parent::batchAddRiskData($userId, $data);
    }

    protected function itemFormat($item)
    {
        $item['has_phone_number'] = 1;
        return $item;
    }
}
