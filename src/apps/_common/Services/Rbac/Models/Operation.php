<?php

namespace Common\Services\Rbac\Models;

use Closure;
use Common\Services\Rbac\Exceptions\OperationAlreadyExists;
use Common\Services\Rbac\Exceptions\OperationDoesNotExist;
use Common\Services\Rbac\Utils\Helper;
use Common\Traits\Model\GlobalScopeModel;
use Common\Utils\MerchantHelper;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Guard;

/**
 * Common\Services\Rbac\Models\Operation
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string $menu_key
 * @property string $remark
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereUpdatedAt($value)
 * @property int $merchant_id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation newQuery()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Operation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereMenuKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Operation whereRemark($value)
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Operation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Operation withoutTrashed()
 */
class Operation extends Model
{

    use SoftDeletes, GlobalScopeModel;

    /**
     * @var array
     */
    protected $fillable = ['id', 'name', 'menu_key', 'guard_name', 'remark', 'merchant_id'];

    /**
     * Operation constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = Helper::getGuardName();
        parent::__construct($attributes);

        $this->connection = Helper::getConnection();
        $this->setTable(Helper::getDatabaseName($this->connection) . '.' . config('permission.table_names.operations'));
    }

    /**
     * @param string $name
     * @param null $guardName
     * @return mixed
     */
    public static function findByName(string $name, $guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $operation = static::where('name', $name)->where('guard_name', $guardName)->first();

        if (!$operation) {
            throw OperationDoesNotExist::named($name);
        }

        return $operation;
    }

    /**
     * @param string $menuKey
     * @return Operation|null
     */
    public static function findByMenuKey(string $menuKey)
    {
        return static::where('menu_key', $menuKey)->get();
    }

    /**
     * 菜单和功能结合
     *
     * @param array $menus
     * @param array $operations
     * @param array $condition
     */
    public static function combineOperationMenu(array &$menus, &$operations = [], array $condition = [], $level = 1)
    {
        if ($level == 1) {
            /** @var Operation[] $operationsModels */
            $operationsModels = static::when(!empty($condition), function ($query) use ($condition) {
                $query->whereIn('id', $condition);
            })->get();

            foreach ($operationsModels as $operation) {
                $temp = [
                    'name' => $operation->name,
                    'key' => $operation->id,
                    'remark' => $operation->remark,
                ];
                if (array_key_exists($operation->menu_key, $operations)) {
                    $operations[$operation->menu_key][] = $temp;
                } else {
                    $operations[$operation->menu_key] = [];
                    $operations[$operation->menu_key][] = $temp;
                }
            }
        }

        foreach ($menus as &$menu) {
            if (isset($menu['children']) && !empty($menu['children'])) {
                static::combineOperationMenu($menu['children'], $operations, $condition, ++$level);
            } else {
                //最小节点
                if (array_key_exists($menu['path'], $operations)) {
                    $menu['children'] = $operations[$menu['path']];
                }
            }
        }
    }

    /**
     * 获取用户功能
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserOperations()
    {
        if (Helper::isSuperAdmin() || Helper::isRbacClosed()) {
            $operations = Operation::get();
        } else {
            $user = Helper::getUser();
            $operations = $user->getOperationsViaRoles();
        }

        return $operations;
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.operations');
            $builder->where("{$table}.guard_name", Helper::getGuardName());
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });

        static::setMerchantIdBootScope();
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.role_has_operations'),
            'operation_id',
            'role_id'
        );
    }

    /**
     * 添加功能
     *
     * @param $data
     * @return Role
     */
    public function add($data)
    {
        $condition = [
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'menu_key' => $data['menu_key'],
            'merchant_id' => MerchantHelper::getMerchantId(),
        ];
        if ($this->checkExists($condition)) {
            throw OperationAlreadyExists::create();
        }

        return DB::transaction(function () use ($data) {
            /** @var static $operation */
            $operation = static::create($data);
            $permissions = array_filter(explode(',', $data['permissions']));
            //添加路由
            $operation->givePermissionTo($permissions);

            return $operation;
        });

    }

