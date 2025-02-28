<?php

namespace Common\Models\Common;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Common\BankInfo
 *
 * @property int $bank_info_id
 * @property string|null $bank 银行名称
 * @property string|null $ifsc 银行编号
 * @property string|null $micr_code 编号
 * @property string|null $branch 分支
 * @property string|null $address 地址
 * @property string|null $contact 联系方式
 * @property string|null $city 所属城市
 * @property string|null $district 所属区
 * @property string|null $state 状态
 * @property int|null $created_time
 * @property int|null $updated_time
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereBankInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereCreatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereMicrCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Common\BankInfo whereUpdatedTime($value)
 * @mixin \Eloquent
 */
class BankInfo extends Model
{
    use StaticModel;

    protected $table = 'bank_info';
    protected $fillable = [];
    protected $hidden = [];

    protected $primaryKey = 'bank_info_id';

    public function textRules()
    {
        return [
            'array' => [
            ],
        ];
    }

    public function search($params)
    {
        $query = $this->newQuery();
        // bank
        if ($bank = array_get($params, 'bank')) {
            $bank = strtoupper(trim($bank));
            $bank = "%{$bank}%";
            $query->where('bank', 'like', $bank);
            $query->groupBy('state');
        }
        // state
        if ($state = array_get($params, 'state')) {
            $query->whereState($state);
            $query->groupBy('district');
        }
        // district
        if ($district = array_get($params, 'district')) {
            $query->whereDistrict($district);
            $query->groupBy('city');
        }
        // city
        if ($city = array_get($params, 'city')) {
            $query->whereCity($city);
            $query->groupBy('branch');
        }
        // branch
        if ($branch = array_get($params, 'branch')) {
            $query->whereBranch($branch);
        }
        // ifsc
        if ($ifsc = array_get($params, 'ifsc')) {
            $query->whereIfsc($ifsc);
        }

        return $query;
    }

    public function getBankList()
    {
        $query = $this->newQuery()
            ->groupBy('bank')
            ->orderBy('bank');
        return $query->pluck('bank');
    }

    public function getBranchList($state, $city)
    {
        $where = [
            'state' => $state,
            'city' => $city,
        ];
        $query = $this->newQuery()
            ->where($where)
            ->groupBy('branch')
            ->orderBy('branch');
        return $query->get();
    }

    public function getCityList($state)
    {
        $query = self::query()
            ->select(['city'])
            ->orderBy('city');
        if ($state) {
            $query->where(['state' => $state]);
        }
        return $query->groupBy('city')->pluck('city');
    }

    public function getStateList()
    {
        $query = self::query()->select(['state'])
            ->orderBy('state');
        return $query->groupBy('state')->pluck('state');
    }
}
