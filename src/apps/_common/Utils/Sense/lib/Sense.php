<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/25
 * Time: 11:49
 */

namespace Common\Utils\Sense\lib;

use Common\Models\Third\ThirdPartyLog;
use Common\Utils\Curl;
use Yunhan\Utils\Env;

class Sense
{
    # 对两张通过接口上传的人脸照片进行比对，来判断是否为同一个人
    const IMAGE_VERIFY = 'IMAGE_VERIFY';
    # 静默活体检测以及与自拍照人脸比对
    const SILENT_IMAGE_VERIFY = 'SILENT_IMAGE_VERIFY';
    # 对通过接口上传的人脸照片和活体数据进行比对，来判断是否为同一个人
    const LIVENESS_IMAGE_VERIFY = 'LIVENESS_IMAGE_VERIFY';
    # 活体人脸检测
    const SILENT_DETECTION = 'SILENT_DETECTION';

    private $_url = '';

    private $_header = [];

    public function __construct()
    {
        $this->_url = config('cashnow.sense.url');
        $this->_header = (new SenseAuthorization)->getSenceAuth();
    }

    public function sendImg($type, $field_first, $field_sencond = '')
    {
        if (!$type) {
            return false;
        }
        if ($type !== self::SILENT_DETECTION) {
            $field_first = $this->base64_img($field_first);
            $field_sencond = $this->base64_img($field_sencond);
        }

        switch ($type) {
            /** 对两张通过接口上传的人脸照片进行比对，来判断是否为同一个人 */
            case self::IMAGE_VERIFY:
                $data = $this->img_verify_stateless($field_first, $field_sencond);
                unlink($field_first);
                unlink($field_sencond);
                return json_decode($data, true);
                break;
            /** 静默活体检测以及与自拍照人脸比对 */
            case self::SILENT_IMAGE_VERIFY:
                $data = $this->silent_img_verify_stateless($field_first, $field_sencond);
                unlink($field_first);
                unlink($field_sencond);
                return json_decode($data, true);
                break;
            /** 对通过接口上传的人脸照片和活体数据进行比对，来判断是否为同一个人 */
            case self::LIVENESS_IMAGE_VERIFY:
                $data = $this->liveness_img_verify_stateless($field_first, $field_sencond);
                unlink($field_first);
                unlink($field_sencond);
                return json_decode($data, true);
                break;
            case self::SILENT_DETECTION:
                $data = $this->silent_detection($field_first);
                unlink($field_first);
                return json_decode($data, true);
                break;
            default :
                unlink($field_first);
                unlink($field_sencond);
                return false;
        }
    }

    public function base64_img($base64)
    {
        /*$path = __FILE__;
        $path = substr($path, 0, strrpos($path, '/'));*/
        $path = '/data/temp/';
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $type = 'jpg';//$result[2];

            $new_file = $path . md5($base64) . ".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64)))) {
                return $new_file;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 对两张通过接口上传的人脸照片进行比对，来判断是否为同一个人
     */
    private function img_verify_stateless($livenessPath, $photoPath)
    {
        $url = $this->_url . '/identity/image_verification/stateless'; // url
        /*$livenessPath = 'C:/api_doc/Users/face1.jpg';//活体数据路径
        $photoPath = 'C:/api_doc/Users/face2.jpg';//照片路径*/
        $livenessContent = new \CURLFile($livenessPath);
        $photoContent = new \CURLFile($photoPath);
        $post_data = array('first_image_file' => $livenessContent, 'second_image_file' => $photoContent);
        $output = Curl::post($post_data, $url, $this->_header);
        return $output;
    }

    /**
     * 静默活体检测以及与自拍照人脸比对
     */
    private function silent_img_verify_stateless($imageFilePath, $videoFilePath)
    {
        $url = $this->_url . '/identity/silent_image_verification/stateless';
        /*$imageFilePath = 'C:/api_doc/Users/face.jpg';  //图片路径
        $videoFilePath = 'C:/Users/video.mp4';  //视频路径*/
        $imageFileContent = new \CURLFile($imageFilePath);
        $videoFileContent = new \CURLFile($videoFilePath);
        $post_data = array('image_file' => $imageFileContent, 'video_file' => $videoFileContent);
        $output = Curl::post($post_data, $url, $this->_header);
        return $output;
    }

    /**
     * 对通过接口上传的人脸照片和活体数据进行比对，来判断是否为同一个人
     */
    private function liveness_img_verify_stateless($livenessPath, $photoPath)
    {
        $url = $this->_url . '/identity/liveness_image_verification/stateless'; // url
        /*$livenessPath = 'C:/Users/liveness_data';//活体数据路径
        $photoPath = 'C:/api_doc/Users/face.jpg';//本地图片路径*/
        $livenessContent = new \CURLFile($livenessPath);
        $photoContent = new \CURLFile($photoPath);
        $post_data = array('liveness_file' => $livenessContent, 'image_file' => $photoContent);
        $output = Curl::post($post_data, $url, $this->_header);
        return $output;
    }

    /**
     * 静默活体检测以及与身份证照人脸比对
     */
    private function silent_detection($videoPath)
    {
        /** 正式服人脸通过测试服做验证 */
        if (!Env::isDevOrTest()) {
            $testCheck = $this->testCheck($videoPath);
            if ($testCheck) {
                return json_encode($testCheck, 256);
            }
        }

        $url = $this->_url . '/liveness/silent_detection/stateless';

        $filePath = $videoPath;  //视频路径
        $fileContent = new \CURLFile($filePath);
        $post_data = array('video_file' => $fileContent, 'return_image' => true);

        $thirdPartyLog = new ThirdPartyLog();
        $thirdPartyLog->createByRequest(ThirdPartyLog::NAME_FACE, '', $url);

        $output = Curl::post($post_data, $url, $this->_header, 90);
        $data = json_decode($output, true);
        $dataResponse = $data;
        if (!array_key_exists('passed', $dataResponse)) {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL, $dataResponse);
        } else {
            $dataResponse['base64_image'] = array_get($dataResponse, 'base64_image') ? true : false;
            if ($passed = array_get($dataResponse, 'data.passed')) {
                $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $dataResponse);
            } else {
                $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL, $dataResponse);
            }
        }
        return json_encode($data, 256);
    }

    private function testCheck($videoPath)
    {
        $url = 'http://api.cash.jiumiaodai.com/authentication/faces-check';

        $filePath = $videoPath;  //视频路径
        $fileContent = new \CURLFile($filePath);
        $post_data = array('video' => $fileContent, 'ticket' => 'c8c24f2f33554f8ea43e4db657ac3d80');

        $thirdPartyLog = new ThirdPartyLog();
        $thirdPartyLog->createByRequest(ThirdPartyLog::NAME_FACE_BY_TEST, '', $url);

        $output = Curl::post($post_data, $url, $this->_header, 90);
        $data = json_decode($output, true);
        $dataResponse = $data;
        if (!isset($dataResponse['code']) || $dataResponse['code'] == 13000) {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_FAIL, $dataResponse);
            return false;
        }
        if ($passed = array_get($dataResponse, 'data.passed')) {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_SUCCESS, $dataResponse);
        } else {
            $thirdPartyLog->updatedByResponse(ThirdPartyLog::REQUEST_STATUS_VERIFY_FAIL, $dataResponse);
        }
        return $data['data'];
    }
}
