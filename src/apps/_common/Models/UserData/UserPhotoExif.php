<?php

namespace Common\Models\UserData;

use Carbon\Carbon;
use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

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
 * @property string|null $image_type 照片类型：证件正面，证件背面，自拍
 * @property string|null $image_name 照片文件名
 * @property string|null $model 相机型号
 * @property float|null $IMAGEWIDTH
 * @property float|null $IMAGEHEIGHT
 * @property float|null $PIXELYDIMENSION
 * @property float|null $PIXELXDIMENSION
 * @property string|null $make 相机制造商
 * @property string|null $sofeware 软件
 * @property string|null $datetimeoriginal 拍照时间
 * @property string|null $datetime 拍照时间
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereDatetimeoriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereIMAGEHEIGHT($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereIMAGEWIDTH($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereImageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereMake($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif wherePIXELXDIMENSION($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif wherePIXELYDIMENSION($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhotoExif whereSofeware($value)
 */
class UserPhotoExif extends Model
{
    use StaticModel;

    const IMAGE_TYPE_ID_FRONT = 'id_front'; //身份证件正面
    const IMAGE_TYPE_ID_BACK = 'id_back'; //身份证件背面
    const IMAGE_TYPE_SELFIE = 'selfie'; //自拍

    protected $table = 'user_photo_exif';
    protected $fillable = [
        'user_id',
        'order_id',
        'image_type', //照片类型：证件正面，证件背面，自拍
        'image_name', //照片文件名
        'model', //相机型号
        'IMAGEWIDTH', //照片宽度
        'IMAGEHEIGHT', //照片高度
        'PIXELYDIMENSION', //照片像素Y
        'PIXELXDIMENSION', //照片像素X
        'make', //相机制造商
        'sofeware', //软件
        'datetimeoriginal', //拍照时间
        'datetime', //拍照时间
        'latitude', //纬度
        'longitude', //经度
        'province', //经纬度对应省
        'city', //经纬度对应市
        'district', //经纬度对应区
        'street', //街道
        'address', //详细地址
    ];
    protected $guarded = [];
    protected $hidden = [];

    const SCENARIO_CREATE = 'create';
    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'user_id',
                'order_id',
                'image_type', //照片类型：证件正面，证件背面，自拍
                'image_name', //照片文件名
                'model', //相机型号
                'IMAGEWIDTH',
                'IMAGEHEIGHT',
                'PIXELYDIMENSION',
                'PIXELXDIMENSION',
                'make', //相机制造商
                'sofeware', //软件
                'datetimeoriginal', //拍照时间
                'datetime', //拍照时间
                'latitude', //纬度
                'longitude', //经度
                'province', //经纬度对应省
                'city', //经纬度对应市
                'district', //经纬度对应区
                'street', //街道
                'address', //详细地址
                'created_at' => Carbon::now()->toDateString()
            ]
        ];
    }

    const UPDATED_AT = null;

    public function add($data)
    {
        $item = array_only($data, $this->fillable);
        return $this->create($item);
    }

    public function batchAdd($datas)
    {
        return UserPhotoExif::model(UserPhotoExif::SCENARIO_CREATE)->saveModels($datas);
    }
}
