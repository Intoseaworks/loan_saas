<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserSms
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $contact_fullname
 * @property string|null $contact_telephone
 * @property int $relation_level 亲戚关系(0无亲戚关系,1近亲,2近亲模糊匹配,3远亲)
 * @property string|null $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereContactFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereContactTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereRelationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserSms whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $sms_telephone 短信【发送|接收】电话
 * @property string|null $sms_centent 短信内容
 * @property string|null $sms_date 短信【发送|接收】的时间
 * @property int $type 1 接收 2发送
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsCentent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereType($value)
 */
class UserSms extends Model
{
    use StaticModel;

    protected $table = 'user_sms';
    protected $fillable = [
        'user_id',
        'type',
        'sms_telephone',
        'sms_centent',
        'sms_date',
        'order_id',
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
    }

    /**
     * 获取用户ID通讯录
     * @param $userId
     * @param int $num
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getSms($userId, $num = 10)
    {
        $query = self::query()->where('user_id', $userId);
        if ($num > 0) {
            $query->limit($num);
        }
        return $query->orderBy('id', 'desc')->get();
    }
}
