<?php

namespace Common\Models\Third;

use Common\Models\User\User;
use Common\Traits\Model\StaticModel;
use Common\Utils\MerchantHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Common\Models\Third\ThirdPartyLog
 *
 * @property int $id
 * @property int $system_send_id 对应系统认证记录唯一标识
 * @property int $merchant_id merchant_id
 * @property string|null $name
 * @property string|null $channel 调用渠道
 * @property int|null $user_id
 * @property string|null $report_id
 * @property string|null $remark
 * @property string|null $request
 * @property string|null $request_url
 * @property int|null $request_status
 * @property string|null $response
 * @property string|null $response_time
 * @property string|null $callback
 * @property string|null $callback_url
 * @property string|null $callback_time
 * @property int|null $callback_status
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereCallback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereCallbackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereCallbackTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereCallbackUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereMerchantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereRequestStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereRequestUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereSystemSendId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\Third\ThirdPartyLog whereUserId($value)
 * @mixin \Eloquent
 */
class ThirdPartyLog extends Model
{
    use StaticModel;

    // 验证成功
    const REQUEST_STATUS_SUCCESS = 1;
    // 验证失败
    const REQUEST_STATUS_VERIFY_FAIL = 2;
    // 请求失败
    const REQUEST_STATUS_FAIL = -1;
    // 未知
    const REQUEST_STATUS_NOT = 0;

    const REQUEST_STATUS = [
        '' => 'all',
        self::REQUEST_STATUS_SUCCESS => '成功',
        self::REQUEST_STATUS_VERIFY_FAIL => '认证失败',
        self::REQUEST_STATUS_FAIL => '失败',
        self::REQUEST_STATUS_NOT => '未知',
    ];

    const NAME_FACE = 'face'; // 活体检测

    const NAME_FACE_BY_TEST = 'face_by_test'; // 人脸识别（新加坡） *未用

    const NAME_RISK_OPERATOR = 'risk_operator'; // 风控运营商报告发送 *未用

    const NAME_CONCRACT_SIGN = 'contract_sign'; // 合同签约 *未用

    const NAME_BANK_CARD_CHACK = 'bankcard_check'; // 银行卡验证

    const NAME_PAN_CARD_VERFIY = 'pan_card_verfiy'; // PAN CARD ID验证

    const NAME_AADHAAR_CARD_VERFIY = 'aadhaar_card_verfiy'; // AADHAAR CARD ID验证

    const NAME_DL_VERFIY = 'dl_verfiy'; // 驾驶证ID *未用

    const NAME_VOTER_ID_VERFIY = 'voter_id_verfiy'; // voter_id 选民证ID验证

    const NAME_PASSPORT_VERFIY = 'passport_verfiy'; // passport 护照ID验证

    const NAME_ESIGN = 'esign'; // esign 电子签约

    const NAME_SIGN = 'sign'; // 电子签约

    const NAME_PAN_CARD_OCR = 'pan_card_ocr'; // PAN CARD ocr

    const NAME_AADHAAR_CARD_FRONT_OCR = 'aadhaar_card_front_ocr'; // AADHAAR CARD 正面 ocr

    const NAME_AADHAAR_CARD_BACK_OCR = 'aadhaar_card_back_ocr'; // AADHAAR CARD 反面 ocr

    const NAME_VOTER_ID_OCR = 'voter_id_ocr'; // voter_id 选民证ocr验证

    const NAME_PASSPORT_OCR = 'passport_ocr'; // passport 护照ocr验证

    const NAME_AADHAAR_CARD_KYC = 'aadhaar_card_kyc'; // Aadhaar card KYC验证

    const NAME_FACE_COMPARISON = 'face_comparison'; // 人脸比对

    const NAME = [
        '' => 'all',
        self::NAME_FACE => '活体检测',
        self::NAME_FACE_BY_TEST => '人脸识别（新加坡）*',
        self::NAME_RISK_OPERATOR => '风控运营商报告发送*',
        self::NAME_CONCRACT_SIGN => '合同签约*',
        self::NAME_BANK_CARD_CHACK => '银行卡验证',
        self::NAME_PAN_CARD_VERFIY => '税卡ID(panCard)验证',
        self::NAME_DL_VERFIY => '驾驶证ID验证*',
        self::NAME_VOTER_ID_VERFIY => '选民证ID验证',
        self::NAME_PASSPORT_VERFIY => '护照ID验证',
        self::NAME_ESIGN => 'esign 电子签约',
        self::NAME_PAN_CARD_OCR => '税卡(panCard) OCR',
        self::NAME_VOTER_ID_OCR => '选民证 OCR',
        self::NAME_PASSPORT_OCR => '护照 OCR',
        self::NAME_AADHAAR_CARD_KYC => 'Aadhaar card 验证 EKYC',
        self::NAME_FACE_COMPARISON => '人脸比对',
    ];

