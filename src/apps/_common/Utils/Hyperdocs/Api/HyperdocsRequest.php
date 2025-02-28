<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-03-07
 * Time: 15:05
 */

namespace Common\Utils\Hyperdocs\Api;

use Common\Models\Third\ThirdPartyLog;
use Common\Services\BaseService;
use Common\Utils\Curl;
use CURLFile;

class HyperdocsRequest extends BaseService
{
    /**
     * @var string
     */
    const STATUS_CODE_SUCCESS = '200';

    /**
     * 未检测到
     *
     * @var string
     */
    const STATUS_NOT_DETECTED = '422';

    /**
     * @var string
     */
    const STATUS_SUCCESS = 'success';

    const TYPE_PASSPORT_FRONT = 'passport_front';//护照正面
    const TYPE_PASSPORT_BACK = 'passport_back';//护照反面
    const TYPE_AADHAAR_FRONT = 'aadhaar_front_bottom';//aadhaar正面
    const TYPE_AADHAAR_BACK = 'aadhaar_back';//aadhaar反面
    const TYPE_VOTER_ID_FRONT = 'voterid_front';//选民证正面
    const TYPE_VOTER_ID_BACK = 'voterid_back';//选民证反面

    /**
     * @param $file
     * @param $userId
     * @return HyperdocsRequest
     */
    public function readKYC($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_KYC, $file, Config::getReadKYCUrl(), '', '', $userId);
        return $this->request($file, Config::getReadKYCUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $url
     * @param ThirdPartyLog $thirdPartLogModel
     * @param null $respoHandle
     * @param string $inputType
     * @return HyperdocsRequest
     */
    protected function request($file, $url, ThirdPartyLog $thirdPartLogModel, $respoHandle = null, $inputType = 'image')
    {
        $appId = Config::getAppId();
        $appKey = Config::getAppKey();
        $header = [
            "appid: {$appId}",
            "appkey: {$appKey}",
            'Content-Type:multipart/form-data',
        ];

        // url
        if (preg_match('#http(s?)\:#', $file)) {
            $postfields = ['url' => $file];
        } else {
            if ($inputType == 'image') {
                $postfields = ['image' => new CURLFile($file)];
            } elseif ($inputType == 'pdf') {
                $postfields = ['pdf' => new CURLFile($file)];
            } else {
                return $this->outputError("Invalid inputType. It can be either an 'image' or 'pdf'");
            }
        }

        $result = json_decode(Curl::post($postfields, $url, $header), true);
        $response = $result;
        // 处理数据,响应数据如果存在base64的数据把他过滤掉
        if ($respoHandle && ($respoHandle instanceof \Closure)) {
            $response = $respoHandle($result);
        }
        // 响应结果保存到日志
        $thirdPartLogModel->updatedByResponse(
            array_get($result, 'statusCode') == static::STATUS_CODE_SUCCESS ? ThirdPartyLog::REQUEST_STATUS_SUCCESS : ThirdPartyLog::REQUEST_STATUS_FAIL,
            $response
        );
        if ($result) {
            $result['third_part_log_id'] = $thirdPartLogModel->id;
            return $this->outputSuccess('Success', $result);
        }


        return $this->outputError('Error, please try again');
    }

    /**
     * @param $file
     * @param $userId
     * @return HyperdocsRequest
     */
    public function readAadhaar($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_AADHAAR, $file, Config::getReadAadhaarUrl(), '', '', $userId);
        return $this->request($file, Config::getReadAadhaarUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $userId
     * @return HyperdocsRequest
     */
    public function readPassport($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_PASSPROT, $file, Config::getReadPassportUrl(), '', '', $userId);
        return $this->request($file, Config::getReadPassportUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $userId
     * @return HyperdocsRequest
     */
    public function readVoterId($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_VOTER_ID, $file, Config::getReadVoterIdUrl(), '', '', $userId);
        return $this->request($file, Config::getReadVoterIdUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $userId
     * @return HyperdocsRequest
     */
    public function readPan($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_PAN, $file, Config::getPanCardUrl(), '', '', $userId);
        return $this->request($file, Config::getPanCardUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $userId
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function readDriving($file, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERDOCS_DRIVING, $file, Config::getReadDrivingUrl(), '', '', $userId);
        return $this->request($file, Config::getReadDrivingUrl(), $thirdPartLogModel);
    }
}
