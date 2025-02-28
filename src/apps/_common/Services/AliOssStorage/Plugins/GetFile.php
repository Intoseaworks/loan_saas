<?php
/**
 * Created by jacob.
 * User: jacob
 * Date: 16/5/20
 * Time: 下午8:31
 */

namespace Common\Services\AliOssStorage\Plugins;

use Common\Services\AliOssStorage\AliOssAdapter;
use League\Flysystem\Plugin\AbstractPlugin;
use OSS\OssClient;

class GetFile extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'getFile';
    }

    /**
     * @param $path
     * @param int $size
     * @param bool $useCDN
     * @return mixed
     */
    public function handle($object, $localFile)
    {

        $options = [
            OssClient::OSS_FILE_DOWNLOAD => $localFile,
        ];
        /** @var AliOssAdapter $adapter */
        $adapter = $this->filesystem->getAdapter();
        return $adapter->getClient()->getObject($adapter->getBucket(), $object, $options);
    }
}
