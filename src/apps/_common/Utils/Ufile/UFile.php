<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/19
 * Time: 15:26
 * author: soliang
 */

namespace Common\Utils\UFile;

use Common\Utils\UFile\Lib\Util;

class UFile
{
    private $base;

    public function __construct($proxySuffix, $bucket, $publicKey, $privateKey, $https = false)
    {
        $this->base = new BaseUFile($publicKey, $privateKey, $proxySuffix, $bucket, $https);
    }

    public function setBucket($bucket)
    {
        $this->base->setBucket($bucket);
    }

    /**
     * 构造公有Bucket访问链接
     * @param $key
     * @return string
     */
    public function generatePublicUrl($key)
    {
        return $this->base->generatePublicUrl($key);
    }

    /**
     * 构造私有Bucket访问链接
     * @param $key
     * @param int $expires 过期时间，单位秒
     * @return string
     */
    public function generatePrivateUrl($key, $expires = 0)
    {
        return $this->base->generatePrivateUrl($key, $expires);
    }

    /**
     * 下载文件到本地
     * @param $key
     * @param $localPath
     * @return bool
     */
    public function getFile($key, $localPath)
    {
        return $this->base->getFile($key, $localPath);
    }

    /**
     * 获取文件内容
     * @param $key
     * @return false|string
     */
    public function getFileContent($key)
    {
        $url = $this->generatePrivateUrl($key);

        $content = file_get_contents($url);

        return $content;
    }

    /**
     * 判断文件是否存在
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        $res = Util::getHeaderByUrl($this->generatePrivateUrl($key), 0);

        return $res && (bool)strpos($res, '200');
    }

    /**
     * 获取文件大小
     * @param $key
     * @return bool|mixed|string
     */
    public function size($key)
    {
        $size = Util::getHeaderByUrl($this->generatePrivateUrl($key), 'Content-Length');

        return $size ? (int)$size : $size;
    }

    /**
     * 获取文件 mime
     * @param $key
     * @return bool|mixed|string
     */
    public function getMime($key)
    {
        $size = Util::getHeaderByUrl($this->generatePrivateUrl($key), 'Content-Type');

        return $size;
    }

    public function getMeta($key)
    {
        return Util::urlParseHeaders($this->generatePrivateUrl($key));
    }

    /**
     * 上传文件
     * 根据文件大小自动选择上传方式: 10M下用 putFile，10M上用分片 minitUpload
     * @param $key
     * @param $filePath
     *
     * @return bool
     * @throws UFileException
     */
    public function uploadFile($key, $filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        $fileSize = Util::getSizeUnit($filePath);

        if (!$fileSize || $fileSize > 10) {
            $res = $this->minitUpload($key, $filePath);
        } else {
            $res = $this->putFile($key, $filePath);
        }

        return $res;
    }

    /**
     * 普通上传
     * @param $key
     * @param $filePath
     * @return bool
     * @throws UFileException
     */
    public function putFile($key, $filePath)
    {
        return $this->base->putFile($key, $filePath);
    }

    /**
     * 直接上传文件内容
     * @param $key
     * @param $content
     * @param string $mime
     * @return bool
     * @throws UFileException
     */
    public function put($key, $content, $mime = 'application/octet-stream')
    {
        return $this->base->put($key, $content, $mime);
    }

    /**
     * 分片上传
     * @param $key
     * @param $filePath
     * @return bool
     * @throws UFileException
     */
    public function minitUpload($key, $filePath)
    {
        $initRes = $this->base->minitUploadInit($key);

        // id
        $uploadId = $initRes->getData('UploadId');
        // 分块大小,默认返回的4M
        $blkSize = $initRes->getData('BlkSize');
        if (!$uploadId || !$blkSize) {
            return false;
        }

        $uplRes = $this->base->minitUploadExec($key, $filePath, $uploadId, $blkSize);

        if ($uplRes === false) {
            $this->base->minitUploadCancel($uploadId, $key);
            return false;
        }

        $finishRes = $this->base->minitUploadFinish($key, $uploadId, $uplRes);

        if ($finishRes->isFailed()) {
            $this->base->minitUploadCancel($uploadId, $key);
            return false;
        }

        return true;
    }

    /**
     * 删除文件
     * @param $key
     * @return bool
     * @throws UFileException
     */
    public function delete($key)
    {
        return $this->base->delete($key);
    }
}
