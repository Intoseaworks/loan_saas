<?php

namespace Common\Services\Rbac\Models;

use Common\Services\Rbac\Exceptions\MenuDoesNotExist;
use Common\Services\Rbac\Utils\Helper;
use Common\Traits\Model\GlobalScopeModel;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Guard;


/**
 * Common\Services\Rbac\Models\Menu
 *
 * @property int $id
 * @property string $path path guard + 所有父节点path
 * @property string $route route 对应前端路由
 * @property string $parent_path 父Id
 * @property string $name
 * @property int $is_show 是否显示菜单 1显示 0不显示 默认显示
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin).
 * @property string $icon 图标
 * @property int $sort
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Menu[] $children
 * @property-read \Common\Services\Rbac\Models\Menu $parent
 * @property-read \Common\Services\Rbac\Models\Permission $permission
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Menu onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereParentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Menu withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Menu withoutTrashed()
 * @mixin \Eloquent
 * @property int $merchant_id
 * @property int $type 菜单类型 1项目菜单 2子项目印牛服务菜单
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Menu whereType($value)
 */
class Menu extends Model
{
    use GlobalScopeModel;

    /**
     * @var int
     */
    const SHOW = 1;

    /**
     * @var int
     */
    const NOT_SHOW = 0;

    /**
     * @var int
     */
    const MENU_TYPE_NORMAL = 1;

    /**
     * @var int
     */
    const MENU_TYPE_SUB = 2;

    /**
     * @var array
     */
    public static $sortedTree = [];

    /**
     * @var string
     */
    protected static $guardName;

    /**
     * @var string
     */
    protected static $parentPath = '';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'path';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $fillable = [
        'path',
        'route',
        'parent_path',
        'name',
        'is_show',
        'path',
        'icon',
        'sort',
        'guard_name',
        'created_at',
        'updated_at',
    ];

    /**
     * 添加菜单
     *
     * @var array
     */
    protected $insertMenus = [];
    /**
     * 添加权限
     *
     * @var array
     */
    protected $insertActions = [];

    /**
     * Menu constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = Helper::getGuardName();
        parent::__construct($attributes);

        $this->connection = Helper::getConnection();
        $this->setTable(Helper::getDatabaseName($this->connection) . '.' . config('permission.table_names.menus'));
    }

    /**
     * @param string $path
     * @param null $guardName
     * @return Menu|Model|null|object
     * @throws MenuDoesNotExist
     */
    public static function findByPath(string $path, $guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $menu = static::where('path', $path)->where('guard_name', $guardName)->first();

        if (!$menu) {
            throw MenuDoesNotExist::withPath($path);
        }

        return $menu;
    }

    /**
     * @param string $name
     * @param null $guardName
     * @return Menu|Model|null|object
     * @throws MenuDoesNotExist
     */
    public static function findByName(string $name, $guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $menu = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$menu) {
            throw MenuDoesNotExist::named($name);
        }

