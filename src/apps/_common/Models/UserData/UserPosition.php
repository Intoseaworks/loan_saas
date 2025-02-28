<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Common\Models\UserData\UserPosition
 *
 * @property int $id
 * @property int $user_id 用户id
 * @property float $longitude 经度
 * @property float $latitude 维度
 * @property string|null $province 省份
 * @property string|null $city
 * @property string|null $district 县/区
 * @property string|null $street 街道
 * @property string|null $address 详细地址
 * @property \Illuminate\Support\Carbon|null $created_at 添加时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPosition whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $order_id 申请流水号
 * @property string|null $type 上报场景
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPosition whereType($value)
 */
class UserPosition extends Model
{
    use StaticModel;

    const TYPE_LOGIN = 'login';
    const TYPE_FINISH_APPLY = 'finish_apply';

    const TYPE = [
        self::TYPE_LOGIN => '登录',
        self::TYPE_FINISH_APPLY => '完成进件',
    ];

    protected $table = 'user_position';
    protected $fillable = [
        'user_id',
        'order_id',
        'longitude',
        'latitude',
        'province',
        'city',
        'district',
        'street',
        'address',
        'type',
    ];
    protected $guarded = [];
    protected $hidden = [];

    const UPDATED_AT = null;

    public function add($userId, $data)
    {
        $item = array_only($data, $this->fillable);

        $res = DB::transaction(function () use ($userId, $item) {
            //$this->clearRepeatData($userId, $item['province'], $item['city']);

            return $this->create($item);
        });

        return $res;
    }

    /**
     * 根据用户所在 省份/城市 去重
     * @param $userId
     * @param $province
     * @param $city
     * @return mixed
     */
    public function clearRepeatData($userId, $province, $city)
    {
        $where = [
            'user_id' => $userId,
            'province' => $province,
            'city' => $city,
        ];

        return self::query()->where($where)->delete();
    }

    public function getAddress($userId)
    {
        $where = [
            'user_id' => $userId,
        ];
        return self::query()->where($where)->value('address');
    }

}