    /**
     * @param array $condition
     * @param Closure|null $closure
     * @return bool
     * @throws OperationAlreadyExists
     */
    public function checkExists(array $condition, Closure $closure = null)
    {
        $query = static::where($condition);
        if ($closure) {
            $query->where($closure);
        }

        if ($query->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed ...$permissions
     * @return $this|BaseRole
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permissions) {
                return $this->getStoredPermission($permissions);
            })
            ->all();

        $this->permissions()->saveMany($permissions);

        return $this;
    }

    /**
     * @param $permissions
     * @return Permission
     */
    protected function getStoredPermission($permissions)
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return app(Permission::class)
                ->whereIn('name', $permissions)
                ->where('guard_name', $this->getDefaultGuardName())
                ->get();
        }

        return $permissions;
    }

    /**
     * @return string
     */
    protected function getDefaultGuardName()
    {
        return Guard::getDefaultName($this);
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.operation_has_permissions'),
            'operation_id',
            'permission_key'
        );
    }

    /**
     * 编辑功能
     *
     * @param $data
     * @return Role
     */
    public function edit($data)
    {
        $condition = [
            ['name', $data['name']],
            ['guard_name', $data['guard_name']],
            ['menu_key', $data['menu_key']],
            ['id', '<>', $data['id']],
        ];
        if ($this->checkExists($condition)) {
            throw OperationAlreadyExists::create();
        }

        return DB::transaction(function () use ($data) {
            $operation = static::findById($data['id']);
            $operation->name = $data['name'];
            $operation->guard_name = $data['guard_name'];
            $operation->menu_key = $data['menu_key'];
            isset($data['remark']) && $data['remark'] ? $operation->remark = $data['remark'] : '';
            $operation->save();

            $permissions = array_filter(explode(',', $data['permissions']));
            //更新功能
            $result = $operation->syncPermissions($permissions);

            return $operation;
        });
    }

    /**
     * @param int $id
     * @param null $guardName
     * @return Operation|Model|null|object
     */
    public static function findById(int $id, $guardName = null)
    {
        $guardName = $guardName ?? Guard::getDefaultName(static::class);

        $operation = static::where('id', $id)->where('guard_name', $guardName)->first();

        if (!$operation) {
            throw OperationDoesNotExist::withId($id);
        }

        return $operation;
    }

    /**
     * @param mixed ...$permissions
     * @return mixed
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    /**
     * 删除功能
     *
     * @param $id
     * @return bool|null
     */
    public function destory($id)
    {
        //这里要使用Model的delete方法,触发删除事件清除缓存.同时这里也会把关联的中间表删掉
        $operation = static::findById((int)$id);
        return $operation->delete();
    }

    public function list()
    {
        //TODO
    }

    /**
     * 同步操作
     *
     * @param array $operations
     */
    public function syncOperation(array $operations)
    {
        $guardName = Helper::getGuardName();
        DB::transaction(function () use ($guardName, $operations) {

            //获取数据库所有operationId
            $dbIds = static::select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $pushIds = [];
            foreach ($operations as $operation) {
                array_push($pushIds, $operation['id']);
                $data = array_only($operation, ['name', 'menu_key']);
                $data['guard_name'] = $guardName;
                $data['merchant_id'] = MerchantHelper::getMerchantId();
                /** @var Operation $operationModel */
                $operationModel = static::updateOrCreate(['id' => $operation['id'], 'merchant_id' => MerchantHelper::getMerchantId()], $data);
                if (is_array($operation['permissions'])) {
                    $operationModel->syncPermissions($operation['permissions']);
                }
            }
            $deleteIds = array_diff($dbIds, $pushIds);
            static::whereIn('id', $deleteIds)->forceDelete();
        });

        return true;
    }
}
