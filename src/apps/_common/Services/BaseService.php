<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 10:40
 */

namespace Common\Services;

use Carbon\Carbon;
use Common\Exceptions\ApiException;

class BaseService
{
    const OUTPUT_SUCCESS = 18000;
    const OUTPUT_ERROR = 13000;

    /** 商户超额报错 */
    const OUTPUT_EXCESS_ERROR = 13001;
    /** 代付超额 */
    const OUTPUT_DAIFU_EXCESS = 13011;
    /** 代付失败 */
    const OUTPUT_DAIFU_FAIL = 13012;

    public $code = self::OUTPUT_SUCCESS;
    public $msg;
    public $data;

    protected static $_classServer;

    /**
     * @suppress PhanParamTooManyUnpack
     * @return static
     */
    public static function server()
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

    public function eqCode($code)
    {
        return $this->code == $code;
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
     * 当前时间戳
     * @return int
     */
    public function getTime()
    {
        return Carbon::now()->timestamp;
    }

    /**
     * 当前毫秒时间戳
     * @return float|int
     */
    public function getTimeMs()
    {
        return Carbon::now()->timestamp * 1000;
    }

    /**
     * 当前时间
     * 2019-1-21 09:25:18
     * @return string
     */
    public function getDateTime()
    {
        return Carbon::now()->toDateTimeString();
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

    /**
     * 判断是否导出
     *
     * @return bool
     */
    protected function getExport()
    {
        $res = (bool)array_get(request()->all(), 'export', 0);
        # 接触禁止 20210728
        # tracy 20211018 停止
        if($res){
            throw new \Exception("Temporarily disable this feature");
        }
        return $res;
    }

}
