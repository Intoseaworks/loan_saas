<?php

namespace Risk\Common\Models\Business\User;

use Common\Utils\Data\DataHelper;
use Common\Utils\Data\DateHelper;
use Common\Utils\MerchantHelper;
use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\User\UserThirdData
 *
 * @property int $id
 * @property int $app_id
 * @property int|null $user_id
 * @property string|null $type
 * @property string|null $channel
 * @property string|null $result
 * @property string|null $report_id
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property int|null $res_status -1没有 1匹配 2不匹配
 * @property int|null $status 状态 1正常 -1失效
 * @property string|null $value
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereResStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereValue($value)
 * @mixin \Eloquent
 */
class UserThirdData extends BusinessBaseModel
{
    const STATUS_NORMAL = 1;//正常
    const STATUS_FORGET = -1;//失效

    const TYPE_PASSPORT = 'passport';
    /** 护照身份 */
    const TYPE_PASSPORT_IDENTITY = 'passportIdentity';
    /** 护照人口统计 */
    const TYPE_PASSPORT_DEMOGRAPHICS = 'passportDemographics';

    const TYPE_ADDRESS_AADHAAR = 'addressAadhaar';
    /** aadhaar */
    const TYPE_AADHAAR_CARD_KYC = 'addressAadhaarKyc';

    const TYPE_AADHAAR_CARD = 'aadhaarCard';
    /** aadhaar地址正面 */
    const TYPE_AADHAAR_CARD_FRONT = 'aadhaarCardFront';
    /** aadhaar地址反面 */
    const TYPE_AADHAAR_CARD_BACK = 'aadhaarCardBack';
    /** aadhaarCard用户名验证 */
    const TYPE_AADHAAR_CARD_TELEPHONE_CHECK = 'aadhaarCardTelephoneCheck'; //  "aadhaar_telephone":"xxxxxxx280"
    const TYPE_AADHAAR_CARD_AGE_CHECK = 'aadhaarCardAgeCheck'; // "aadhaar_age":"20-30"
    /** panCard用户名验证 */
    const TYPE_PANCARD_NAME_CHECK = 'pancardNameCheck'; // "auth_name":"YESHPAL SHARMA"
    const TYPE_PANCARD_OCR_VERFIY_NAME_CHECK = 'pancardOcrVerfiyNameCheck'; // TYPE_PAN_CARD_FRONT   "verfiy_name":"YESHPAL SHARMA"
    /** passport用户名验证 */
    const TYPE_PASSPORT_NAME_CHECK = 'passportNameCheck'; // "auth_name":"MAMOO HUSSAIN"
    /** voter用户名验证 */
    const TYPE_VOTER_NAME_CHECK = 'voterNameCheck'; // "auth_name":"YASHAPAL"
    /** 用户填写生日比对 */
    const TYPE_INPUT_BIRTHDAY_CHECK = 'inputBirthdayCheck';
    /** 银行卡姓名比对 */
    const TYPE_BANKNAME_CHECK = 'banknameCheck'; // "bankname":"Mr. Naveen  C"

    /** 选民 */
    const TYPE_VOTER_ID = 'addressVoterId';
    /** 选民身份证正面 */
    const TYPE_VOTER_ID_FRONT = 'addressVoterIdFront';
    /** 选民身份证反面 */
    const TYPE_VOTER_ID_BACK = 'addressVoterIdBack';

    /** pan card */
    const TYPE_PAN_CARD = 'panCard';
    /** pan card 正面 */
    const TYPE_PAN_CARD_FRONT = 'panCardFront'; // {"card_no":"JBUPS6608L","father_name":"SATISH","name":"YESHPAL SHARMA","date_info":"05\/01\/1991"}

    /** 驾驶证 */
    const TYPE_DRIVING_LICENCE = 'DrivingLicence';
    /** aadhaar */
    const TYPE_AADHAAR = 'addressAadhaar';

