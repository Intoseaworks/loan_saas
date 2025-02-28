<?php

namespace Common\Services\Rbac\Models;

use Common\Services\Rbac\Utils\Helper;
use Common\Traits\Model\GlobalScopeModel;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Permission as BasePermission;


/**
 * Common\Services\Rbac\Models\Permission
 *
 * @property int $id
 * @property string $remark 描述
 * @property string $name 请求方式+路由
 * @property string $guard_name 守卫,区分模块.比如:前端(web),后端(admin).
 * @property string $menu_key 菜单对应key
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $children
 * @property-read \Common\Services\Rbac\Models\Menu $menu
 * @property-read \Common\Services\Rbac\Models\Permission $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Permission onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission permission($permissions)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Spatie\Permission\Models\Permission role($roles)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereMenuKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Permission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Common\Services\Rbac\Models\Permission withoutTrashed()
 * @mixin \Eloquent
 * @property int $merchant_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Services\Rbac\Models\Operation[] $operations
 * @property-read \Illuminate\Database\Eloquent\Collection|\Admin\Models\Staff\Staff[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Services\Rbac\Models\Permission whereMerchantId($value)
 */
class Permission extends BasePermission
{

    use GlobalScopeModel;

    /**
     * @var array
     */
    public $guarded = ['*'];
    /**
     * @var string
     */
    protected $primaryKey = 'name';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';
    /**
     * @var array
     */
    protected $fillable = ['remark', 'name', 'parent_id', 'menu_key', 'guard_name', 'created_at', 'updated_at'];

    /**
     * Permission constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = Helper::getGuardName();
        parent::__construct($attributes);

        $this->connection = Helper::getConnection();
        $this->setTable(Helper::getDatabaseName($this->connection) . '.' . config('permission.table_names.permissions'));
    }

    /**
     * @param array $menuKeys
     * @return mixed
     */
    public static function getPermissionByMenuKey(array $menuKeys)
    {
        return static::whereIn('menu_key', $menuKeys)->get();
    }

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('guard', function (Builder $builder) {
            $table = config('permission.table_names.permissions');
            $builder->where("{$table}.guard_name", Helper::getGuardName());
        });
    }

    /**
     * @return mixed
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_key', 'path');
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.role_has_permissions'),
            'permission_key',
            'role_id'
        );
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
     * A permission belongs to some users of the model associated with its guard.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            config('permission.user_model'),
            'model',
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.model_has_permissions'),
            'permission_id',
            'model_id'
        );
    }

    /**
     * @return BelongsToMany
     */
    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.operation'),
            Helper::getDatabaseName(Helper::getConnection()) . '.' . config('permission.table_names.operation_has_permissions'),
            'permission_key',
            'operation_id'
        );
    }

    /**
     * @param array $permissions
     * @return bool
     * @throws \Throwable
     */
    public function pushPermissions(array $permissions)
    {
        $guardName = Helper::getGuardName();
        /** @var static[] $oldPermissions */
        $oldPermissions = static::get();
        $sortOldPermissions = [];
        foreach ($oldPermissions as $oldPermission) {
            $sortOldPermissions[$oldPermission->name] = [
                'remark' => $oldPermission->remark,
                'name' => $oldPermission->name,
                'guard_name' => $oldPermission->guard_name,
                'menu_key' => $oldPermission->menu_key,
            ];
        }

        $sortNewPermissions = [];
        foreach ($permissions as $permission) {
            $sortNewPermissions[$permission['name']] = [
                'remark' => $permission['remark'],
                'name' => $permission['name'],
                'guard_name' => $guardName,
                'menu_key' => $permission['menu_key'],
            ];
        }

        $data = Helper::arrayDiff($sortOldPermissions, $sortNewPermissions);

        DB::transaction(function () use ($data, $guardName) {

            foreach ($data['delete'] as $delete) {
                static::where(['name' => $delete['name'], 'guard_name' => $delete['guard_name']])->delete();
            }

            foreach ($data['add'] as $add) {
                static::create($add);
            }

            foreach ($data['update'] as $update) {
                static::where(['name' => $update['name'], 'guard_name' => $update['guard_name']])->update($update);
            }
        });

        return true;
    }

}
