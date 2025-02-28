<?php
/**
 * Created by jacob.
 * Date: 2016/5/19 0019
 * Time: 下午 17:07
 */

namespace Common\Services\AliOssStorage;


use OSS\Core\OssUtil;
use OSS\OssClient;

class AliOssAdapter extends \Jacobcyl\AliOSS\AliOssAdapter
{
    /**
     * 标识内网节点还是外网节点
     *
     * @var string
     */
    public $symbol;
    /**
     * bucket的访问权限
     *
     * @var bool
     */
    protected $bucketIsPublic = false;

    /**
     * AliOssAdapter constructor.
     * @param OssClient $client
     * @param $bucket
     * @param $endPoint
     * @param $ssl
     * @param $symbol
     * @param bool $isCname
     * @param bool $debug
     * @param $cdnDomain
     * @param null $prefix
     * @param array $options
     */
    public function __construct(OssClient $client, $bucket, $endPoint, $ssl, $symbol, $isCname = false, $debug = false, $cdnDomain, $prefix = null, array $options = [])
    {
        $this->symbol = $symbol;
        parent::__construct($client, $bucket, $endPoint, $ssl, $isCname, $debug, $cdnDomain, $prefix, $options);
    }

    /**
     * @param $path
     * @param int $height
     * @param bool $useCDN
     * @return mixed|string
     * @throws \OSS\Core\OssException
     */
    public function getOssUrl($path, $height = 400, $useCDN = true)
    {
        // 公共bucket
        if ($this->bucketIsPublic) {
            return ($this->ssl ? 'https://' : 'http://') . ($this->isCname ? ($this->cdnDomain == '' ? $this->endPoint : $this->cdnDomain) : $this->bucket . '.' . $this->endPoint) . '/' . ltrim($path, '/');
        }

        // 私有bucket,创建临时链接
        if (!OssUtil::validateObject($path)) {
            return '';
        }
        $timeout = 3600;
        $options = [];
        if (preg_match('/.*(\.png|\.jpg|\.jpeg|\.gif)$/', $path)) {
            if ($height != 0) {
                $options = [
                    //OssClient::OSS_PROCESS => "image/resize,m_lfit,h_100,w_100",
                    OssClient::OSS_PROCESS => "image/resize,m_lfit,h_{$height}",
                ];
            }
        }
        $this->client->setUseSSL($this->ssl);
        $signedUrl = $this->client->signUrl($this->bucket, $path, $timeout, "GET", $options);
        if ($useCDN && $this->cdnDomain) {
            return str_replace($this->endPoint, $this->cdnDomain, $signedUrl);
        }
        return $signedUrl;
    }

    /**
     * @param $path
     * @return mixed|string
     * @throws \OSS\Core\OssException
     */
    public function getUrl($path)
    {
        return $this->getOssUrl($path, 0, true);
    }

    /**
     * @param $bucket
     */
    public function setBucket($bucket)
    {
        $buckets = config('filesystems.disks.oss.buckets');
        if (is_array($buckets) && isset($buckets[$bucket])) {
            $this->bucket = $bucket;
            $this->bucketIsPublic = $buckets[$bucket];
        }
    }

    /**
     * @param string $path
     * @return array|false
     */
    public function readStream($path)
    {
        $result = $this->readObject($path);
        if (is_resource($result['raw_contents'])) {
            $result['stream'] = $result['raw_contents'];
            // Ensure the EntityBody object destruction doesn't close the stream
            $result['raw_contents']->detachStream();
        } else {
            $result['stream'] = fopen('php://temp', 'r+');
            fwrite($result['stream'], $result['raw_contents']);
        }
        rewind($result['stream']);
        unset($result['raw_contents']);
        return $result;
    }

}
