<?php

namespace Common\Services\UFileStorage\Plugins;

use Common\Services\UFileStorage\UFileAdapter;
use League\Flysystem\Plugin\AbstractPlugin;

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
     * @param $key
     * @param $localFile
     * @return mixed
     */
    public function handle($key, $localFile)
    {
        /** @var UFileAdapter $adapter */
        $adapter = $this->filesystem->getAdapter();
        return $adapter->getClient()->getFile($key, $localFile);
    }
}
