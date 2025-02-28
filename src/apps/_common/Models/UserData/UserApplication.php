<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserApplication
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property string|null $app_name 应用名
 * @property string|null $pkg_name 包名
 * @property int|null $installed_time 应用安装时间
 * @property int|null $updated_time 应用的最后更新时间
 * @property string|null $created_at 记录添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereInstalledTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication wherePkgName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereUpdatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserApplication whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $order_id 申请流水号
 * @property int $version 版本号，用于区分批次
 * @property string|null $version_name 版本名
 * @property int|null $is_system 是否系统应用 1是 0否
 * @property int|null $is_app_active 是否活动应用 1是 0否
 * @method static \Illuminate\Database\Eloquent\Builder|UserApplication whereIsAppActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserApplication whereIsSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserApplication whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserApplication whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserApplication whereVersionName($value)
 */
class UserApplication extends Model
{
    use StaticModel;

    protected $table = 'user_application';
    protected $fillable = [
        'user_id',
        'order_id',
        'app_name',
        'pkg_name',
        'installed_time',
        'updated_time',
        'version_name',
        'is_system', //是否系统应用
        'is_app_active', //是否活动应用
        'apply_id',
        'created_at'
    ];
    protected $guarded = [];
    protected $hidden = [];

    public $timestamps = false;

    public function batchAdd($userId, $data)
    {
        $version = $this->getLastVersion($userId);
        $version++;

        foreach ($data as &$item) {
            $item = array_only($item, $this->fillable);
            $item['created_at'] = date('Y-m-d H:i:s');
            $item['version'] = $version;
        }

        return $this->insertIgnore($data);

//        $this->clearRepeatData($userId, array_column($data, 'pkg_name'));
//
//        return $this->insert($data);
    }

    public function getAllData($userId, $deadline = null)
    {
        $where = [
            'user_id' => $userId,
        ];
        $query = self::query()->where($where);

        if (isset($deadline)) {
            $query->where('created_at', '<', $deadline);
        }

        $data = $query->get();

        return $data->unique('pkg_name')->toArray();
    }

    public function getLastVersionData($userId)
    {
        $lastVersion = $this->getLastVersion($userId);

        $where = [
            'user_id' => $userId,
            'version' => $lastVersion,
        ];
        $data = self::query()->where($where)->get();

        return $data->unique('pkg_name')->toArray();
    }

    public function getLastVersion($userId)
    {
        $lastRecord = self::query()->where('user_id', $userId)
            ->latest()
            ->latest('id')
            ->first();

        if (!$lastRecord) {
            return 0;
        }

        return $lastRecord->version;
    }

    public function clearRepeatData($userId, $pkgNames)
    {
        return self::query()->where('user_id', $userId)
            ->whereIn('pkg_name', $pkgNames)
            ->delete();
    }
}
