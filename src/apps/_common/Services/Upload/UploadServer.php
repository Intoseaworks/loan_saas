<?php

namespace Common\Services\Upload;

use Common\Models\Upload\Upload;
use Common\Utils\Data\StringHelper;
use Common\Utils\Email\EmailHelper;
use Common\Utils\Upload\FileHelper;
use Common\Utils\Upload\OssHelper;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadServer
 * @package App\Servers\Upload
 * @author ChangHai Zhan
 */
class UploadServer
{
    /**
     * @param $files
     * @return array|bool
     */
    public static function moveFiles($files)
    {
        $attribute = [];
        foreach ($files as $file) {
            if (!$attribute[] = self::moveFile($file)) {
                return false;
            }
        }
        return $attribute;
    }

    /**
     * @param UploadedFile $file
     * @return array|bool
     */
    public static function moveFile(UploadedFile $file)
    {
        if (Upload::hasOss()) {
            $uploadInfo = static::getOssFileName($file);
        } else {
            $uploadInfo = static::getLocalFileName($file);
        }

        if (OssHelper::helper()->uploadFile($uploadInfo['path'], $file)) {
            return $uploadInfo;
        }

        return false;
    }

    public static function saveFile($file, $path = '', $fileName = '', $extension = 'jepg')
    {
        $dev = app()->environment('prod') ? 'prod/' : 'dev/';
        $path = 'data/saas/' . $dev . $path . '/' . date("Ymd") . "/" . $fileName . '.' . $extension;
        if (OssHelper::helper()->uploadFile($path, $file)) {
            return [
                'path' => $path,
                'filename' => $fileName,
            ];
        }
        return false;
    }

    /**
     * 根据path数组下载文件
     *
     * @param array $filePaths
     * @param $destPath
     *
     * @return bool
     */
    public static function downloadFiles(array $filePaths, $destPath)
    {
        if (Config('config.app_upload_oss')) {
            $tmpPath = base_path('public/uploads/ossTmp/');
            if (!$filePaths = self::setOssTmp($tmpPath, $filePaths)) {
                return false;
            }
            $unlink = true;
        } else {
            foreach ($filePaths as &$path) {
                $path = base_path('public/' . $path);
            }
            $unlink = false;
        }
        if (count($filePaths) < 2) {
            $filePath = array_first($filePaths);
            return FileHelper::copy($filePath, $destPath, $unlink);
        }
        return self::downloadZip($filePaths, $destPath, $unlink);
    }

    /**
     * 创建sso临时文件
     *
     * @param $tmpPath
     * @param $filePaths
     *
     * @return array|bool
     */
    public static function setOssTmp($tmpPath, $filePaths)
    {
        if (!is_dir($tmpPath)) {
            @mkdir($tmpPath, 0777, true);
        }
        $ossTmpFiles = [];
        $filePathArr = (array)$filePaths;
        foreach ($filePathArr as $path) {
            $tmpFile = str_finish($tmpPath, '/') . uniqid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            if (!OssHelper::helper()->getFile($path, $tmpFile)) {
                self::unlinkFile($ossTmpFiles);
                return false;
            }
            $ossTmpFiles[] = $tmpFile;
        }
        if (!is_array($filePaths)) {
            return array_shift($ossTmpFiles);
        }
        return $ossTmpFiles;
    }

