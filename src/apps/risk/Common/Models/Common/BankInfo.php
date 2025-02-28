<?php

namespace Risk\Common\Models\Common;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Common\BankInfo
 *
 * @property int $bank_info_id
 * @property string|null $bank 银行名
 * @property string|null $ifsc IFSC CODE
 * @property string|null $branch 支行
 * @property string|null $address 地址
 * @property string|null $contact 联系方式
 * @property string|null $city 城市
 * @property string|null $district 区
 * @property string|null $state 邦
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereBankInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereIfsc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BankInfo whereState($value)
 * @mixin \Eloquent
 */
class BankInfo extends RiskBaseModel
{
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
            $bankLick = "%{$bank}%";
            $query->where('bank', 'like', $bankLick);
        }
        // state
        if ($state = array_get($params, 'state')) {
            $stateLike = "%{$state}%";
            $query->where('state', 'like', $stateLike);
        }
        // city
        if ($city = array_get($params, 'city')) {
            $cityLike = "%{$city}%";
            $query->where('city', 'like', $cityLike);
        }
        // branch
        if ($branch = array_get($params, 'branch')) {
            $branchLike = "%{$branch}%";
            $query->where('branch', 'like', $branchLike);
        }
        // ifsc
        if ($ifsc = array_get($params, 'ifsc')) {
            $ifscLike = "%{$ifsc}%";
            $query->where('ifsc', 'like', $ifscLike);
        }

        return $query;
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
        return $query->groupBy('state')->pluck('city');
    }

    public function getStateList()
    {
        $query = self::query()->select(['state'])
            ->orderBy('state');
        return $query->groupBy('state')->pluck('state');
    }
}
