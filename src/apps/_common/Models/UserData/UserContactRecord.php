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
class UserContactRecord extends Model
{
    use StaticModel;

    protected $fillable = [

    ];
    protected $guarded = [];
    protected $hidden = [];

    public $timestamps = false;

}
