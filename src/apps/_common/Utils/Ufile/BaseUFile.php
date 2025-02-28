<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/19
 * Time: 16:12
 * author: soliang
 */

namespace Common\Utils\UFile;

use Common\Utils\UFile\Lib\ActionType;
use Common\Utils\UFile\Lib\Auth;
use Common\Utils\UFile\Lib\Client;
use Common\Utils\UFile\Lib\MimeType;
use Common\Utils\UFile\Lib\RequestEntity;
use Common\Utils\UFile\Lib\Util;

class BaseUFile
{
    public $publicKey;
    public $privateKey;
    public $proxySuffix;
    public $bucket;

    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var RequestEntity
     */
    private $requestEntity;

    private $urlPrefix = 'http://';

    public function __construct($publicKey, $privateKey, $proxySuffix, $bucket, bool $https = false)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->proxySuffix = $proxySuffix;
        $this->bucket = $bucket;

        if ($https) {
            $this->urlPrefix = 'https://';
        }

        $this->checkConfig();

        $this->auth = new Auth($this->publicKey, $this->privateKey);
    }

    private function setRequestEntity($actionType, $key, $body = null, $header = [], $query = [])
    {
        $this->requestEntity = new RequestEntity(
            $actionType,
            $this->getProxyDomain(),
            $body,
            $this->bucket,
            $key,
            $query
        );
        $this->requestEntity->appendHeader($header);
    }

    private function checkConfig()
    {
        if (!$this->proxySuffix) {
            throw new UFileException('no proxy suffix found in config');
        } else if (!$this->publicKey || strstr($this->publicKey, " ") != FALSE) {
            throw new UFileException('invalid public key found in config');
        } else if (!$this->privateKey || strstr($this->privateKey, " ") != FALSE) {
            throw new UFileException('invalid private key found in config');
        }
    }

    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    public function getProxyDomain()
    {
        return $this->urlPrefix . $this->bucket . $this->proxySuffix;
    }

    /**
     * 构造公有Bucket访问链接
     * @param $key
     * @return string
     */
    public function generatePublicUrl($key)
    {
        return str_finish($this->getProxyDomain(), '/') . rawurlencode($key);
    }

    /**
     * 构造私有Bucket访问链接
     * @param $key
     * @param int $expires 过期时间，单位秒
     * @return string
     */
    public function generatePrivateUrl($key, $expires = null)
    {
        $actionType = ActionType::GETFILE;

        $params = [];
        if ($expires) {
            $expires = time() + $expires;
            $params['Expires'] = $expires;
            $this->setRequestEntity($actionType, $key, null, [
                'Expires' => $expires,
            ]);
        } else {
            $this->setRequestEntity($actionType, $key, null);
        }
        $publicUrl = $this->generatePublicUrl($key);

        $sign = $this->auth->signRequest($this->requestEntity, Auth::QUERY_STRING_CHECK);

        $params = array_merge($params, [
            'UCloudPublicKey' => $this->publicKey,
            'Signature' => $sign,
        ]);

        return $publicUrl . '?' . http_build_query($params);
    }

    /**
     * 普通上传
     * @param $key
     * @param $localFilePath
     * @return bool
     * @throws UFileException
     */
    public function putFile($key, $localFilePath)
    {
        $content = Util::getFileContent($localFilePath);

        return $this->put($key, $content, MimeType::getFileMimeType($localFilePath));
    }

    /**
     * 下载文件到本地
     * @param $key
     * @param $localPath
     * @return bool
     */
    public function getFile($key, $localPath)
    {
        $dir = dirname($localPath);
        if (!is_dir($dir)) {
            Util::mkdirs($dir);
        }

        $url = $this->generatePrivateUrl($key);

        try {
            $file = fopen($url, 'rb');
            if ($file) {
                $newf = fopen($localPath, 'wb');
                if ($newf) {
                    while (!feof($file)) {
                        fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                    }
                }
            }
            if ($file) {
                fclose($file);
            }
            if (isset($newf) && $newf) {
                fclose($newf);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 直接上传文件内容
     * @param $key
     * @param $content
     * @param $mime
     * @return bool
     * @throws UFileException
     */
    public function put($key, $content, $mime)
    {
        $actionType = ActionType::PUTFILE;

        $this->setRequestEntity($actionType, $key, $content, [
            'Expect' => '',
            'Content-Type' => $mime,
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response->isSuccess();
    }

//    public function postFile($key, $localFilePath)
//    {
//        $actionType = ActionType::POSTFILE;
//
//        $content = Util::getFileContent($localFilePath);
//
//        $this->setRequestEntity($actionType, $key, $content, [
//            'Expect' => '',
//        ]);
//
//        dd($this->requestEntity);
//    }

    /**
     * 分片上传：初始化
     * @param $key
     *
     * @return \Tests\Ufile\Lib\Response
     * @throws UFileException
     */
    public function minitUploadInit($key)
    {
        $actionType = ActionType::MINIT;

        $this->setRequestEntity($actionType, $key, null, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], [
            'uploads' => '',
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response;
    }

    /**
     * 分片上传：上传
     * 根据初始化接口返回的分片大小(4M‬)进行分片上传
     * @param $key
     * @param $file
     * @param $uploadId
     * @param $blkSize
     * @param int $partNumber
     *
     * @return array|bool
     */
    public function minitUploadExec($key, $file, $uploadId, $blkSize, $partNumber = 0)
    {
        $actionType = ActionType::MUPLOAD;
        $mimetype = MimeType::getFileMimeType($file);

        // 分片上传
        $res = Util::fileSyncopation($file, $blkSize, function ($partNumber, $content) use ($uploadId, $key, $actionType, $mimetype) {

            $this->setRequestEntity($actionType, $key, $content, [
                'Content-Type' => $mimetype,
                'Expect' => '',
            ], [
                'uploadId' => $uploadId,
                'partNumber' => $partNumber,
            ]);

            $response = Client::instance($this->auth, $this->requestEntity)->execSign();

            if ($response->isFailed()) {
                return false;
            }
            $part = $response->getData('PartNumber');
            $etag = $response->getData('ETag');
            if ($part != $partNumber) {
                return false;
            }

            return $etag;
        });

        return $res;
    }

    /**
     * 分片上传：完成
     * @param $key
     * @param $uploadId
     * @param array $etagList
     * @param string $newKey
     *
     * @return \Tests\Ufile\Lib\Response
     * @throws UFileException
     */
    public function minitUploadFinish($key, $uploadId, array $etagList, $newKey = '')
    {
        $actionType = ActionType::MFINISH;

        $body = @implode(',', $etagList);

        $this->setRequestEntity($actionType, $key, $body, [
            'Content-Type' => 'text/plain',
        ], [
            'uploadId' => $uploadId,
            'newKey' => $newKey,
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response;
    }

    /**
     * 分片上传：取消
     * @param $uploadId
     * @param $key
     *
     * @return \Tests\Ufile\Lib\Response
     * @throws UFileException
     */
    public function minitUploadCancel($uploadId, $key)
    {
        $actionType = ActionType::MCANCEL;

        $this->setRequestEntity($actionType, $key, null, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], [
            'uploadId' => $uploadId,
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response;
    }

    /**
     * 判断文件是否存在
     * 该接口不是上传接口
     * 如果秒传返回非200错误码,意味着该文件在服务器不存在
     * @param $key
     * @param $file
     *
     * @return \Tests\Ufile\Lib\Response
     * @throws UFileException
     */
    public function uploadHit($key, $file)
    {
        $actionType = ActionType::UPLOADHIT;

        // todo 未完成
        $fileHash = Util::fileHash($file);
        $fileSize = Util::fileSize($file);

        $this->setRequestEntity($actionType, $key, null, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], [
            'Hash' => $fileHash,
            'FileName' => $key,
            'FileSize' => $fileSize,
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response;
    }

    /**
     * 删除文件
     * @param $key
     * @return bool
     * @throws UFileException
     */
    public function delete($key)
    {
        $actionType = ActionType::DELETE;

        $this->setRequestEntity($actionType, $key, null, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response->isSuccess();
    }

    /**
     * 追加上传
     * todo 未测试
     * @param $key
     * @param $file
     * @param $position 当前append的文件已有的大小, 新建填0
     * @return bool
     * @throws UFileException
     */
    public function appendFile($key, $file, $position)
    {
        $actionType = ActionType::APPENDFILE;

        $content = Util::getFileContent($file);

        $key = $key . "?append&position=" . $position;

        $this->setRequestEntity($actionType, $key, $content, [
            'Expect' => '',
            'Content-Type' => MimeType::getFileMimeType($file),
        ]);

        $response = Client::instance($this->auth, $this->requestEntity)->execSign();

        return $response->isSuccess();
    }
}
