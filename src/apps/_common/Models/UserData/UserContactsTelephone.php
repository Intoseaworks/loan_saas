<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserContactsTelephone
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $contact_fullname
 * @property string|null $contact_telephone
 * @property int $relation_level 亲戚关系(0无亲戚关系,1近亲,2近亲模糊匹配,3远亲)
 * @property string|null $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereContactFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereRelationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserContactsTelephone whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $times_contacted 联系人联系次数
 * @property int|null $last_time_contacted 最近联系时间
 * @property string|null $has_phone_number 是否有号码
 * @property int|null $starred 是否收藏
 * @property int|null $contact_last_updated_timestamp 联系人最后编辑时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereContactLastUpdatedTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereHasPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereLastTimeContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereStarred($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereTimesContacted($value)
 * @property int|null $order_id 申请ID
 * @property int|null $contact_created_at 号码添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereContactCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserContactsTelephone whereOrderId($value)
 */
class UserContactsTelephone extends Model
{
    use StaticModel;

    protected $table = 'user_contacts_telephone';
    protected $fillable = [
        'user_id',
        'order_id',
        'contact_fullname',
        'contact_telephone',
        'relation_level',
        'contact_created_at',
        'times_contacted',
        'last_time_contacted',
        'has_phone_number',
        'starred',
        'contact_last_updated_timestamp',
        'created_at'
    ];
    protected $guarded = [];
    protected $hidden = [];

    public $timestamps = false;

    public function batchAdd($userId, $data)
    {
        foreach ($data as &$item) {
            $item = array_only($item, $this->fillable);
            $item['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->insertIgnore($data);

//            $this->clearRepeatData($userId, array_column($data, 'contact_telephone'));
//
//            return $this->insert($data);
    }

    public function clearRepeatData($userId, $telephones)
    {
        return self::query()->where('user_id', $userId)
            ->whereIn('contact_telephone', $telephones)
            ->delete();
    }

    /**
     * 获取用户ID通讯录
     * @param $userId
     * @param int $num
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getContacts($userId, $num = 10)
    {
        $query = self::query()->where('user_id', $userId);
        if ($num > 0) {
            $query->limit($num);
        }
        return $query->orderBy('id', 'desc')->get();
    }

    public function getContactsCount($userId, $datetime=''){
        $query = self::query()->where("user_id", $userId);
        if($datetime){
            $query = $query->where("created_at", "<", $datetime);
        }
        return $query->count();
    }
}
