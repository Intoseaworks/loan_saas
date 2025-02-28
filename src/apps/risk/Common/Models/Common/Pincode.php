<?php

namespace Risk\Common\Models\Common;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Common\Pincode
 *
 * @property int $id
 * @property string|null $pincode
 * @property string|null $districtname
 * @property string|null $statename 邦 | 直辖区
 * @property int $type 0 从官方提供数据录入 1 手工录入(补充)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode whereDistrictname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode wherePincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode whereStatename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pincode whereType($value)
 * @mixin \Eloquent
 */
class Pincode extends RiskBaseModel
{
    // 官方文件导入
    const TYPE_OFFICIAL = 0;
    // 手工录入
    const TYPE_MANUAL = 1;

    protected $table = 'pincode';
    protected $fillable = [];
    protected $hidden = [];

    public function textRules()
    {
        return [
            'array' => [
            ],
        ];
    }

    public function checkPincode($pincode)
    {
        if ($this->getPincodeData($pincode)) {
            return true;
        }
        return false;
    }

    public function getPincodeData($pincode)
    {
        return static::getOne(['pincode' => $pincode]);
    }
}
