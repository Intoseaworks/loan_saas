<?php

namespace Admin\Models\Staff;

use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;

/**
 * Admin\Models\Staff\Staff
 *
 * @property int $id
 * @property string $username 用户名
 * @property string|null $nickname 昵称
 * @property string $password_hash 密码
 * @property string|null $role 角色
 * @property string|null $tissue 组织
 * @property string|null $uuid 用户唯一标示
 * @property int $company_id 公司id
 * @property string|null $ding_openid 钉钉openid
 * @property string|null $wechat_openid 微信openid
 * @property string|null $remember_token
 * @property string|null $email email
 * @property string $last_login_time 最后登录时间
 * @property string $last_login_ip 最后登录IP
 * @property int $status 状态
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $last_update_pwd_time 上次更新密码
 * @property int $is_need_update_pwd 需要更新密码
 * @property int $is_need_ding_login 是否需要钉钉登录，1:需要，0:不需要
 * @property string $ding_unionid 钉钉unionid
 * @property-read mixed $ip_address
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereDingOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereDingUnionid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereIsNeedDingLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereIsNeedUpdatePwd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereLastLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereLastUpdatePwdTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff wherePasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereTissue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereWechatOpenid($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Staff\Staff orderByCustom($column = null, $direction = 'asc')
 * @property string $last_login_time_old 最后登录时间
 * @property int $created_at_old
 * @property int $updated_at_old
 * @property int $last_update_pwd_time_old 上次更新密码
 * @property string|null $password 密码
 * @property string|null $deleted_at 删除时间
 * @property integer $merchant_id 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereCreatedAtOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereLastLoginTimeOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereLastUpdatePwdTimeOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereUpdatedAtOld($value)
 * @property int $is_super 是否超级管理员账号
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereIsSuper($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Admin\Models\Staff\Staff whereMerchantId($value)
 * @property int|null $show_chinese 1=显示中文;0=不显示中文
 * @property-read int|null $permissions_count
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereShowChinese($value)
 */
class Staff extends \Common\Models\Staff\Staff
{
    protected $appends = ['ip_address'];

    const SCENARIO_INFO = 'info';
    const SCENARIO_LIST = 'list';
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function getIpAddressAttribute()
    {
        if (empty($this->last_login_ip)) {
            return '';
        }
        if (is_numeric($this->last_login_ip)) {
            $ipAddress = HostHelper::isEN() ? HostHelper::getEnAddressByIp(long2ip((int)$this->last_login_ip)) : HostHelper::getAddressByIp(long2ip((int)$this->last_login_ip));
        } else {
            $ipAddress = HostHelper::isEN() ? HostHelper::getEnAddressByIp($this->last_login_ip) : HostHelper::getAddressByIp($this->last_login_ip);
        }
        return t($ipAddress, 'common');
    }

