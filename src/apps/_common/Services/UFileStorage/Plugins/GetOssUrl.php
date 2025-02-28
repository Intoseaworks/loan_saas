<?php

namespace Common\Services\UFileStorage\Plugins;

use Common\Services\UFileStorage\UFileAdapter;
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
     * @return mixed
     */
    public function handle($path)
    {
        /** @var UFileAdapter $adapter */
        $adapter = $this->filesystem->getAdapter();

        return $adapter->getPrivateUrl($path);
    }
}