    const TYPE_FACE_COMPARISON = 'faceComparison';//{"identical":true,"score":0.915}

    /** 渠道 */
    const CHANNEL_SERVICES = 'Services';

    // 验证成功
    const RES_STATUS_SUCCESS = 1;
    // 验证失败
    const RES_STATUS_VERIFY_FAIL = 2;
    // 不满足验证条件
    const RES_STATUS_FAIL = -1;
    // 未知
    const RES_STATUS_NOT = 0;
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录列ID
        'data.*.type' => 'required|string', // 类型
        'data.*.channel' => 'string', // 渠道
        'data.*.result' => 'required|string', // 第三方原数据
        'data.*.value' => 'nullable', // 具体值
        'data.*.status' => 'required|integer', // 记录状态 -1:失效 1:有效 不传默认有效
        'data.*.created_at' => 'date', // 记录创建时间
        'data.*.updated_at' => 'date', // 最后修改时间
    ];
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'data_user_third_data';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [
        'id',
        'app_id',
        'user_id',
        'type',
        'channel',
        'result',
        'value',
        'report_id',
        'created_at',
        'updated_at',
        'res_status',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

    public function getDataByType($userId, $type)
    {
        $model = $this->getByType($userId, $type);
        return json_decode(optional($model)->result, true);
    }

    public function getByType($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
            'status' => self::STATUS_NORMAL
        ];
        return self::query()->where($where)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getAllByType($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
            'status' => self::STATUS_NORMAL
        ];
        return self::query()->where($where)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function create($userId, $type, $result, $channel = '', $reportId = '', $needClear = true)
    {
        if (is_array($result)) {
            $result = json_encode($result, 256);
        }
        $needClear && $this->clear($userId, $type);
        $model = new static();
        $model->id = DataHelper::getUniqueId();
        $model->app_id = MerchantHelper::getMerchantId();
        $model->user_id = $userId;
        $model->result = $result;
        $model->type = $type;
        $model->channel = $channel;
        $model->report_id = $reportId;
        $model->status = self::STATUS_NORMAL;
        $model->created_at = DateHelper::dateTime();
        $model->updated_at = DateHelper::dateTime();
        return $model->save();
    }

    public function clear($userId, $type)
    {
        $where = ['user_id' => $userId, 'type' => $type];
        return UserThirdData::model()->where($where)->update(['status' => UserThirdData::STATUS_FORGET]);
    }

    protected function itemFormat($item)
    {
        if (!isset($item['status'])) {
            $item['status'] = self::STATUS_NORMAL;
        }
        if (!isset($item['value'])) {
            $item['value'] = $this->getValue($item['type'], $item['result']);
        }
        if (!isset($item['res_status'])) {
            $item['res_status'] = self::RES_STATUS_SUCCESS;
        }

        return $item;
    }

    public function getValue($type, $result)
    {
        if (!$result = json_decode($result, true)) {
            return false;
        }

        switch ($type) {
            case self::TYPE_AADHAAR_CARD_TELEPHONE_CHECK:
                return $result['aadhaar_telephone'] ?? '';
            case self::TYPE_AADHAAR_CARD_AGE_CHECK:
                return $result['aadhaar_age'] ?? '';
            case self::TYPE_PANCARD_NAME_CHECK:
                return $result['auth_name'] ?? '';
            case self::TYPE_PANCARD_OCR_VERFIY_NAME_CHECK:
                return $result['verfiy_name'] ?? '';
            case self::TYPE_PASSPORT_NAME_CHECK:
                return $result['auth_name'] ?? '';
            case self::TYPE_VOTER_NAME_CHECK:
                return $result['auth_name'] ?? '';
            case self::TYPE_BANKNAME_CHECK:
                return $result['bankname'] ?? '';
            case self::TYPE_PAN_CARD_FRONT:
                return $result['name'] ?? '';
            case self::TYPE_FACE_COMPARISON:
                return $result['score'] ?? '';
            default:
                return '';
        }
    }
}
