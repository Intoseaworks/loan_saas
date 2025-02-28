<?php

namespace Common\Services\Rbac\Models;

/**
 * Common\Services\Rbac\Models\ServicesMenu
 *
 * @property int                                                                                     $id
 * @property string                                                                                  $path path guard + 所有父节点path
 * @property string                                                                                  $route route 对应前端路由
 * @property string                                                                                  $parent_path 父Id
 * @property string                                                                                  $name
 * @property int                                                                                     $is_show 是否显示菜单 1显示 0不显示 默认显示
 * @property string                                                                                  $guard_name 守卫,区分模块.比如:前端(web),后端(admin).
 * @property string                                                                                  $icon 图标
 * @property int                                                                                     $sort
 * @property \Carbon\Carbon|null                                                                     $created_at
 * @property \Carbon\Carbon|null                                                                     $updated_at
 * @property \Carbon\Carbon|null                                                                     $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\ServicesMenu[] $children
 * @property-read \Common\Services\Rbac\Models\ServicesMenu                                            $parent
 * @property-read \Common\Services\Rbac\Models\Permission                                              $permission
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[]         $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereParentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $merchant_id
 * @property int $type 菜单类型 1项目菜单 2子项目印牛服务菜单
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\ServicesMenu whereType($value)
 */
class ServicesMenu extends Menu
{
    /**
     * ServicesMenu constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        //这使用印牛服务的连接,获取印牛服务菜单
        $this->connection = config('permission.microservice_connection');
    }
}
