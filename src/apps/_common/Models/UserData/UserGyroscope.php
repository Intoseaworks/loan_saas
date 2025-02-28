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
 * @property string|null $step 步骤
 * @property string|null $anglex x轴
 * @property string|null $angley y轴
 * @property string|null $anglez z轴
 * @property \Illuminate\Support\Carbon|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereAnglex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereAngley($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereAnglez($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserGyroscope whereUserId($value)
 */
class UserGyroscope extends Model
{
    use StaticModel;

    const STEP_LOGIN = 'login'; //登录
    const STEP_PERSONAL_DETAIL = 'personal_info'; //个人信息页
    const STEP_WORK_INFO = 'work_info'; //工作信息页
    const STEP_CONTACTS = 'contacts'; //联系信息页
    const STEP_PAYMENT = 'payment'; //收款方式页
    const STEP_FINISH_APPLY = 'finish_apply'; //完件提交页
    const UPDATED_AT = null;
    protected $table = 'user_gyroscope';
    protected $fillable = [
        'user_id',
        'order_id',
        'step', //获取阶段：登录、个人信息页、工作信息页、联系信息页、收款方式页、完件提交页
        'anglex', //x轴
        'angley', //y轴
        'anglez', //z轴
    ];
    protected $guarded = [];
    protected $hidden = [];

    public function add($data)
    {
        $item = array_only($data, $this->fillable);
        return $this->create($item);
    }

    public function batchAdd($data)
    {
        foreach ($data as &$item) {
            $item = array_only($item, $this->fillable);
            $item['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insertIgnore($data);
    }
}
