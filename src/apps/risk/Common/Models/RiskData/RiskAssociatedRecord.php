<?php

namespace Risk\Common\Models\RiskData;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\RiskData\RiskAssociatedRecord
 *
 * @property int $id
 * @property string $account_id
 * @property string|null $aadhaar
 * @property string|null $pancard
 * @property string|null $telephone
 * @property string|null $bankcard
 * @property string|null $voteid
 * @property string|null $passport
 * @property string|null $imei
 * @property string|null $email
 * @property int|null $apply_cnt
 * @property int|null $issued_cnt
 * @property int|null $max_overdue_days
 * @property string|null $max_issued_amount
 * @property int|null $merchant_cnt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\Risk\Common\Models\RiskData\RiskAssociatedRecordVar[] $riskAssociatedRecordVar
 * @property-read int|null $risk_associated_record_var_count
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereAadhaar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereApplyCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereBankcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereIssuedCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereMaxIssuedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereMaxOverdueDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereMerchantCnt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord wherePancard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord wherePassport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RiskAssociatedRecord whereVoteid($value)
 * @mixin \Eloquent
 */
class RiskAssociatedRecord extends RiskBaseModel
{
    protected $table = 'risk_associated_record';

    protected $fillable = [];

    protected $guarded = [];

    public static function addRecord()
    {
        $attribute = [
            'account_id' => self::generateNo(),
        ];

        return self::create($attribute);
    }

    public static function generateNo($prefix = null)
    {
        $prefix = $prefix ?: (string)microtime(true);
        $no = 'AC_' . strtoupper(substr(md5(uniqid($prefix, true)), 8, 16));
        if (static::getByAccountId($no)) {
            return self::generateNo($prefix);
        }

        return $no;
    }

    public static function getByAccountId($id)
    {
        return self::query()->where('account_id', $id)->first();
    }

    public function recordVar($associatedValue)
    {
        DB::connection($this->getConnectionName())->transaction(function () use ($associatedValue) {
            foreach ($associatedValue as $type => $value) {
                $value = (array)$value;

                foreach ($value as $item) {
                    $recordVar = RiskAssociatedRecordVar::firstOrNew([
                        'risk_associated_record_id' => $this->id,
                        'type' => $type,
                        'value' => $item,
                    ]);
                    if (in_array($type, RiskAssociatedRecordVar::NEED_CNT_TYPE)) {
                        $recordVar->cnt++;
                    }
                    $recordVar->save();
                }
            }

            $this->refreshVar();
        });

        return true;
    }

    /**
     * 刷新 risk_associated_record 中存储的字段值
     * @return bool
     */
    public function refreshVar()
    {
        /** @var Collection $recordVar */
        $recordVar = $this->riskAssociatedRecordVar;

        $recordVarGroup = $recordVar->groupBy('type');

        foreach ($recordVarGroup as $type => $item) {
            $this->varTypeToFieldName($type, $item);
        }

        return $this->save();
    }

    protected function varTypeToFieldName($type, $collection)
    {
        if (!$collection instanceof \Illuminate\Support\Collection) {
            return false;
        }
        $values = $collection->sortByDesc('cnt')
            ->sortBy('created_at')
            ->pluck('value')
            ->unique()
            ->toArray();
        $value = current($values);

        switch ($type) {
            case RiskAssociatedRecordVar::TYPE_AADHAAR:
                $this->aadhaar = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_PANCARD:
                $this->pancard = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_TELEPHONE:
                $this->telephone = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_BANKCARD:
                $this->bankcard = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_VOTEID:
                $this->voteid = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_PASSPORT:
                $this->passport = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_IMEI:
                $this->imei = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_EMAIL:
                $this->email = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_ORDER_NO:
                $this->apply_cnt = count($values);
                break;
            case RiskAssociatedRecordVar::TYPE_ISSUED_ORDER_NO:
                $this->issued_cnt = count($values);
                break;
            case RiskAssociatedRecordVar::TYPE_MAX_OVERDUE_DAYS:
                $this->max_overdue_days = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_MAX_ISSUED_AMOUNT:
                $this->max_issued_amount = $value;
                break;
            case RiskAssociatedRecordVar::TYPE_APP_ID:
                $this->merchant_cnt = count($values);
                break;
        }
        return $this;
    }

    public function riskAssociatedRecordVar()
    {
        return $this->hasMany(RiskAssociatedRecordVar::class, 'risk_associated_record_id', 'id');
    }
}
