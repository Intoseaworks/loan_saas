<?php

namespace Common\Models\Common;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Common\Pincode
 *
 * @property int $id
 * @property string|null $pincode
 * @property string|null $districtname
 * @property string|null $statename
 * @property int $type 0 从官方提供数据录入 1 手工录入(补充)
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode whereDistrictname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode wherePincode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode whereStatename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Cashnow\Common\Models\Business\Pincode whereType($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\Pincode orderByCustom($defaultSort = null)
 */
class Pincode extends Model
{
    use StaticModel;

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