    # 第三方渠道
    const CHANNEL_SERVICES = 'services';
    const CHANNEL = [
        self::CHANNEL_SERVICES => '印牛服务认证',
    ];

    const NAME_BY_CHANNEL = [
        self::NAME_FACE => self::CHANNEL_SERVICES,
        self::NAME_BANK_CARD_CHACK => self::CHANNEL_SERVICES,
        self::NAME_PAN_CARD_VERFIY => self::CHANNEL_SERVICES,
        self::NAME_VOTER_ID_VERFIY => self::CHANNEL_SERVICES,
        self::NAME_DL_VERFIY => self::CHANNEL_SERVICES,
        self::NAME_PASSPORT_VERFIY => self::CHANNEL_SERVICES,
        self::NAME_SIGN => self::CHANNEL_SERVICES,
    ];

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    /**
     * @var string
     */
    protected $table = 'third_party_log';
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
     * @param $id
     * @param $requestStatus
     * @return bool
     */
    public static function updateRequestStatus($id, $requestStatus)
    {
        $model = ThirdPartyLog::model()->getOne($id);
        if ($model) {
            $model->request_status = $requestStatus;
            return $model->save();
        }
        return false;
    }


    public function textRules()
    {
        return [];
    }

    public function user($class = User::class)
    {
        return $this->hasOne($class, 'id', 'user_id');
    }

    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * 请求开始事件
     *
     * @param $name
     * @param $request
     * @param $requestUrl
     * @param int $reportId
     * @param string $remark
     * @return bool
     */
    public function createByRequest($name, $request = '', $requestUrl = '', $reportId = '', $remark = '', $userId = 0)
    {
        $this->name = $name;
        $this->channel = array_get(self::NAME_BY_CHANNEL, $name, '');
        $this->request = is_array($request) ? json_encode($request, 256) : $request;
        $this->request_url = $requestUrl;
        $this->merchant_id = MerchantHelper::getMerchantId();
        if ($reportId != '') {
            $this->report_id = $reportId;
        }
        if ($remark != '') {
            $this->remark = $remark;
        }
        if (Auth::user()) {
            $this->user_id = Auth::user()->id;
        }
        if ($userId != 0) {
            $this->user_id = $userId;
        }
        return $this->save();
    }

    /**
     * 请求返回事件
     *
     * @param $response
     * @param $requestStatus
     * @param string $remark
     * @return bool
     */
    public function updatedByResponse($requestStatus, $response = '', $reportId = '', $remark = '')
    {
        if($response != ''){
            $this->response = is_array($response) ? json_encode($response, 256) : $response;
        }
        $this->request_status = $requestStatus;
        if ($reportId != '') {
            $this->report_id = $reportId;
        }
        if ($remark != '') {
            $this->remark = $remark;
        }
        $this->response_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 请求回调事件
     *
     * @param $callback
     * @param $callbackUrl
     * @param $callbackStatus
     * @param string $remark
     * @return bool
     */
    public function updatedByCallback($callbackStatus, $callback, $callbackUrl, $remark = '')
    {
        $this->callback = is_array($callback) ? json_encode($callback, 256) : $callback;
        $this->callback_url = $callbackUrl;
        $this->callback_status = $callbackStatus;
        if ($remark != '') {
            $this->remark = $remark;
        }
        $this->callback_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 请求回调事件 根据reportId
     *
     * @param $reportId
     * @param $callbackStatus
     * @param $callback
     * @param $callbackUrl
     * @param string $remark
     * @return bool
     */
    public function updatedByCallbackByReportId($reportId, $callbackStatus, $callback, $callbackUrl, $remark = '')
    {
        $model = static::getOne(['report_id' => $reportId]);
        if (!$model) {
            return false;
        }
        return $model->updatedByCallback($callbackStatus, $callback, $callbackUrl, $remark);
    }

}
