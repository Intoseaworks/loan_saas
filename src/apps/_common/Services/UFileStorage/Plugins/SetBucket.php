<?php

namespace Common\Services\UFileStorage\Plugins;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Plugin\AbstractPlugin;

class SetBucket extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'setBucket';
    }

    /**
     * @param $bucket
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function handle($bucket)
    {
        $this->filesystem->getAdapter()->setBucket($bucket);

        return Storage::disk($this->filesystem->getAdapter()->symbol);
    }
}
