<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019-03-07
 * Time: 15:05
 */

namespace Common\Utils\Hyperface\Api;

use Common\Models\Third\ThirdPartyLog;
use Common\Services\BaseService;
use CURLFile;

class HyperfaceRequest extends BaseService
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
     * @return HyperfaceRequest
     */
    public function readFacePan($file, $fileFace, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERFACE_FACE_PAN, [$file, $fileFace], Config::getFaceUrl(), '', '', $userId);
        return $this->request($file, $fileFace, Config::getFaceUrl(), $thirdPartLogModel);
    }

    /**
     * @param $file
     * @param $url
     * @param ThirdPartyLog $thirdPartLogModel
     * @param null $respoHandle
     * @param string $inputType
     * @return HyperfaceRequest
     */
    protected function request($file, $fileFace, $url, ThirdPartyLog $thirdPartLogModel, $respoHandle = null, $inputType = 'image')
    {
        $appId = Config::getAppId();
        $appKey = Config::getAppKey();
        $header = [
            "appid: {$appId}",
            "appkey: {$appKey}",
            'Content-Type:multipart/form-data',
        ];

        // url
        $postfields = [
            'type' => 'selfie',
            'image1' => new CURLFile($file),
            'image2' => new CURLFile($fileFace)
        ];

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
     * @return HyperfaceRequest
     */
    public function readFaceAddress($file, $fileFace, $userId)
    {
        $thirdPartLogModel = new ThirdPartyLog();
        $thirdPartLogModel->createByRequest(ThirdPartyLog::NAME_HYPERFACE_FACE_ADDRESS, [$file, $fileFace], Config::getFaceUrl(), '', '', $userId);
        return $this->request($file, $fileFace, Config::getFaceUrl(), $thirdPartLogModel);
    }
}
