<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/9
 * Time: 16:42
 */

namespace Common\Utils\Upload;

class ImageHelper
{
    /**
     * 通过图片链接获取图片全链接
     * @param $pic_url
     * @param int $height 0表示不压缩
     * @return string
     */
    static public function getPicUrl($pic_url, $height = 900)
    {
        if (strpos($pic_url, 'http') === 0) {
            return $pic_url;
        }
        return OssHelper::helper()->picTokenUrl($pic_url, $height);
    }

    /**
     * 判断图片url是否存在
     * @param $url
     * @return bool
     */
    public static function file_exists($url)
    {
        $curl = curl_init($url);
        // 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求
        $result = curl_exec($curl);
        // 如果请求没有发送失败
        if (!$result) {
            return false;
        }
        // 再检查http响应码是否为200
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (!in_array($code, [200])) {
            return false;
        }
        return true;
    }
}
