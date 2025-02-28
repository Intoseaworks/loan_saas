<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 10:40
 */

namespace Common\Helper;

use Carbon\Carbon;
use Common\Exceptions\ApiException;

class BaseHelper
{
    const OUTPUT_SUCCESS = 18000;
    const OUTPUT_ERROR = 13000;

    /** 商户超额报错 */
    const OUTPUT_EXCESS_ERROR = 13001;

    public $code = self::OUTPUT_SUCCESS;
    public $msg;
    public $data;

    protected static $_classServer;

    /**
     * @suppress PhanParamTooManyUnpack
     * @return static
     */
    public static function helper()
    {
        $params = func_get_args();
        if ($params) {
            return new static(...$params);
        } else {
            return new static();
        }
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        if ($this->code == self::OUTPUT_SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        if ($this->code != self::OUTPUT_SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * 逻辑层成功输出
     * @param $msg
     * @param $data
     * @return $this
     */
    protected function outputSuccess($msg = '', $data = '')
    {
        return $this->output(self::OUTPUT_SUCCESS, $msg, $data);
    }

    /**
     * 逻辑层错误输出
     * @param string $msg
     * @param string $data
     * @return $this
     */
    protected function outputError($msg = '', $data = '')
    {
        return $this->output(self::OUTPUT_ERROR, $msg, $data);
    }

    /**
     * 用于方便逻辑层直接抛到客户端
     * 减少控制器层对错误逻辑处理
     * @param string $msg
     * @param int $code
     * @throws ApiException
     */
    protected function outputException($msg = '', $code = self::OUTPUT_ERROR)
    {
        # 统一国际化
        if ($msg != '') {
            $msg = t($msg, 'exception');
        }
        throw new ApiException($msg, $code);
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getAll()
    {
        return [
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->data
        ];
    }

    /**
     * @param $code
     * @param string $msg
     * @param string $data
     * @return $this
     */
    protected function output($code, $msg = '', $data = '')
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
        return $this;
    }
}
