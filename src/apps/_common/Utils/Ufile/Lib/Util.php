<?php
/**
 * Created by IntelliJ IDEA.
 * User: len
 * Date: 2019/11/20
 * Time: 10:13
 * author: soliang
 */

namespace Common\Utils\UFile\Lib;

class Util
{
    public static function mkdirs($path, $mode = "0755")
    {
        if (!is_dir($path)) { // 判断目录是否存在
            self::mkdirs(dirname($path), $mode); // 循环建立目录
            mkdir($path, $mode); // 建立目录
        }

        return true;
    }

    public static function getHeaderByUrl($url, $key)
    {
        $headers = self::urlParseHeaders($url);

        if ($headers === false) {
            return false;
        }

        return $headers[$key] ?? '';
    }

    /**
     * 解析url响应头
     * @param $url
     * @return array|bool
     */
    public static function urlParseHeaders($url)
    {
        $headers = @get_headers($url);

        if ($headers === false) {
            return false;
        }

        $resHeaders = [];
        foreach ($headers as $index => $header) {
            if (strstr($header, ":")) {
                $header = trim($header);
                list($k, $v) = explode(":", $header);
                $resHeaders[$k] = trim($v);
            } else {
                $resHeaders[$index] = $header;
            }
        }
        return $resHeaders;
    }

    /**
     * 获取本地文件内容
     * @param $file
     * @return bool|false|string
     */
    public static function getFileContent($file)
    {
        $f = @fopen($file, "r");
        if ($f === false) {
            return false;
        }
        $content = @fread($f, filesize($file));
        fclose($f);

        return $content;
    }

    /**
     * 文件分片处理
     * @param $file
     * @param $blkSize
     * @param callable $function
     * @param int $partNumber
     *
     * @return array|bool
     */
    public static function fileSyncopation($file, $blkSize, callable $function, $partNumber = 0)
    {
        $f = @fopen($file, "r");

        if (!$f) {
            return false;
        }

        $data = [];
        for (; ;) {
            if (@fseek($f, $blkSize * $partNumber, SEEK_SET) < 0) {
                fclose($f);
                return false;
            }
            $content = @fread($f, $blkSize);
            if ($content == FALSE) {
                if (feof($f)) break;
                fclose($f);
                return false;
            }

            $res = $function($partNumber, $content);
            if (!$res) {
                return false;
            }

            $data[] = $res;

            $partNumber += 1;
        }
        fclose($f);

        return $data;
    }

    public static function fileHash($file)
    {
        $f = fopen($file, "r");
        if (!$f) return array(null, new UCloud_Error(0, -1, "open $file error"));

        $fileSize = filesize($file);
        $buffer = '';
        $sha = '';
        $blkcnt = $fileSize / BLKSIZE;
        if ($fileSize % BLKSIZE) $blkcnt += 1;
        $buffer .= pack("L", $blkcnt);
        if ($fileSize <= BLKSIZE) {
            $content = fread($f, BLKSIZE);
            if (!$content) {
                fclose($f);
                return array("", new UCloud_Error(0, -1, "read file error"));
            }
            $sha .= sha1($content, TRUE);
        } else {
            for ($i = 0; $i < $blkcnt; $i += 1) {
                $content = fread($f, BLKSIZE);
                if (!$content) {
                    if (feof($f)) break;
                    fclose($f);
                    return array("", new UCloud_Error(0, -1, "read file error"));
                }
                $sha .= sha1($content, TRUE);
            }
            $sha = sha1($sha, TRUE);
        }
        $buffer .= $sha;
        $hash = UCloud_UrlSafe_Encode(base64_encode($buffer));
        fclose($f);
        return array($hash, null);
    }

    public static function getSizeUnit($file, $unit = 'M')
    {
        $filesize = filesize($file);
        $unit = strtoupper($unit);

        switch ($unit) {
            case 'G':
                $res = round($filesize / 1073741824 * 100) / 100;
                break;
            case 'M':
                $res = round($filesize / 1048576 * 100) / 100;
                break;
            case 'K':
                $res = round($filesize / 1024 * 100) / 100;
                break;
            default:
                return false;
        }

        return $res;
    }

    /**
     * @param $file
     * @return null
     */
    public static function fileSize($file)
    {
        return filesize($file);
    }
}
