<?php

namespace Common\Models\UserData;

use Carbon\Carbon;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserPosition
 *
 * @property int $id
 * @property int $user_id 用户id
 * @mixin \Eloquent
 * @property int|null $order_id 申请流水号
 * @property int|null $focus_name_duration 姓名填写时长
 * @property int|null $focus_name_freq 姓名填写次数
 * @property int|null $focus_email_duration 邮箱填写时长
 * @property int|null $focus_email_freq 邮箱填写次数
 * @property int|null $focus_idno_duration 证件号码填写时长
 * @property int|null $focus_idno_freq 证件号码填写次数
 * @property int|null $focus_amount_duration 选择额度时长
 * @property int|null $focus_amount_freq 选择额度次数
 * @property int|null $focus_tenor_duration 选择期限时长
 * @property int|null $focus_tenor_freq 选择期限次数
 * @property int|null $focus_bankcct_duration 银行账号填写时长
 * @property int|null $focus_bankcct_freq 银行账号填写次数
 * @property int|null $focus_address_duration 详细地址填写时长
 * @property int|null $focus_address_freq 详细地址填写次数
 * @property int|null $gross_baseinfo_time 完成个人信息页总计耗时
 * @property int|null $gross_jobinfo_time 完成工作信息页总计耗时
 * @property int|null $gross_contact_time 完成联系信息页总计耗时
 * @property int|null $gross_idauth_time 完成证照页总计耗时
 * @property int|null $gross_receive_time 完成收款页总计耗时
 * @property int|null $gross_sign_time 完成签约页总计耗时
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusAddressDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusAddressFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusAmountDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusAmountFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusBankcctDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusBankcctFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusEmailDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusEmailFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusIdnoDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusIdnoFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusNameDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusNameFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusTenorDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereFocusTenorFreq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossBaseinfoTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossContactTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossIdauthTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossJobinfoTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossReceiveTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereGrossSignTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehaviorStatistics whereUserId($value)
 */
class UserBehaviorStatistics extends Model
{
    use StaticModel;

    const UPDATED_AT = null;
    protected $table = 'user_behavior_statistics';
    protected $fillable = [
        'user_id',
        'order_id',
        'focus_name_duration', //姓名填写时长
        'focus_name_freq', //姓名填写次数
        'focus_email_duration', //邮箱填写时长
        'focus_email_freq', //邮箱填写次数
        'focus_idno_duration', //证件号码填写时长
        'focus_idno_freq', //证件号码填写次数
        'focus_amount_duration', //选择额度时长
        'focus_amount_freq', //选择额度次数
        'focus_tenor_duration', //选择期限时长
        'focus_tenor_freq', //选择期限次数
        'focus_bankcct_duration', //银行账号填写时长
        'focus_bankcct_freq', //银行账号填写次数
        'focus_address_duration', //详细地址填写时长
        'focus_address_freq', //详细地址填写次数
        'gross_baseinfo_time', //完成个人信息页总计耗时
        'gross_jobinfo_time', //完成工作信息页总计耗时
        'gross_contact_time', //完成联系信息页总计耗时
        'gross_idauth_time', //完成证照页总计耗时
        'gross_receive_time', //完成收款页总计耗时
        'gross_sign_time', //完成签约页总计耗时
    ];
    protected $guarded = [];
    protected $hidden = [];

    public function add($data)
    {
        $data['created_at'] = Carbon::now()->toDateTimeString();
        return $this->create($data);
    }
}
