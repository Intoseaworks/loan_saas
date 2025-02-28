<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 10:20
 */

namespace Common\Utils\Upload;

use Common\Utils\Email\EmailHelper;
use Common\Utils\Helper;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use OSS\Core\OssUtil;

class OssHelper
{
    use  Helper;

    /**
     * 通过文件上传
     *
     * @param string $ossFile 自定义oss上对应路径加文件名
     * @param \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string $localFile 源文件
     * @return bool|string
     */
    public function uploadFile($ossFile, $localFile)
    {
        // oss上传
        if ($this->hasOss()) {
            if (!OssUtil::validateObject($ossFile)) {
                $this->addError('路径不能以/开头');
                return false;
            }
            //判断base64，并转换
            $base64 = false;
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $localFile, $result)) {
                if (strstr($localFile, ",")) {
                    $file = explode(',', $localFile);
                    $file = $file[1];
                }
                $localFile = base64_decode($file);
                $base64 = true;
            } else {
                if (!file_exists($localFile)) {
                    $this->addError('文件不存在');
                    return false;
                }
            }

            if ($this->isInternal()) {
                $storage = Storage::disk('inner-oss');
            } else {
                $storage = Storage::disk('oss');
            }
            $result = $storage->putAs($ossFile, $localFile, $base64);

        } else {
            // 本地上传
            $pathInfo = pathinfo($ossFile);
            if (is_string($localFile)) {
                $localFile = new File($localFile);
            }
            $result = Storage::disk('local')->putFileAs($pathInfo['dirname'], $localFile, $pathInfo['basename']);
        }

        if (!$result) {
            $this->errorHandle('文件上传文件出错');
            return false;
        }
        return $result;

    }

    /**
     * 获取图片临时链接,$heigth,$userCDN对本地上传无效
     *
     * @param $object
     * @param int $height
     * @param bool $useCDN
     * @return string
     */
    public function picTokenUrl($object, $height = 400, $useCDN = false)
    {
        if ($this->hasOss()) {
            if (!OssUtil::validateObject($object)) {
                return '';
            }
            return Storage::disk('oss')->ossUrl($object, $height, $useCDN);
        }

        return Storage::disk('local')->url($object);
    }

    /**
     * 获取pdf临时链接
     *
     * @param $object
     * @return string
     */
    public function pdfTokenUrl($object)
    {
        if ($this->hasOss()) {
            if (!OssUtil::validateObject($object)) {
                return '';
            }
            return Storage::disk('oss')->ossUrl($object);
        }

        return Storage::disk('local')->url($object);
    }

    /**
     * 文件下载
     *
     * @param $object
     * @param $localFile
     * @param int $height
     * @return bool
     */
    public function getFile($object, $localFile)
    {
        if ($this->hasOss()) {
            if (!OssUtil::validateObject($object)) {
                $this->addError('路径不能以/开头');
                return false;
            }
            $result = Storage::disk('oss')->getFile($object, $localFile);
        } else {
            $result = Storage::disk('local')->copy($object, $localFile);
        }

        if ($result === false) {
            $this->errorHandle('文件下载失败');
            return false;
        }
        return true;
    }

    /**
     * 拷贝object
     *
     * @param string $fromObject oss上原对应路径加文件名
     * @param string $toObject oss上新对应路径加文件名
     * @return bool
     */
    public function copyObject($fromObject, $toObject)
    {
        $result = Storage::disk($this->getDriver())->copy($fromObject, $toObject);
        if (!$result) {
            $this->errorHandle('拷贝文件失败');
            return false;
        }

        return true;
    }

    /**
     * 删除object
     *
     * @param array|string $object
     * @return bool
     */
    public function deleteObject($object)
    {

        $result = Storage::disk($this->getDriver())->delete($object);
        if (!$result) {
            $this->errorHandle('删除文件失败', false);
            return false;
        }

        return true;
    }

    /**
     * @param $msg
     * @param bool $sendMail
     */
    protected function errorHandle($msg, $sendMail = true)
    {
        $msg = $this->getDriver() . '-' . $msg;
        $this->addError($msg);
        if ($sendMail) {
            EmailHelper::send($msg, $msg);
        }
    }

    /**
     * @return string
     */
    protected function getDriver()
    {
        return $this->hasOss() ? 'oss' : 'local';
    }

    /**
     * 是否阿里内网
     *
     * @return mixed
     */
    protected function isInternal()
    {
        return config('config.app_upload_oss_internal');
    }

    /**
     * 是否使用Oss
     *
     * @return mixed
     */
    protected function hasOss()
    {
        return config('config.app_upload_oss');
    }

    /**
     * oss图片缓存本地
     *
     * @param $ossPath
     * @return bool|string
     */
    public static function ossToLocal($ossPath)
    {
        $extension = explode('.', $ossPath);
        if (!isset($extension[1])) {
            return false;
        }
        //$localPath = '/data/tmp/pic/';
        $localPath = self::uploadLocalPath('pic');
        if (!is_dir($localPath)) {
            mkdir($localPath, 0777, true);
        }
        chmod($localPath, 0777);
        $tmpPath = $localPath . uniqid('u') . '.' . $extension[1];
        if (!OssHelper::helper()->getFile($ossPath, $tmpPath)) {
            return false;
        }
        //unlink($tmpPath);
        return $tmpPath;
    }

    public static function uploadLocalPath($path)
    {
        return storage_path() . "/upload/{$path}/";
    }
}
