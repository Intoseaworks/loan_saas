<?php

namespace Common\Models\User;

use Common\Models\Upload\Upload;
use Common\Traits\Model\StaticModel;
use Common\Utils\Data\DateHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\User\UserThirdData
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $type
 * @property string|null $channel
 * @property string|null $result
 * @property string|null $report_id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\UserThirdData whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $res_status -1没有 1匹配 2不匹配
 * @property int|null $status 状态 1正常 -1失效
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereResStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserThirdData whereStatus($value)
 */
class UserThirdData extends Model
{
    use StaticModel;

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
    const TYPE_AADHAAR_CARD_TELEPHONE_CHECK = 'aadhaarCardTelephoneCheck';
    const TYPE_AADHAAR_CARD_AGE_CHECK = 'aadhaarCardAgeCheck';
    /** panCard用户名验证 */
    const TYPE_PANCARD_NAME_CHECK = 'pancardNameCheck';
    const TYPE_PANCARD_OCR_VERFIY_NAME_CHECK = 'pancardOcrVerfiyNameCheck';
    /** passport用户名验证 */
    const TYPE_PASSPORT_NAME_CHECK = 'passportNameCheck';
    /** voter用户名验证 */
    const TYPE_VOTER_NAME_CHECK = 'voterNameCheck';
    /** 用户填写生日比对 */
    const TYPE_INPUT_BIRTHDAY_CHECK = 'inputBirthdayCheck';
    /** 银行卡姓名比对 */
    const TYPE_BANKNAME_CHECK = 'banknameCheck';

    /** 选民 */
    const TYPE_VOTER_ID = 'addressVoterId';
    /** 选民身份证正面 */
    const TYPE_VOTER_ID_FRONT = 'addressVoterIdFront';
    /** 选民身份证反面 */
    const TYPE_VOTER_ID_BACK = 'addressVoterIdBack';

    /** pan card */
    const TYPE_PAN_CARD = 'panCard';
    /** pan card 正面 */
    const TYPE_PAN_CARD_FRONT = 'panCardFront';

    /** 驾驶证 */
    const TYPE_DRIVING_LICENCE = 'DrivingLicence';
    /** aadhaar */
    const TYPE_AADHAAR = 'addressAadhaar';

    const TYPE_FACE_COMPARISON = 'faceComparison';

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

    const USER_FILE_MAP = [
        self::TYPE_PAN_CARD => [
            Upload::TYPE_PAN_CARD,
        ],
        self::TYPE_PASSPORT => [
            Upload::TYPE_PASSPORT_IDENTITY,
            //Upload::TYPE_PASSPORT_DEMOGRAPHICS,
        ],
        self::TYPE_AADHAAR => [
            Upload::TYPE_AADHAAR_CARD_BACK,
            Upload::TYPE_AADHAAR_CARD_FRONT,
        ],
        self::TYPE_DRIVING_LICENCE => [
            Upload::TYPE_DRIVING_LICENSE_BACK,
            Upload::TYPE_DRIVING_LICENSE_FRONT,
        ],
        self::TYPE_VOTER_ID => [
            Upload::TYPE_VOTER_ID_CARD_FRONT,
            //Upload::TYPE_VOTER_ID_CARD_BACK,
        ],
    ];

    /**
     * @var string
     */
    protected $table = 'user_third_data';
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

    public function textRules()
    {
        return [];
    }


    /**
     * @param $userId
     * @param $type
     * @param $result
     * @param $channel
     * @param string $reportId
     * @return bool
     */
    public function create($userId, $type, $result, $channel = '', $reportId = '', $needClear = true)
    {
        if (is_array($result)) {
            $result = json_encode($result, 256);
        }
        if ($reportId == '') {
            $reportId = array_get($result, 'third_part_log_id', '');
        }
        $needClear && $this->clear($userId, $type);
        $model = new static();
        $model->user_id = $userId;
        $model->result = $result;
        $model->type = $type;
        $model->channel = $channel;
        $model->report_id = $reportId;
        $model->res_status = $this->getResStatus();
        $model->status = self::STATUS_NORMAL;
        return $model->save();
    }

    public function clear($userId, $type)
    {
        $where = ['user_id' => $userId, 'type' => $type];
        return UserThirdData::model()->where($where)->update(['status' => UserThirdData::STATUS_FORGET]);
    }

    public function getResStatus()
    {
        return $this->res_status ?: 0;
    }

    public function setResStatus($status)
    {
        $this->res_status = $status;
    }

    /**
     * 获取aadhaarOcr名称
     * @param $userId
     * @return mixed|string
     */
    public function getAadhaarName($userId)
    {
        $result = $this->getDataByType($userId, self::TYPE_AADHAAR_CARD_FRONT);
        if (!$result || !is_array($result)) {
            return '';
        }
        return array_get($result, 'name', '');
    }

    public function getPanName($userId)
    {
        $result = $this->getDataByType($userId, self::TYPE_PAN_CARD_FRONT);
        if (!$result || !is_array($result)) {
            return '';
        }
        return array_get($result, 'name', '');
    }

    public function getPanBirthday($userId)
    {
        $result = $this->getDataByType($userId, self::TYPE_PAN_CARD_FRONT);
        if (!$result || !is_array($result)) {
            return '';
        }
        return str_replace('-', '/', array_get($result, 'date_info', ''));
    }

    public function getaadhaarGender($userId)
    {
        $result = $this->getDataByType($userId, self::TYPE_AADHAAR_CARD_FRONT);
        if (!$result || !is_array($result)) {
            return '';
        }
        return array_get($result, 'gender', '');
    }

    /**
     * 是否验证aadhaar卡号
     * @param $userId
     * @return bool
     */
    public function getAadhaarVerityCheck($userId, $datetime = '')
    {
        $result = $this->getByType($userId, self::TYPE_AADHAAR_CARD);
        if (!$result) {
            return false;
        }
        # 参照时间，大于1天判为无效
        if ($datetime) {
            if (!$createDate = array_get($result, 'created_at')) {
                return false;
            }
            if (DateHelper::diffInDays($createDate, $datetime) > 1) {
                return false;
            }
        }
        return true;
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
}
