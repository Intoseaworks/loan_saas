<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\ThirdPartyVerify
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $status
 * @property string $result
 * @property string|null $type
 * @property string|null $report_id
 * @property string|null $source_id
 * @property string|null $source_val
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereSourceVal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyVerify whereUserId($value)
 * @mixin \Eloquent
 */
class ThirdPartyVerify extends Model
{
    use StaticModel;

    const TYPE_BANK_CARD = 'bank_card';

    const TYPE_SERVICES_PAN_CARD = 'services_pan_card';
    const TYPE_SERVICES_AADHAAR_CARD = 'services_aadhaar_card';
    const TYPE_SERVICES_VOTER = 'services_voter';
    const TYPE_SERVICES_PASSPORT = 'services_passport';

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;

    /**
     * @var string
     */
    protected $table = 'third_party_verify';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];
    /**
     * @var bool
     */
    //public $timestamps = false;
    /**
     * @param $cardNo
     * @return bool|mixed
     */
    public static function verifyRes($cardNo, $type)
    {
        $bankCard = self::query()
            ->where('source_val', $cardNo)
            ->where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        if (!$bankCard) {
            return false;
        }
        if (!($result = json_decode($bankCard->result, true))) {
            return false;
        }
        return $result;
    }

    public function createPanCard($userId, $status, $result, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        return $this->create($userId, $status, $result, self::TYPE_SERVICES_PAN_CARD, $reportId, $sourceId, $sourceVal);
    }

    public function createAadhaarCard($userId, $status, $result, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        return $this->create($userId, $status, $result, self::TYPE_SERVICES_AADHAAR_CARD, $reportId, $sourceId, $sourceVal);
    }

    public function createVoter($userId, $status, $result, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        return $this->create($userId, $status, $result, self::TYPE_SERVICES_VOTER, $reportId, $sourceId, $sourceVal);
    }

    public function createPassPort($userId, $status, $result, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        return $this->create($userId, $status, $result, self::TYPE_SERVICES_PASSPORT, $reportId, $sourceId, $sourceVal);
    }

    public function createBankCard($userId, $status, $result, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        return $this->create($userId, $status, $result, self::TYPE_BANK_CARD, $reportId, $sourceId, $sourceVal);
    }

    /**
     * pan card
     *
     * @param $cardNo
     * @return bool|mixed
     */
    public static function panCardVerifyRes($sourceId)
    {
        $panCard = self::query()->where(['source_id' => $sourceId, 'type' => static::TYPE_SERVICES_PAN_CARD])
            ->orderBy('id', 'desc')
            ->first();
        if (!$panCard) {
            return false;
        }
        if (!($result = json_decode($panCard->result, true))) {
            return false;
        }
        return $result;
    }

    /**
     * @param $sourceId
     * @return bool|mixed
     */
    public static function addressVerifyRes($sourceId)
    {
        $address = self::query()->where(['source_id' => $sourceId])
            ->where(['status' => self::STATUS_SUCCESS])
            ->orderBy('id', 'desc')
            ->first();
        if (!$address) {
            return false;
        }
        if (!($result = json_decode($address->result, true))) {
            return false;
        }
        return $result;
    }


    public function textRules()
    {
        return [];
    }

    /**
     * @param $userId
     * @param $status
     * @param $result
     * @param $type
     * @param string $reportId
     * @param int $sourceId
     * @param string $sourceVal
     * @return bool
     */
    public function create($userId, $status, $result, $type, $reportId = '', $sourceId = 0, $sourceVal = '')
    {
        if (is_array($result)) {
            $result = json_encode($result, 256);
        }
        $model = new static();
        $model->user_id = $userId;
        $model->status = $status;
        $model->result = $result;
        $model->type = $type;
        $model->report_id = $reportId;
        $model->source_id = $sourceId;
        $model->source_val = $sourceVal;
        return $model->save();
    }

}
