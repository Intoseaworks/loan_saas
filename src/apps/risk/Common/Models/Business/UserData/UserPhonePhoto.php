<?php

namespace Risk\Common\Models\Business\UserData;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\UserData\UserPhonePhoto
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id 用户id
 * @property string|null $storage 存储位置
 * @property float $longitude 经度
 * @property float $latitude 维度
 * @property string|null $length 照片长度
 * @property string|null $width 照片宽度
 * @property string|null $province 省份
 * @property string|null $city 市
 * @property string|null $district 县/区
 * @property string|null $street 街道
 * @property string|null $address 详细地址
 * @property string|null $photo_created_time 拍照时间
 * @property string|null $all_data 存储位置
 * @property string|null $created_at 添加时间
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereAllData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto wherePhotoCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhonePhoto whereWidth($value)
 * @mixin \Eloquent
 */
class UserPhonePhoto extends BusinessBaseModel
{
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录列ID
        'data.*.storage' => 'nullable|string', // 存储位置  /storage/emulated/0/Pictures/Screenshots/20190220-101100.jpg
        'data.*.longitude' => 'required|numeric', // 经度 113.198768972222
        'data.*.latitude' => 'required|numeric', // 维度 23.416796000000
        'data.*.length' => 'nullable|string', // 照片长度 2280
        'data.*.width' => 'nullable|string', // 照片宽度 1080
        'data.*.province' => 'nullable|string', // 州
        'data.*.city' => 'nullable|string', // 城市
        'data.*.district' => 'nullable|string', // 区
        'data.*.street' => 'nullable|string', // 街道
        'data.*.address' => 'nullable|string', // 详细地址
        'data.*.photo_created_time' => 'nullable|date', // 拍照时间
        'data.*.all_data' => 'nullable|string', // 所有相册信息 {"date_time":"2018:02:04 22:33:55","image_length":"3456","latitude":"23.416796","name":"\/storage\/emulated\/0\/UCMobile\/Temp\/15177548191270.4994811770850389.jpg","image_width":"4608","longitude":"113.19876897222223"}
        'data.*.created_at' => 'nullable|string', // 记录添加时间
    ];
    public $timestamps = false;
    protected $table = 'data_user_phone_photo';
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'storage',
        'longitude',
        'latitude',
        'length',
        'width',
        'province',
        'city',
        'district',
        'street',
        'address',
        'photo_created_time',
        'all_data',
        'created_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
