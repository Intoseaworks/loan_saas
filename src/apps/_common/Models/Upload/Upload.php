<?php

namespace Common\Models\Upload;

use Common\Traits\Model\StaticModel;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Upload\OssHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Upload\Upload
 *
 * @property int $id ID
 * @property int $user_id 用户ID
 * @property int $type 用途类型
 * @property int $status 状态-1:已删除 1:正常
 * @property int $source_id 来源ID
 * @property string $filename 文件名称
 * @property string $path 文件地址
 * @property string|null $created_at 创建时间
 * @property string|null $updated_at 更新时间
 * @property string $ext_info 扩展信息
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereExtInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereUserId($value)
 * @mixin \Eloquent
 * @property int $user_type 来源类型 1:APP 2:管理后台
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload orderByCustom($column = null, $direction = 'asc')
 * @property int $merchant_id merchant_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Upload\Upload whereMerchantId($value)
 */
class Upload extends Model
{
    use StaticModel;

    const SCENARIO_CREATE = 'create';

    const TYPE_PROMOTION = 'promotion';
    const TYPE_ID_FRONT = 'id_front';
    const TYPE_ID_BACK = 'id_back';
    const TYPE_ID_HANDHELD = 'id_handheld';
    const TYPE_FACES = 'facePass';
    const TYPE_USER_BLACK_LIST = 'user_black_list';
    const TYPE_COMPANY_ID = "company_id";
    /** Aadhaar Card正面 */
    const TYPE_AADHAAR_CARD_FRONT = 'aadhaarCardFront';
    /** Aadhaar Card反面 */
    const TYPE_AADHAAR_CARD_BACK = 'aadhaarCardBack';
    /** Aadhaar Card KYC */
    const TYPE_AADHAAR_CARD_KYC= 'aadhaarCardKYC';
    /** SIGN */
    const TYPE_SIGN = 'sign';
    /** SIGN 签名 */
    const TYPE_SIGN_NAME = 'sign_name';

    /** PAN Card */
    const TYPE_PAN_CARD = 'panCard';
    /** Work Card正面 */
    const TYPE_WORK_CARD_FRONT = 'workCardFront';
    /** Work Card反面 */
    const TYPE_WORK_CARD_BACK = 'workCardBack';
    /** Bill */
    const TYPE_BILL = 'bill';
    /** Pay slip */
    const TYPE_PAY_SLIP = 'paySlip';

    /** 护照身份 */
    const TYPE_PASSPORT_IDENTITY = 'passportIdentity';
    /** 护照人口统计 */
    const TYPE_PASSPORT_DEMOGRAPHICS = 'passportDemographics';

    /** 选民身份证正面 */
    const TYPE_VOTER_ID_CARD_FRONT = 'voterIdCardFront';
    /** 选民身份证反面 */
    const TYPE_VOTER_ID_CARD_BACK = 'voterIdCardBack';

    /** 驾驶证反面 */
    const TYPE_DRIVING_LICENSE_BACK = 'drivingLicenseBack';

    const TYPE_DOGIO_SIGN = 'dogioSign';
    /*菲律宾*/
    const TYPE_BUK_NIC = "MY01";  //Driver’s License
    const TYPE_BUK_PASSPORT = "MY02";  //PRC ID
    const TYPE_BUK_NIC_BACK = "MY01_BACK";
    
    

    /** 驾驶证正面 */
    const TYPE_DRIVING_LICENSE_FRONT = 'drivingLicenseFront';
    /**
     * 类型
     */
    const TYPE = [
        self::TYPE_PROMOTION => '广告图',
        self::TYPE_ID_FRONT => '身份证正面',
        self::TYPE_ID_BACK => '身份证背面',
        self::TYPE_ID_HANDHELD => '手持身份证',
        self::TYPE_USER_BLACK_LIST => '用户黑名单',
        self::TYPE_AADHAAR_CARD_FRONT => 'aadhaar card front',
        self::TYPE_AADHAAR_CARD_BACK => 'aadhaar card back',
        self::TYPE_PAN_CARD => 'panCard',
        self::TYPE_FACES => 'face recognition',
        self::TYPE_WORK_CARD_FRONT => 'work card front',
        self::TYPE_WORK_CARD_BACK => 'work card back',
        self::TYPE_SIGN => 'sign',
        self::TYPE_BILL => 'bill',
        self::TYPE_PAY_SLIP => 'pay slip',
        self::TYPE_PASSPORT_IDENTITY => 'possport identity',
        self::TYPE_PASSPORT_DEMOGRAPHICS => 'passport demographics',
        self::TYPE_VOTER_ID_CARD_FRONT => 'voter id card front',
        self::TYPE_VOTER_ID_CARD_BACK => 'voter id card back',
        self::TYPE_DRIVING_LICENSE_FRONT => 'driving license front',
        self::TYPE_DRIVING_LICENSE_BACK => 'driving license back',
        self::TYPE_SIGN_NAME => 'sign name',
        self::TYPE_AADHAAR_CARD_KYC => 'aadhaar card kyc',
        self::TYPE_COMPANY_ID => "Company ID",
    ];

    /*菲律宾证件类型*/
    const TYPE_BUK = [
        self::TYPE_BUK_NIC => 'National ID card(NIC)',
        self::TYPE_BUK_PASSPORT => 'PASSPORT',
        self::TYPE_BUK_NIC_BACK => "NIC Back",
        self::TYPE_FACES => "facePass"
    ];