    public function texts()
    {
        return [
            self::SCENARIO_INFO => [
                'id',
                'username',
                'nickname',
            ],
            self::SCENARIO_LIST => [
                'id',
                'username',
                'nickname',
                'created_at',
                'last_login_ip',
                'last_login_time',
                'status',
                'last_update_pwd_time',
                'email',
                'is_super',
                'show_chinese',
                'levels',
                'language',
            ],
        ];
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'merchant_id' => MerchantHelper::getMerchantId(),
                'username',
                'nickname',
                'password_hash',
                'last_login_time',
                'last_login_ip',
                'last_update_pwd_time',
                'company_id',
                'status' => self::STATUS_NORMAL,
                'ding_unionid',
                'password',
                'created_at' => $this->getDate(),
                'updated_at' => $this->getDate(),
            ],
            self::SCENARIO_UPDATE => [
                'username',
                'nickname',
                'password_hash',
                'last_update_pwd_time',
                'status',
                'ding_openid',
                'company_id',
                'ding_unionid',
                'password',
                'updated_at' => $this->getDate(),
            ],
        ];
    }

    public function textRules()
    {
        return [
            'time' => [
                'last_update_pwd_time',
            ],
            'array' => [
                'status' => self::STATUS,
            ],
            'function' => [
                'last_login_time' => function ($data) {
                    //return $data->last_login_time ? date('Y-m-d H:i:s', (int)$data->last_login_time) : '';
                    return $data->last_login_time ? $data->last_login_time : '';
                }
            ],
        ];
    }

    public function isSuper()
    {
        return $this->is_super == static::IS_SUPER_YES;
    }

    /**
     * 根据id修改数据
     * @param $id
     * @param $data
     * @return mixed
     */
    public static function updateById($id, $data)
    {
        return self::where('id', $id)->update($data);
    }

    public function getList($param)
    {
        $size = array_get($param, 'size');
        return $this->when(isset($param['id']) && $param['id'] != null, function ($query) use ($param) {
            $query->where('staff.id', $param['id']);
        })
            ->when(isset($param['status']) && $param['status'] != null, function ($query) use ($param) {
                $query->where('status', $param['status']);
            })
            ->when(isset($param['keyword']) && $param['keyword'] != null, function ($query) use ($param) {
                $query->where('username', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('nickname', 'like', '%' . $param['keyword'] . '%');
            })
            ->with(['roles'])
            ->orderBy('staff.id', 'desc')
            ->paginate($size);
    }

    public function getSpecialList($param)
    {
        $size = array_get($param, 'size');
        return $this->when(isset($param['id']) && $param['id'] != null, function ($query) use ($param) {
            $query->where('staff.id', $param['id']);
        })
            ->when(isset($param['status']) && $param['status'] != null, function ($query) use ($param) {
                $query->where('status', $param['status']);
            })
            ->when(isset($param['keyword']) && $param['keyword'] != null, function ($query) use ($param) {
                $query->where('username', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('nickname', 'like', '%' . $param['keyword'] . '%');
            })
            ->whereHas('roles',function ($query){
                $query->where('name','催收Collection');
            })
            ->with(['roles'])
            ->orderBy('staff.id', 'desc')
            ->paginate($size);
    }

    /**
     * 根据id获取
     * @param $id
     * @return $this|static
     */
    public function getOne($id)
    {
        return self::whereId($id)->first();
    }

    /**
     * 根据id获取名字
     * @param $id
     * @return mixed
     */
    public function getNameById($id)
    {
        return self::whereId($id)->value('nickname');
    }

    /**
     * 根据username获取
     * @param $username
     * @return $this|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOneByUsername($username)
    {
        return self::whereUsername($username)->first();
    }

    /**
     * 根据钉钉openid获取
     * @param $dingOpenId
     * @return $this
     */
    public function getOneByDingOpenId($dingOpenId)
    {
        return self::whereDingOpenid($dingOpenId)->first();
    }

    /**
     * 根据钉钉unionid获取
     * @param $dingUnionId
     * @return $this
     */
    public function getOneByUnionId($dingUnionId)
    {
        return self::whereDingUnionid($dingUnionId)->first();
    }

    public function deleteById($id)
    {
        return $this->whereId($id)->update([
            'status' => self::STATUS_DELETE,
        ]);
    }

    public function updateStatusById($id, $status)
    {
        return $this->whereId($id)->update([
            'status' => $status,
        ]);
    }

    public function initialization($id)
    {
        return $this->whereId($id)->update([
            'is_need_update_pwd' => self::IS_NEED_UPDATE_PWD_NEED,
            'ding_openid' => '',
        ]);
    }

    public function reset($id)
    {
        return $this->whereId($id)->update([
            'is_need_update_pwd' => self::IS_NEED_UPDATE_PWD_NEED,
        ]);
    }

    /**
     * @param $adminIds
     * @return mixed
     */
    public function getNamesByAdminIds($adminIds)
    {
        return self::where('id', $adminIds)->pluck('username', 'id');
    }

    /**
     * @param $adminIds
     * @return mixed
     */
    public function getNormalIdsByAdminIds($adminIds)
    {
        return self::whereIn('id', $adminIds)->where('status', self::STATUS_NORMAL)->pluck('id')->toArray();
    }

    /**
     * 判断是否支持多地登录
     */
    /*public function supportAnyLogin()
    {
        if (config('config.any_login') == '1') {
            return true;
        }
        if (isset($this->is_any_login) && $this->is_any_login == Staff::IS_ANY_LOGIN_SUPPORT) {
            return true;
        }
        return false;
    }*/

}
