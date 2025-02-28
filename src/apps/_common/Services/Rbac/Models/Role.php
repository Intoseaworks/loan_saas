<?php

namespace Common\Services\Rbac\Models;

use Admin\Models\Staff\Staff;
use Common\Services\Rbac\Utils\Helper;
use Common\Traits\Model\GlobalScopeModel;
use Common\Utils\MerchantHelper;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as BaseRole;


/**
 * Common\Services\Rbac\Models\Role
 *
 * @property int $id
 * @property string|null $remark 描述
 * @property string $name 角色名称
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin).
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Menu[] $menus
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $permissions
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Role onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Role permission($permissions)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Role withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Role withoutTrashed()
 * @mixin \Eloquent
 * @property int $merchant_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Operation[] $operations
 * @property-read \Illuminate\Database\Eloquent\Collection|\Admin\Models\Staff\Staff[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Role whereMerchantId($value)
 */
class Role extends BaseRole
{
    use SoftDeletes, GlobalScopeModel;

    /**
     * @var int
     */
    const SUPER_ROLE_ID = 1;

    /**
     * @var array
     */
    public $guarded = ['*'];

    /**
     * @var array
     */
    protected $fillable = ['name', 'guard_name', 'remark', 'created_at', 'updated_at', 'merchant_id', 'is_super'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    const IS_SUPER_YES = 1;
    const IS_SUPER_NO = 0;

    /**
     * Role constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = Helper::getGuardName();
        parent::__construct($attributes);

        $this->connection = Helper::getConnection();
        $this->setTable(Helper::getDatabaseName($this->connection) . '.' . config('permission.table_names.roles'));

    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.roles');
            $builder->where("{$table}.guard_name", Helper::getGuardName());
        });

        static::deleting(function (Role $model) {
            $model->menus()->detach();
            $model->users()->detach();
            $model->operations()->detach();
        });

        static::setMerchantIdBootScope();
    }

    /**
     * A role may be given various permissions.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.menu'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.role_has_menus'),
            'role_id',
            'menu_key'
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            config('permission.user_model'),
            'model',
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.model_has_roles'),
            'role_id',
            'model_id'
        );
    }

    /**
     * A role may be given various permissions.
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.operation'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.role_has_operations'),
            'role_id',
            'operation_id'
        );
    }

    public function isSuper()
    {
        return $this->is_super == static::IS_SUPER_YES;
    }

    public static function getSuperRole()
    {
        return static::where('is_super', static::IS_SUPER_YES)->first();
    }

    /**
     * 角色类表
     *
     * @param int $perPage
     * @param null|string $roleName
     * @return mixed
     */
    public function list($perPage = 20, $roleName = null)
    {
        $lists = static::withCount('users')
            ->when($roleName !== null, function ($query) use ($roleName) {
                $query->where('name', 'like', "%{$roleName}%");
            })
            ->paginate($perPage);
        return $lists;
    }

    /**
     * 获取角色和权限
     *
     * @param $id
     * @return Builder|\Illuminate\Database\Eloquent\Model|null|object|Role
     */
    public function findWithMenus($id)
    {
        $role = static::findById($id);
        $menus = $role->menus->pluck('path')->toArray();
        $operations = $role->operations;
        $operationMenuPath = $operations->pluck('menu_key')->toArray();
        $operationIds = $operations->pluck('id')->toArray();
        $menuTree = Menu::getMenuTreeByPath(array_merge($menus, $operationMenuPath), ['*'], false, false);

        $temp = [];
        Operation::combineOperationMenu($menuTree, $temp, $operationIds ?: [-1]);
        unset($role->operations);
        $role = $role->toArray();
        $role['menus'] = $menuTree;
        return $role;
    }

    /**
     * 通过角色Id获取菜单分组的权限列表
     *
     * @param array $roleIds
     * @return array
     */
    public function findWithPermissionByRole(array $roleIds)
    {
        $roles = static::with('menus')
            ->whereIn('id', $roleIds)
            ->get()
            ->toArray();
        $data = [];
        foreach ($roles as $role) {
            $data[] = [
                'name' => $role['name'],
                'data' => Menu::getMenuTreeByPath(Menu::getPathWithChildren($role['menus'])),
            ];
        }

        return $data;
    }

    /**
     * 删除角色
     *
     * @param $id
     * @return bool|null
     */
    public function destory($id)
    {
        //这里要使用Model的delete方法,触发删除事件清除缓存.同时这里也会把关联的中间表删掉
        /** @var static $role */
        $role = static::findById((int)$id);

        if ($role->isSuper()) {
            throw new \Exception('超级角色无法删除', 500);
        }

        return $role->delete();
    }

    /**
     * 添加角色
     *
     * @param $data
     * @return Role
     */
    public function add($data)
    {
        return DB::transaction(function () use ($data) {
            /** @var static $role */
            $data['guard_name'] = Helper::getGuardName();
            $data['merchant_id'] = MerchantHelper::getMerchantId();
            $role = static::create($data);

            $menuKeys = (isset($data['menus']) && $data['menus'])
                ? array_filter(explode(',', $data['menus']))
                : [];
            $operations = (isset($data['operations']) && $data['operations'])
                ? array_filter(explode(',', $data['operations']))
                : [];
            if ($menuKeys) {
                //添加菜单
                $role->giveMenuTo($menuKeys);
            }
            if ($operations) {
                //添加功能(接口集合)
                $role->giveOperationTo(array_map('intval', $operations));
            }

            if (isset($data['users']) && $data['users']) {
                $userIds = array_filter(explode(',', $data['users']));
                foreach ($userIds as $userId) {
                    $this->assignRole($userId, $role->id);
                }
            }

            return $role;
        });
    }

    /**
     * 权限对应菜单
     *
     * @param mixed ...$menus
     * @return $this|BaseRole
     */
    public function giveMenuTo(...$menus)
    {
        $menus = collect($menus)
            ->flatten()
            ->map(function ($menus) {
                return $this->getStoredMenu($menus);
            })
            ->all();

        $this->menus()->saveMany($menus);

        return $this;
    }

    /**
     * @param $menus
     * @return Menu|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|null|object
     */
    protected function getStoredMenu($menus)
    {
        if (is_string($menus)) {
            return Menu::findByPath($menus, $this->getDefaultGuardName());
        }

        if (is_array($menus)) {
            return Menu::whereIn('name', $menus)
                ->where('guard_name', $this->getDefaultGuardName())
                ->get();
        }

        return $menus;
    }

    /**
     * 权限对应菜单
     *
     * @param mixed ...$operations
     * @return $this|BaseRole
     */
    public function giveOperationTo(...$operations)
    {
        $operations = collect($operations)
            ->flatten()
            ->map(function ($operations) {
                return $this->getStoredOperation($operations);
            })
            ->all();

        $this->operations()->saveMany($operations);

        return $this;
    }

    /**
     * @param $operations
     * @return Operation|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|mixed|null|object
     */
    protected function getStoredOperation($operations)
    {
        if (is_string($operations)) {
            return Operation::findByName($operations, $this->getDefaultGuardName());
        }

        if (is_numeric($operations)) {
            return Operation::findById($operations, $this->getDefaultGuardName());
        }

        if (is_array($operations)) {
            return Operation::whereIn('name', $operations)
                ->where('guard_name', $this->getDefaultGuardName())
                ->get();
        }

        return $operations;
    }

    /**
     * 赋值权限
     *
     * @param string $uid
     * @param string $roleIds
     * @param bool $sync
     * @throws \Exception
     */
    public function assignRole($uid, $roleIds, $sync = false)
    {
        /** @var Staff $user */
        $user = app('rbacUser')->whereId($uid)->first();
        if (!$user) {
            throw new \Exception('用户未找到', 500);
        }

        $user->assignRoleUser(explode(',', $roleIds), $sync);
    }

    /**
     * 编辑角色
     *
     * @param $data
     * @return Role
     */
    public function edit($data)
    {
        return DB::transaction(function () use ($data) {
            /** @var static $role */
            $role = static::findById($data['id']);
            //超级权限只能给人员赋值权限
            if (!$role->isSuper()) {
                $role->name = $data['name'];
                isset($data['remark']) && $data['remark'] ? $role->remark = $data['remark'] : '';
                isset($data['guard_name']) && $data['guard_name']
                    ? $role->guard_name = $data['guard_name']
                    : $role->guard_name = Helper::getGuardName();
                $role->save();

                $menuKeys = (isset($data['menus']) && $data['menus'])
                    ? array_filter(explode(',', $data['menus']))
                    : [];
                $operations = (isset($data['operations']) && $data['operations'])
                    ? array_filter(explode(',', $data['operations']))
                    : [];

                if ($menuKeys) {
                    //更新菜单
                    $role->syncMenus($menuKeys);
                } else {
                    //清空菜单
                    $role->menus()->detach();
                }

                if ($operations) {
                    //更新功能
                    $role->syncOperations(array_map('intval', $operations));
                } else {
                    //清空功能
                    $role->operations()->detach();
                }

            }

            if (isset($data['users'])) {
                $roleUsers = $role->users->pluck('id')->toArray();
                $userIds = array_filter(explode(',', $data['users']));
                //查看是否有用户被移除权限
                $removeUsers = array_diff($roleUsers, $userIds);
                if ($removeUsers) {
                    foreach ($removeUsers as $removeUser) {
                        $this->removeUserRole($removeUser, $role->id, $role->isSuper());
                    }
                }
                //新增用户
                $newUsers = array_diff($userIds, $roleUsers);
                if ($newUsers) {
                    foreach ($newUsers as $newUser) {
                        $this->assignRole($newUser, $role->id);
                    }
                }
            }

            return $role;
        });
    }

    /**
     * @param mixed ...$menus
     * @return Role|BaseRole
     */
    public function syncMenus(...$menus)
    {
        $this->menus()->detach();

        return $this->giveMenuTo($menus);
    }

    /**
     * @param mixed ...$operations
     * @return Role|BaseRole
     */
    public function syncOperations(...$operations)
    {
        $this->operations()->detach();

        return $this->giveOperationTo($operations);
    }

    /**
     * 去掉用户角色
     *
     * @param $uid
     * @param $roleId
     * @param $isSuperRole
     * @return bool|void
     * @throws \Exception
     */
    protected function removeUserRole($uid, $roleId, $isSuperRole)
    {
        /** @var Staff $user */
        $user = app('rbacUser')->whereId($uid)->first();
        if (!$user) {
            throw new \Exception('用户未找到', 500);
        }

        // 超管用户不能移除超管角色
        if ($user->isSuper() && $isSuperRole) {
            return false;
        }

        $user->removeRole($roleId);
    }

    /**
     * 用户授权列表
     *
     * @param int $perPage
     * @param null|array $roleId
     * @param null|string $userName
     * @return mixed
     */
    public function roleUserList($perPage = 20, $roleId = null, $userName = null)
    {
        $users = app('rbacUser')
            ->isActive()
            ->select(['id', 'username', 'nickname', 'last_login_ip', 'last_login_time', 'status'])
            ->whereHas('roles', function ($query) use ($roleId) {
                if ($roleId !== null) {
                    $query->whereIn('role_id', $roleId);
                }
            })
            ->with(['roles' => function ($query) use ($roleId) {
                $query->select('name')
                    ->when($roleId !== null, function ($query) use ($roleId) {
                        $query->whereIn('role_id', $roleId);
                    });
            }])->when($userName !== null, function ($query) use ($userName) {
                $query->where('nickname', 'like', "%$userName%");
                $query->orWhere('username', 'like', "%$userName%");
            })->orderByRaw('MID(username, 1, 2), MID(username,3,4)')
            ->paginate($perPage)
            ->toArray();

        foreach ($users['data'] as $k => $user) {
            $roleArr = [];
            foreach ($user['roles'] as $role) {
                $roleArr[] = $role['name'];
            }
            $users['data'][$k]['roles'] = $roleArr;
        }
        return $users;
    }

    /**
     * 清空用户角色
     *
     * @param $uid
     * @throws \Exception
     */
    public function detachRole($uid)
    {
        /** @var Staff $user */
        $user = app('rbacUser')->whereId($uid)->first();
        if (!$user) {
            throw new \Exception('用户未找到', 500);
        }

        $user->removeAllRoles();
    }

    /**
     * 用户权限检查 API鉴权
     *
     * @param $route
     * @param $path
     * @return bool
     * @throws \Exception
     */
    public function checkPermission($route, $path)
    {
        //API鉴权的时候,传过来的userId是正式环境的.如果是测试环境API鉴权.
        //可能会报用户未找到.所以这里不判断用户是否存在
        /** @var Staff $user */
        $user = Helper::getUser();
        return $user->checkPermission($route, $path);
    }

    /**
     * 未被授权角色的用户列表
     *
     * @param null|integer $roleId
     * @param null|string $userName
     * @return mixed
     */
    public function unauthorizedUserList($roleId = null, $userName = null)
    {
        $users = app('rbacUser')
            ->isActive()
            ->select(['id', 'username', 'nickname', 'last_login_ip', 'last_login_time', 'status'])
            ->when($roleId !== null, function ($query) use ($roleId) {
                $query->whereDoesntHave('roles', function ($query) use ($roleId) {
                    $query->where('id', $roleId);
                });
            })
            ->when($userName !== null, function ($query) use ($userName) {
                $query->where('nickname', 'like', "%$userName%");
                $query->orWhere('username', 'like', "%$userName%");
            })
            ->get();

        return $users;
    }

    /**
     * 用户角色列表
     *
     * @param $uid
     * @return mixed
     * @throws \Exception
     */
    public function userRoleList($uid)
    {
        /** @var Staff $user */
        $user = app('rbacUser')->whereId($uid)->first();
        if (!$user) {
            throw new \Exception('用户未找到', 500);
        }

        return $user->roles->each(function ($role) {
            unset($role->pivot);
        });
    }

}