    public static function unlinkFile($file)
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (is_file($item)) {
                    unlink($item);
                }
            }
        }
        if (is_string($file) && is_file($file)) {
            unlink($file);
        }
    }

    /**
     * 打包下载
     *
     * @param $filePaths
     * @param $savePath
     * @param bool $unlink
     *
     * @return bool
     */
    public static function downloadZip($filePaths, $savePath = null, $unlink = false)
    {
        $zipFilePath = $savePath === null ? base_path('public/uploads/') . uniqid('zip') . ".zip" : $savePath;
        $zip = new \ZipArchive(); //使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        $zip->open($zipFilePath, \ZIPARCHIVE::OVERWRITE | \ZIPARCHIVE::CREATE);
        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                return false;
            }
            $attachFile = iconv("UTF-8", "GBK", $filePath); //转码，打包中文文档
            $zip->addFile($attachFile, basename($attachFile)); //压栈
        }
        $zip->close();
        if ($unlink) {
            self::unlinkFile($filePaths);
        }
        return $zipFilePath;
        //return self::download($zipFilePath, true, $fileName);
    }

    /**
     * 执行下载
     * @param $filePath
     * @param bool $unlink
     * @param null $fileName
     *
     * @return bool|BinaryFileResponse
     */
    public static function download($filePath, $unlink = false, $fileName = null)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        if (is_null($fileName)) {
            $fileName = basename($filePath);
        } else {
            $fileName = $fileName . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        }
        $response = new BinaryFileResponse($filePath);
        if ($unlink) {
            $response->deleteFileAfterSend(true);
        }
        $response->trustXSendfileTypeHeader();
        if (!is_null($fileName)) {
            return $response->setContentDisposition('attachment', $fileName,
                str_replace('%', '', Str::ascii($fileName)));
        }
        return $response;
    }

    public static function resetDownloadTmp()
    {
        $date = date('Ymd');
        $tmpPath = base_path('public/uploads/downloadTmp/');
        $currentTmpPath = $tmpPath . $date;
        if (!is_dir($currentTmpPath)) {
            mkdir($currentTmpPath, 0777, true);
            chmod($currentTmpPath, 0777);
        }
        if (!FileHelper::delAllChild($tmpPath, $date)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param $paths
     * @param string|null $uniqueName
     * @return string
     */
    public static function setDownloadTmpFileName($paths, $uniqueName = null)
    {
        if (count($paths) <= 1) {
            $ext = pathinfo(current($paths), PATHINFO_EXTENSION);
        } else {
            $ext = 'zip';
        }
        if ($uniqueName === null) {
            $uniqueName = StringHelper::generateUuid();
        }
        return base_path('public/uploads/downloadTmp/') . date('Ymd') . '/' . $uniqueName . '.' . $ext;
    }

    public static function getFileName($source_id, $type)
    {
        return 'saas' . date('Y-m-d H:i:s');
    }

    /**
     * 获取Oss保存路径
     *
     * @param UploadedFile $file
     * @return array
     */
    protected static function getOssFileName(UploadedFile $file)
    {
        $attributes = [];
        $ext = $file->getClientOriginalExtension();
        $attributes['filename'] = $file->getClientOriginalName();
        if (!$ext) {
            $user = \Auth::user();
            EmailHelper::send([
                'user_id' => $user->id ?? 0,
                'filename' => $attributes['filename'],
                'ext' => $ext,
            ], '文件上传异常');
        }
        $filename = Upload::getUniqueFileName() . '.' . $ext;
        $dev = app()->environment('prod') ? 'prod/' : 'dev/';
        $attributes['path'] = 'data/saas/' . $dev . date('Ym') . '/' . $filename;
        $attributes['real_path'] = $file->getRealPath();

        return $attributes;
    }

    /**
     * 获取本地保存路径
     *
     * @param UploadedFile $file
     * @return array
     */
    protected static function getLocalFileName(UploadedFile $file)
    {
        $attributes = [];
        $ext = $file->getClientOriginalExtension();
        $attributes['filename'] = $file->getClientOriginalName();
        if (!$ext) {
            $user = \Auth::user();
            EmailHelper::send([
                'user_id' => $user->id ?? 0,
                'filename' => $attributes['filename'],
                'ext' => $ext,
            ], '文件上传异常');
        }
        $filename = Upload::getUniqueFileName() . '.' . $ext;
        $attributes['path'] = 'uploads/' . date('Ym') . '/' . $filename;
        return $attributes;
    }
}
