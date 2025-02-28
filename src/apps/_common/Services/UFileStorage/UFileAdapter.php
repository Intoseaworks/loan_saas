<?php

namespace Common\Services\UFileStorage;

use Common\Utils\UFile\UFile;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Adapter\Polyfill\StreamedCopyTrait;
use League\Flysystem\Adapter\Polyfill\StreamedTrait;
use League\Flysystem\Config;

class UFileAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait, StreamedTrait, StreamedCopyTrait;

    public $symbol;
    protected $client;

    public function __construct($bucket, $publicKey, $privateKey, $suffix = '.ufile.ucloud.cn', $pathPrefix = null, $symbol = 'ufile', $https = false)
    {
        $this->symbol = $symbol;
        $this->client = new UFile($suffix, $bucket, $publicKey, $privateKey, $https);

        $this->setPathPrefix($pathPrefix);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setBucket($bucket)
    {
        $this->client->setBucket($bucket);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        return ['path' => $dirname];
    }

    /**
     * Delete a file.
     * @param string $path
     * @return bool
     * @throws \Common\Utils\UFile\UFileException
     */
    public function delete($path)
    {
        $path = $this->applyPathPrefix($path);
        
        return $this->client->delete($path);
    }

    /**
     * Delete a directory.
     * @param string $dirname
     * @return bool|void
     */
    public function deleteDir($dirname)
    {
        throw new \LogicException(get_class($this) . ' does not support ' . __FUNCTION__);
    }

    /**
     * Get all the meta data of a file or directory.
     * @param string $path
     * @return array|false|void
     */
    public function getMetadata($path)
    {
        return $this->client->getMeta($path);
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        $path = $this->applyPathPrefix($path);
        $mimeType = $this->client->getMime($path);
        return array('mimetype' => $mimeType);
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        $path = $this->applyPathPrefix($path);

        $size = $this->client->size($path);

        return array('size' => $size);
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        $meta = $this->getMetadata($path);
        $timestamp = strtotime($meta['Date']);
        return array('timestamp' => $timestamp);
    }

    public function getUrl($path)
    {
        return $this->client->generatePrivateUrl($path);
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $path = $this->applyPathPrefix($path);

        return $this->client->exists($path);
    }

    /**
     * List contents of a directory.
     * @param string $directory
     * @param bool $recursive
     *
     * @return array|void
     */
    public function listContents($directory = '', $recursive = false)
    {
        throw new \LogicException(get_class($this) . ' does not support ' . __FUNCTION__);
    }

    public function read($path)
    {
        $path = $this->applyPathPrefix($path);
        $data = [];
        $data['contents'] = $this->client->getFileContent($path);
        return $data;
    }

    /**
     * Rename a file.
     * @param string $path
     * @param string $newpath
     *
     * @return bool|void
     * @throws \Exception
     */
    public function rename($path, $newpath)
    {
        if (!$this->copy($path, $newpath)) {
            return false;
        }

        return $this->delete($path);
    }

    /**
     * Update a file.
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false
     * @throws \Common\Utils\UFile\UFileException
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     * @param string $path
     * @param resource $resource
     * @param Config $config
     *
     * @return array|false
     * @throws \Common\Utils\UFile\UFileException
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Copy a file.
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     * @throws \Common\Utils\UFile\UFileException
     */
    public function copy($path, $newpath)
    {
        $response = $this->readStream($path);

        if ($response === false || !is_resource($response['stream'])) {
            return false;
        }

        $result = $this->writeStream($newpath, $response['stream'], new Config($this->getMimetype($path)));

        if ($result !== false && is_resource($response['stream'])) {
            fclose($response['stream']);
        }

        return $result !== false;
    }

    /**
     * Write a new file.
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @return array|false
     * @throws \Common\Utils\UFile\UFileException
     */
    public function write($path, $contents, Config $config)
    {
        $path = $this->applyPathPrefix($path);
        $params = $config->get('params', null);
        $mime = $config->get('mimetype', 'application/octet-stream');
        $checkCrc = $config->get('checkCrc', false);
        $res = $this->client->put($path, $contents, $mime);
        return $res;
    }

    /**
     * Write a new file using a stream.
     * @param string $path
     * @param resource $resource
     * @param Config $config
     *
     * @return array|false
     * @throws \Common\Utils\UFile\UFileException
     */
    public function writeStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    public function putFile($key, $filePath, $config = [])
    {
        return $this->client->uploadFile($key, $filePath);
    }

    /**
     * 获取私有链接
     * @param $key
     * @param int $expires
     * @return string
     */
    public function getPrivateUrl($key, $expires = 0)
    {
        return $this->client->generatePrivateUrl($key, $expires);
    }
}