        return $menu;
    }

    /**
     * 清除path的guard
     *
     * @param array $data
     * @param array $columns
     */
    public static function cleanPathPrefix(&$data, $columns = ['path', 'parent_path'])
    {
        foreach ($data as $k => $item) {
            if (isset($item['children']) && is_array($item['children'])) {
                static::cleanPathPrefix($data[$k]['children'], $columns);
            }
            foreach ($columns as $column) {
                if (isset($item[$column])) {
                    $data[$k][$column] = static::trimPath($data[$k][$column]);
                }
            }
        }
    }

    /**
     * @param $path
     * @return string
     */
    public static function trimPath($path)
    {
        return str_replace_first(static::pathPrefix(), '', $path);
    }

    /**
     * @return string
     */
    public static function pathPrefix()
    {
        if (static::$guardName) {
            return static::$guardName . '|';
        }

        return Helper::getGuardName() . '|';
    }

    public static function getParentRoles($parentPath, $level = 0)
    {
        if (!$parentPath) {
            return [];
        }

        if ($level > 5) {
            throw new \Exception('递归深度超过5层,menu数据结构有问题.path:' . $parentPath, 500);
        }

        $parents = [];
        $menu = static::where('path', $parentPath)
            ->with('roles')
            ->first();
        if ($menu) {
            $roleIds = $menu->roles
                ->pluck('id')
                ->toArray();
            if ($roleIds) {
                array_push($parents, ...$roleIds);
            }
            $roleIds1 = static::getParentRoles($menu->parent_path, ++$level);
            if ($roleIds1) {
                array_push($parents, ...$roleIds1);
            }
        }

        return array_unique($parents);
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.menus');
            $builder->where("{$table}.guard_name", Helper::getGuardName());
            $builder->where('is_show', static::SHOW);
        });

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }

    /**
     * 通过角色获取用户菜单
     *
     * @return array
     */
    public function findUserMenuByRole()
    {
        //超级管理员获取所有权限
        if (Helper::isSuperAdmin() || Helper::isRbacClosed()) {
            $menuPaths = static::get()
                ->pluck('path')
                ->toArray();
        } else {
            $user = Helper::getUser();
            $roleMenus = $user->getMenusViaRoles()->toArray();
            $menuPaths = static::getPathWithChildren($roleMenus);

            //功能包含的菜单
            $operationMenus = $user->getMenusViaOperations()
                ->pluck('menu_key')
                ->toArray();
            if ($operationMenus) {
                array_push($menuPaths, ...$operationMenus);
            }
        }

        return static::getMenuTreeByPath($menuPaths, ['*'], true);
    }

    /**
     * 获取自身和所有子节点path
     *
     * @param array $menus
     * @return array
     */
    public static function getPathWithChildren(array $menus)
    {
        $allMenus = static::all()->toArray();
        //遍历菜单,然后看菜单下是否有子菜单.因为如果有父级的权限会拥有所有子菜单的权限
        foreach ($menus as $menu) {
            $children = static::getChildMenus($allMenus, $menu['path']);
            if ($children) {
                array_push($menus, ...$children);
            }
        }

        return collect($menus)
            ->pluck('path')
            ->toArray();
    }

    /**
     * 获取所有子节点
     *
     * @param array $data
     * @param string $path
     * @return array
     */
    public static function getChildMenus($data = [], $path = '')
    {
        $children = [];
        if ($data && (is_array($data) || $data instanceof Collection)) {
            foreach ($data as $v) {
                if ($v['parent_path'] == $path) {
                    array_push($children, $v);
                    $son = static::getChildMenus($data, $v['path']);
                    if ($son) {
                        array_push($children, ...$son);
                    }
                }
            }
        }
        return $children;
    }

    /**
     * 根据菜单Id获取父菜单
     *
     * @param array $menuPaths
     * @param array $select
     * @param bool $withOperation
     * @param null $menuType
     * @return array
     */
    public static function getMenuTreeByPath(array $menuPaths, $select = ['*'], $withOperation = false, $menuType = null)
    {
        if ($menuType === null) {
            $menuType = Helper::menuType();
        }
        //查找菜单
        $menuPaths = array_unique($menuPaths);
        $menuLists = static::whereIn('path', $menuPaths)
            ->select($select)
            ->when($menuType, function ($query) use ($menuType) {
                $query->where('type', $menuType);
            })
            ->get()
            ->toArray();

        //获取所有菜单
        $allMenus = static::select($select)
            ->get()
            ->keyBy('path')
            ->toArray();
        //父节点集合
        $parentPathList = [];
        foreach ($menuLists as $menu) {
            if ($menu['parent_path']) {
                array_push($parentPathList, $menu['parent_path']);
            }
        }
        $parentPathList = array_unique($parentPathList);

        $groupMenus = [];
        //对操作进行分组,相同的父节点的放一起
        foreach ($menuLists as $list) {
            //兼容只有一级菜单的菜单
            if (empty($list['parent_path'])) {
                //顶级节点不处理(存在子节点)
                if (in_array($list['path'], $parentPathList)) {
                    continue;
                }
                $groupMenus['empty'][] = [
                    'name' => $list['name'],
                    'path' => $list['path'],
                    'key' => $list['route'],
                    'icon' => $list['icon'],
                    'sort' => $list['sort'],
                    'parent_path' => $list['parent_path'],
                    'children' => [],
                ];
                continue;
            }
            if (array_key_exists($list['parent_path'], $groupMenus)) {
                $groupMenus[$list['parent_path']][] = $list;
            } else {
                $groupMenus[$list['parent_path']] = [];
                $groupMenus[$list['parent_path']][] = $list;
            }
        }

        $sortMenulist = [];
        foreach ($groupMenus as $parentPath => $groupMenu) {
            //一级菜单不做处理
            if ($parentPath == 'empty') {
                continue;
            }
            static::$sortedTree = [];
            $parentMenus = static::getParentMenus($parentPath, $allMenus);
            //合并子节点和父节点
            array_push($parentMenus, ...$groupMenu);
            //$parentMenus[0] 是顶级节点
            if (array_key_exists($parentMenus[0]['path'], $sortMenulist)) {
                array_push($sortMenulist[$parentMenus[0]['path']], ...$parentMenus);
            } else {
                $sortMenulist[$parentMenus[0]['path']] = [];
                array_push($sortMenulist[$parentMenus[0]['path']], ...$parentMenus);
            }
        }

        $menuTrees = [];
        foreach ($sortMenulist as $list) {
            //去重
            $uniques = [];
            foreach ($list as $item) {
                if (array_key_exists($item['path'], $uniques)) {
                    continue;
                }
                $uniques[$item['path']] = $item;
            }
            $menuTrees[] = static::getTree($uniques)[0];
        }

        if (array_key_exists('empty', $groupMenus)) {
            array_push($menuTrees, ...$groupMenus['empty']);
        }

        Helper::sortRecursive($menuTrees, 'sort');

        Helper::cleanNullChildren($menuTrees);

        if ($withOperation) {
            $userOperations = Operation::getUserOperations()
                ->groupBy('menu_key')
                ->toArray();
            static::getOperations($menuTrees, $userOperations);
        }

        return $menuTrees;
    }

    /**
     * 获取父节点,顶级节点在数组第一个位置
     *
     * @param $symbol
     * @param $data
     * @return array
     */
    public static function getParentMenus($symbol, $data)
    {
        $parent = [];
        $temp = null;
        if (is_string($symbol) && isset($data[$symbol])) {
            $temp = $data[$symbol];
        } else {
            $temp = $symbol;
        }
        array_unshift($parent, $temp);

        if ($temp['parent_path'] != '' && isset($data[$temp['parent_path']])) {
            $temp = static::getParentMenus($data[$temp['parent_path']], $data);
            if ($temp) {
                array_unshift($parent, ...$temp);
            }
        }

        return $parent;
    }

    /**
     * 无限极分类,树状结构
     *
     * @param array $data
     * @param string $parentPath
     * @param int $level
     * @return array
     */
    public static function getTree($data = [], $parentPath = '', $level = 0)
    {
        $tree = [];
        if ($data && is_array($data)) {
            foreach ($data as $v) {
                if ($v['parent_path'] == $parentPath) {
                    $tree[] = [
                        'name' => $v['name'],
                        'path' => $v['path'],
                        'key' => $v['route'],
                        'icon' => $v['icon'],
                        'sort' => $v['sort'],
                        'parent_path' => $v['parent_path'],
                        'children' => static::getTree($data, $v['path'], $level + 1),
                    ];
                }
            }
        }

        return $tree;
    }

    /**
     * @param $menus
     * @param $userOperations
     * @param string $column
     */
    public static function getOperations(&$menus, &$userOperations, $column = 'children')
    {
        foreach ($menus as &$item) {
            if (isset($item[$column]) && is_array($item[$column]) && !empty($item[$column])) {
                static::getOperations($item[$column], $userOperations, $column);
            }
            // 最小子节点
            if (empty($item[$column])) {
                if (isset($userOperations[$item['path']])) {
                    // 获取operation
                    $item['operations'] = $userOperations[$item['path']];
                } else {
                    $item['operations'] = [];
                }
            }
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permission()
    {
        return $this->hasOne(Permission::class, 'menu_key', 'path');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_path', 'path');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_path', 'path');
    }

    /**
     * A menu can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.role_has_menus'),
            'menu_key',
            'role_id'
        );
    }

    /**
     * 获取格式化菜单列表
     *
     * @param bool $operation
     * @return array
     */
    public function formatList($operation = false)
    {
        $data = static::select(['*'])
            ->get()
            ->toArray();

        //无限极分类
        $menuTree = static::getTree($data);

        //排序
        Helper::sortRecursive($menuTree, 'sort');

        //返回功能
        if ($operation) {
            Operation::combineOperationMenu($menuTree);
        }

        Helper::cleanNullChildren($menuTree);

        return $menuTree;
    }

    /**
     * 同步菜单
     *
     * @param array $menus
     * @return bool
     * @throws \Throwable
     */
    public function syncMenu(array $menus)
    {
        $guardName = Helper::getGuardName();
        /** @var static[] $oldMenus */
        $oldMenus = static::where('type', static::MENU_TYPE_NORMAL)->get();
        $sortOldMenus = [];
        foreach ($oldMenus as $oldMenu) {
            $sortOldMenus[$oldMenu->path] = [
                'path' => $oldMenu->path,
                'key' => $oldMenu->route,
                'name' => $oldMenu->name,
                'parent' => $oldMenu->parent_path,
                'sort' => $oldMenu->sort,
                'icon' => $oldMenu->icon,
                'guard_name' => $oldMenu->guard_name,
            ];
        }

        $sortNewMenus = [];
        foreach ($menus as $menu) {
            $sortNewMenus[$guardName . '.' . $menu['key']] = [
                'path' => $guardName . '.' . $menu['key'],
                'key' => $menu['key'],
                'name' => $menu['name'],
                'parent' => $menu['parent'] ? $guardName . '.' . $menu['parent'] : '',
                'sort' => $menu['sort'],
                'icon' => $menu['icon'] ?? '',
                'guard_name' => $guardName,
            ];
        }

        $data = Helper::arrayDiff($sortOldMenus, $sortNewMenus);

        DB::transaction(function () use ($data, $guardName) {

            foreach ($data['delete'] as $delete) {
                $model = static::where('path', $delete['path'])->first();
                //这里使用model的delete方法,触发删除事件
                $model->delete();
            }

            foreach ($data['add'] as $add) {
                $insert = [
                    'path' => $add['path'],
                    'route' => $add['key'],
                    'name' => $add['name'],
                    'sort' => $add['sort'],
                    'icon' => $add['icon'],
                    'parent_path' => $add['parent'],
                    'guard_name' => $guardName,
                ];
                static::create($insert);
            }

            foreach ($data['update'] as $update) {
                $insert = [
                    'path' => $update['path'],
                    'route' => $update['key'],
                    'name' => $update['name'],
                    'sort' => $update['sort'],
                    'icon' => $update['icon'],
                    'parent_path' => $update['parent'],
                    'guard_name' => $guardName,
                ];
                static::where('path', $insert['path'])->update($insert);
            }
        });

        return true;

    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function dbTable()
    {
        return $this->getDbConnection()->table($this->getTable());
    }

    /**
     * @return \Illuminate\Database\Connection
     */
    public function getDbConnection()
    {
        return DB::connection($this->connection);
    }

    /**
     * @throws \Throwable
     */
    public function syncSubProjectMenu()
    {
        $connections = RbacConfig::getAllSubProjectConnection();
        $authMenus = $this->getAuthMenu();
        $menuKeys = array_keys($authMenus);
        $permissions = Permission::getPermissionByMenuKey($menuKeys)
            ->toArray();
        foreach ($authMenus as $k => $authMenu) {
            $authMenus[$k]['type'] = static::MENU_TYPE_SUB;
            $authMenus[$k]['name'] .= '(印牛服务)';
            $authMenus[$k]['sort'] = 999;
            unset($authMenus[$k]['id']);
        }
        foreach ($permissions as $k => $permission) {
            unset($permissions[$k]['id']);
        }

        foreach ($connections as $connection) {
            $db = DB::connection($connection);
            $db->transaction(function ($query) use ($authMenus, $permissions, $menuKeys) {
                $menu = $query->table(config('permission.table_names.menus'));
                $permission = $query->table(config('permission.table_names.permissions'));

                $menu->where('type', static::MENU_TYPE_SUB)->delete();
                $permission->whereIn('menu_key', $menuKeys)->delete();

                $menu->insert($authMenus);
                $permission->insert($permissions);
            });
        }
    }

    /**
     * 获取权限菜单
     *
     * @return array
     */
    public function getAuthMenu()
    {
        $menus = config('permission.subProjectMenus');
        $children = $this->getChildren($menus['include']);
        $parents = static::whereIn('path', $menus['include'])
            ->get()
            ->keyBy('path')
            ->toArray();
        $exclude = static::whereIn('path', $menus['exclude'])
            ->get()
            ->keyBy('path')
            ->toArray();
        return array_merge(array_diff_key($children, $exclude), $parents);
    }

    /**
     * 获取子节点
     *
     * @param array $parents
     * @param int $level
     * @param null $menus
     * @return array
     */
    public function getChildren(array $parents, $level = 0, &$menus = null)
    {
        $children = [];
        if ($menus === null) {
            $menus = [];
            $data = static::get()
                ->toArray();
            foreach ($data as $item) {
                if ($item['parent_path']) {
                    if (isset($menus[$item['parent_path']])) {
                        $menus[$item['parent_path']][$item['path']] = $item;
                    } else {
                        $menus[$item['parent_path']] = [];
                        $menus[$item['parent_path']][$item['path']] = $item;
                    }
                }
            }
        }

        foreach ($parents as $parent) {
            if (isset($menus[$parent]) && !empty($menus[$parent])) {
                $children = array_merge($children, $menus[$parent]);
                $temp = $this->getChildren(array_keys($menus[$parent]), ++$level, $menus);
                $children = array_merge($children, $temp);
            }
        }

        return $children;
    }

}
