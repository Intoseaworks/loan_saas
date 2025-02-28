<?php
/**
 * Created by jacob.
 * User: jacob
 * Date: 16/5/20
 * Time: 下午8:31
 */

namespace Common\Services\AliOssStorage\Plugins;

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
