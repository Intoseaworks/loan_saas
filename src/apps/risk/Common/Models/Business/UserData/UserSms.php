<?php

namespace Risk\Common\Models\Business\UserData;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\UserData\UserSms
 *
 * @property int $id 用户短信自增长id
 * @property int $app_id
 * @property int $user_id 用户id
 * @property string|null $sms_telephone 短信【发送|接收】电话
 * @property string|null $sms_centent 短信内容
 * @property string|null $sms_date 短信【发送|接收】的时间
 * @property int|null $type 1 接收 2发送
 * @property string|null $created_at 添加时间
 * @property string|null $sync_time 记录同步时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsCentent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSmsTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSms whereUserId($value)
 * @mixin \Eloquent
 */
class UserSms extends BusinessBaseModel
{
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录列ID
        'data.*.sms_telephone' => 'required', // 短信【发送|接收】电话
        'data.*.sms_centent' => 'nullable', // 短信内容
        'data.*.sms_date' => 'nullable|string', // 短信【发送|接收】的时间
        'data.*.type' => 'nullable|numeric', // 类型 1:接收 2:发送
        'data.*.created_at' => 'nullable|date', // 记录添加时间 Y-m-d H:i:s
    ];
    public $timestamps = false;
    protected $table = 'data_user_sms';
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'sms_telephone',
        'sms_centent',
        'sms_date',
        'type',
        'created_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
