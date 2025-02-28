<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserPosition
 *
 * @property int $id
 * @property int $user_id 用户id
 * @mixin \Eloquent
 * @property int|null $order_id 申请流水号
 * @property string|null $control_no 控件编号
 * @property int|null $start_time 开始时间
 * @property int|null $end_time 结束时间
 * @property string|null $old_value 原值
 * @property string|null $new_value 新值
 * @property \Illuminate\Support\Carbon|null $created_at 记录创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereControlNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereNewValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereOldValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior whereUserId($value)
 */
class UserBehavior extends Model
{
    use StaticModel;

    const UPDATED_AT = null;
    protected $table = 'user_behavior';

    /** 控件list */
    const P07_Enter = 'P07_Enter'; //启动签约页
    const P07_S_LoanAmount = 'P07_S_LoanAmount'; //选择金额
    const P07_S_LoanTerm = 'P07_S_LoanTerm'; //选择期限
    const P07_C_Submit = 'P07_C_Submit'; //签约页点击提交
    const P07_C_Back = 'P07_C_Back'; //签约页点击返回
    const P07_Leave = 'P07_Leave'; //关闭首页
    const P02_Enter = 'P02_Enter'; //启动个人信息填写页
    const P02_I_Name = 'P02_I_Name'; //启动个人信息填写页
    const P02_I_FirstName = 'P02_I_FirstName';
    const P02_I_MiddleName = 'P02_I_MiddleName';
    const P02_I_LastName = 'P02_I_LastName';
    const P02_I_Mail = 'P02_I_Mail'; //填写邮箱
    const P02_C_Back = 'P02_C_Back'; //个人信息填写页点击返回
    const P02_C_Next = 'P02_C_Next'; //个人信息填写页点击下一步
    const P02_Leave = 'P02_Leave'; //个人信息填写页关闭页面
    const P03_Enter = 'P03_Enter'; //启动联系信息填写页
    const P03_I_Street = 'P03_I_Street'; //详细地址(街道、门牌号、房间号)
    const P03_C_Back = 'P03_C_Back'; //联系信息填写页点击返回
    const P03_C_Next = 'P03_C_Next'; //联系信息填写页点击下一步
    const P03_Leave = 'P03_Leave'; //联系信息填写页关闭页面
    const P04_Enter = 'P04_Enter'; //启动工作信息填写页
    const P04_I_Company = 'P04_I_Company'; //填写公司名称
    const P04_I_Position = 'P04_I_Position'; //填写工作职位
    const P04_I_CompanyPhone = 'P04_I_CompanyPhone'; //填写工作电话
    const P04_C_Back = 'P04_C_Back'; //工作信息填写页点击返回
    const P04_C_Next = 'P04_C_Next'; //工作信息填写页点击下一步
    const P04_Leave = 'P04_Leave'; //工作信息填写页关闭页面
    const P05_Enter = 'P05_Enter'; //启动身份验证页
    const P05_I_IDNumber = 'P05_I_IDNumber'; //证件号
    const P05_C_Back = 'P05_C_Back'; //身份验证页点击返回
    const P05_C_Next = 'P05_C_Back'; //身份验证页点击下一步
    const P05_Leave = 'P05_Leave'; //身份验证页关闭页面
    const P06_Enter = 'P06_Enter'; //启动支付信息页
    const P06_I_CardNumber = 'P06_I_CardNumber'; //填写银行卡号
    const P06_C_EditRegisteredPhone = 'P06_I_CardNumber'; //修改注册手机号
    const P06_I_Password = 'P06_I_Password'; //设置密码
    const P06_I_RepeatPassword = 'P06_I_RepeatPassword'; //再次输入密码
    const P06_C_Submit = 'P06_C_Submit'; //支付信息页点击确认提交
    const P06_C_Back = 'P06_C_Back'; //返回身份认证页面
    const P06_Leave = 'P06_Leave'; //支付信息页关闭页面

    protected $fillable = [
        'user_id',
        'order_id',
        'control_no', //控件编号
        'start_time', //开始时间
        'end_time', //结束时间
        'old_value', //原值
        'new_value', //新值
    ];
    protected $guarded = [];
    protected $hidden = [];

    public function batchAdd($data)
    {
        foreach ($data as &$item) {
            $item = array_only($item, $this->fillable);
            $item['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insertIgnore($data);
    }
}
