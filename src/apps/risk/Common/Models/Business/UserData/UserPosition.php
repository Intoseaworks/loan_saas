<?php

namespace Risk\Common\Models\Business\UserData;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\UserData\UserPosition
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id 用户id
 * @property float $longitude 经度
 * @property float $latitude 维度
 * @property string|null $province 省份
 * @property string|null $city
 * @property string|null $district 县/区
 * @property string|null $street 街道
 * @property string|null $address 详细地址
 * @property string|null $created_at 添加时间
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereUserId($value)
 * @mixin \Eloquent
 */
class UserPosition extends BusinessBaseModel
{
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录列ID
        'data.*.longitude' => 'required|numeric', // 经度 116.235416000000
        'data.*.latitude' => 'required|numeric', // 纬度 40.047052000000
        'data.*.province' => 'nullable|string', // 所在州
        'data.*.city' => 'nullable|string', // 城市
        'data.*.district' => 'nullable|string', // 县/区
        'data.*.street' => 'nullable|string', // 街道
        'data.*.address' => 'nullable|string', // 详细地址
        'data.*.created_at' => 'required|date', // 记录创建时间
    ];
    public $timestamps = false;
    protected $table = 'data_user_position';
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'longitude',
        'latitude',
        'province',
        'city',
        'district',
        'street',
        'address',
        'created_at',
    ];

    public static function getLastPosition($userId)
    {
        return UserPosition::query()->where('user_id', $userId)->latest('id')->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
