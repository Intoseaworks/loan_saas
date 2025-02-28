<?php

namespace Common\Utils\Upload;

class FileHelper
{
    /**
     * 删除所有子目录
     * @param $dirName
     * @param $except
     * @param bool $onlyDir
     * @return bool
     */
    public static function delAllChild($dirName, $except = null, $onlyDir = false)
    {
        if (!is_dir($dirName)) {
            return true;
        }
        $list = scandir($dirName);
        if (!empty($except)) {
            $except = (array)$except;
        }
        foreach ($list as $item) {
            if ($item != '.' && $item != '..' && !empty($except) && !in_array($item, $except)) {
                $dir = $dirName . '/' . $item;
                is_dir($dir) ? self::delDir($dir) : ($onlyDir && @unlink($dir));
            }
        }
        return true;
    }

    /**
     * @param $dirName
     * @return bool
     */
    public static function delDir($dirName)
    {
        if (!is_dir($dirName)) {
            return false;
        }
        $handle = @opendir($dirName);
        while (($file = @readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $dir = $dirName . '/' . $file;
                is_dir($dir) ? self::delDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);

        return @rmdir($dirName);
    }

    /**
     * copy文件
     * @param $source
     * @param $dest
     * @param bool $unlink
     * @return bool
     */
    public static function copy($source, $dest, $unlink = false)
    {
        if (!$result = copy($source, $dest)) {
            return $result;
        }
        $unlink && @unlink($source);
        return $result;
    }

    /**
     * 文件转base64
     * @param $file
     * @return string
     */
    public static function base64EncodeFile($file)
    {
        $data = fread(fopen($file, 'r'), filesize($file));
        $base64 = chunk_split(base64_encode($data));
        return $base64;
    }
}