    const TYPE_UNIQUE = [
        self::TYPE_BUK_NIC,
        self::TYPE_BUK_PASSPORT
    ];

    const USER_TYPE_APP = 1;
    const USER_TYPE_ADMIN = 2;

    /**
     * 状态 正常
     */
    const STATUS_NORMAL = 1;
    /**
     * 状态 正常
     */
    const STATUS_CLEAR = 2;
    /**
     * 状态 已删除
     */
    const STATUS_DELETE = -1;
    /**
     * 状态
     */
    const STATUS = [
        self::STATUS_DELETE => '已删除',
        self::STATUS_NORMAL => '正常',
    ];

    /**
     * 文件格式限制
     */
    const EXTENSION = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'csv',
        'xls',
        'xlsx',
    ];
    /**
     * @var string
     */
    protected $table = 'upload';
    /**
     * 批量赋值白名单
     * @var array
     */
    protected $fillable = [];
    /**
     * @var array
     */
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function safes()
    {
        return [
            self::SCENARIO_CREATE => [
                'user_id',
                'user_type',
                'type',
                'source_id',
                'filename',
                'path',
                'status' => self::STATUS_NORMAL,
                'ext_info',
            ],
        ];
    }

    /**
     * @return mixed
     */
    public static function hasOss()
    {
        return config('config.app_upload_oss');
    }

    /**
     * 保存文件
     * @param $file
     * @return mixed array
     */
    public static function moveFile($file)
    {
        $attributes = [];
        $ext = $file->getClientOriginalExtension();
        $attributes['filename'] = $file->getClientOriginalName();
        $filename = self::getUniqueFileName() . '.' . $ext;
        $attributes['path'] = 'uploads/' . date('Ym') . '/';
        $file->move(self::basePathUpload($attributes['path']), $filename);
        $attributes['path'] = $attributes['path'] . $filename;
        return $attributes;
    }

    /**
     * @return string
     */
    public static function getUniqueFileName()
    {
        return uniqid() . mt_rand(100, 999) . mt_rand(100, 999);
    }

    /**
     * @param $path
     * @return string
     */
    public static function basePathUpload($path)
    {
        return base_path('public/' . $path);
    }

    /**
     * 保存文件
     * @param $file
     * @return mixed array
     */
    public static function moveFileOss($file)
    {
        $attributes = [];
        $ext = $file->getClientOriginalExtension();
        $attributes['filename'] = $file->getClientOriginalName();
        if (!$ext) {
            $user = \Auth::user();
            EmailHelper::send([
                'user_id' => $user->id ?? 0,
                'filename' => $attributes['filename'],
                'ext' => $ext,
            ], '文件上传异常');
        }
        $filename = self::getUniqueFileName() . '.' . $ext;
        $dev = app()->environment('prod') ? 'prod/' : 'dev/';
        $attributes['path'] = 'data/housing-loan/' . $dev . date('Ym') . '/';
        $attributes['path'] = $attributes['path'] . $filename;
        $attributes['real_path'] = $file->getRealPath();
        if (!$result = OssHelper::helper()->uploadFile($attributes['path'], $file->getRealPath())) {
            return false;
        }
        return $attributes;
    }

    public function getFileByUser($userId, $type = null)
    {
        $where = [
            'user_id' => $userId,
            'status' => self::STATUS_NORMAL,
        ];
        $query = self::query()->where($where);
        if ($type) {
            $query->where('type', $type);
        }
        return $query->get();
    }

    public function getOneFileByUser($userId, $type = null)
    {
        $where = [
            'user_id' => $userId,
            'status' => self::STATUS_NORMAL,
        ];
        $query = self::query()->where($where);
        if ($type) {
            $query->where('type', $type);
        }
        return $query->first();
    }

    public function textRules()
    {
        return [];
    }

    /**
     * 获取对应类型上传资源id，路径键值对
     *
     * @param $id
     * @param array $type
     * @return mixed
     */
    public function getPathsBySourceIdAndType($id, array $type)
    {
        $where = [
            'source_id' => $id,
            'status' => self::STATUS_NORMAL,
        ];
        return $this->where($where)->whereIn('type', $type)->pluck('path', 'id');
    }

    /**
     * 获取对应类型上传资源
     *
     * @param $id
     * @param $type
     * @return mixed
     */
    public function hasBySourceIdAndType($id, $type)
    {
        $where = [
            'source_id' => $id,
            'status' => self::STATUS_NORMAL,
        ];
        return $this->where($where)->where('type', $type)->first();
    }

    /**
     * @param $data
     * @return $this
     */
    public function setExtInfo($data)
    {
        $this->ext_info = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * 设置文件password
     *
     * @param $password
     * @return Upload
     */
    public function setFilePassword($password)
    {
        return $this->setExtInfo(['password' => $password]);
    }

    /**
     * 获取对应类型上传资源
     *
     * @param $id
     * @param $type
     * @return mixed
     */
    public function hasByUserIdAndType($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'status' => self::STATUS_NORMAL,
        ];
        return $this->where($where)->where('type', $type)->first();
    }

    public function clear($userId, $type)
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
        ];
        return $this->where($where)->where('status', Upload::STATUS_NORMAL)->update([
            'status' => self::STATUS_CLEAR,
        ]);
    }

}
