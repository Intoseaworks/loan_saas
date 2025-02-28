<?php
/**
 * Created by jacob.
 * User: jacob
 * Date: 16/5/20
 * Time: 下午8:31
 */

namespace Common\Services\AliOssStorage\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class GetOssUrl extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'ossUrl';
    }

    /**
     * @param $path
     * @param int $size
     * @param bool $useCDN
     * @return mixed
     */
    public function handle($path, $size = 400, $useCDN = false)
    {
        return $this->filesystem->getAdapter()->getOssUrl($path, $size, $useCDN);
    }
}
