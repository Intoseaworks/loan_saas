<?php

namespace Common\Utils\UFile\Lib;

class ActionType
{
    const NONE = -1;
    /**
     * 普通上传
     * 该类型适用于0-10MB小文件,更大的文件建议使用分片上传接口
     */
    const PUTFILE = 0;
    /**
     * web 表单上传
     * 该接口适用于web的POST表单上传,本SDK为了完整性故带上该接口demo.
     * 服务端上传建议使用分片上传接口,而非POST表单
     */
    const POSTFILE = 1;
    /**
     * 分片上传：初始化
     * 获取本地上传的uploadId和分片大小
     */
    const MINIT = 2;
    /**
     * 分片上传：数据上传
     */
    const MUPLOAD = 3;
    /**
     * 分片上传：完成上传
     */
    const MFINISH = 4;
    /**
     * 分片上传：取消上传
     */
    const MCANCEL = 5;
    /**
     * 删除文件
     */
    const DELETE = 6;
    /**
     * 秒传
     * 非上传接口，如果秒传返回非200错误码，意味着该文件在服务器不存在，需要继续调用其他上传接口完成上传操作
     */
    const UPLOADHIT = 7;
    /**
     * 生成私有文件Url
     */
    const GETFILE = 8;
    /**
     * 追加上传
     * 该接口适用于0-10MB小文件,更大的文件建议使用分片上传接口
     */
    const APPENDFILE = 9;

    public static function getMethod($type)
    {
        switch ($type) {
            case self::PUTFILE:
            case self::MUPLOAD:
            case self::APPENDFILE:
                $method = 'PUT';
                break;
            case self::POSTFILE:
            case self::MINIT:
            case self::MFINISH:
            case self::UPLOADHIT:
                $method = 'POST';
                break;
            case self::MCANCEL:
            case self::DELETE:
                $method = 'DELETE';
                break;
            case self::GETFILE:
                $method = 'GET';
                break;
            default:
                $method = '';
        }
        return $method;
    }

    /**
     * 根据请求类型获取对于的请求路径
     * @param $type
     * @param $key
     * @return string
     */
    public static function getUrlPath($type, string $key): string
    {
        switch ($type) {
            case self::POSTFILE:
                $path = '';
                break;
            case self::UPLOADHIT:
                $path = 'uploadhit';
                break;
            default:
                $path = $key;
        }

        return $path;
    }

    public static function getHeader($type, string $mimeType = ''): array
    {
        switch ($type) {
            case self::PUTFILE:
            case self::MUPLOAD:
            case self::APPENDFILE:
                $header = [
                    'Expect' => '',
                    'Content-Type' => $mimeType,
                ];
                break;
            case self::POSTFILE:
                $header = ['Expect' => '',];
                break;
            case self::MINIT:
            case self::MCANCEL:
            case self::UPLOADHIT:
            case self::DELETE:
                $header = [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ];
                break;
            case self::MFINISH:
                $header = [
                    'Content-Type' => 'text/plain',
                ];
                break;
            default:
                $header = [];
        }

        return $header;
    }
}
