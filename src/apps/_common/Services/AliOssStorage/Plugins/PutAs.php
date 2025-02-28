<?php
/**
 * Created by jacob.
 * User: jacob
 * Date: 16/5/20
 * Time: 下午8:31
 */

namespace Common\Services\AliOssStorage\Plugins;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Plugin\AbstractPlugin;

class PutAs extends AbstractPlugin
{

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'putAs';
    }

    /**
     * @param $path
     * @param \Illuminate\Http\File|\Illuminate\Http\UploadedFile $file
     * @param bool $base64
     * @param array $options
     * @return bool|string
     */
    public function handle($path, $file, $base64 = false, $options = [])
    {
        if (!$base64) {
            if (is_string($file)) {
                $file = new File($file);
            }

            $file = fopen($file->getRealPath(), 'r');
        }

        $filesystemAdapter = Storage::disk($this->filesystem->getAdapter()->symbol);
        $result = $filesystemAdapter->put(
            $path = trim($path, '/'), $file, $options
        );

        if (is_resource($file)) {
            fclose($file);
        }

        return $result ? $path : false;
    }
}
