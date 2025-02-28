<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/16
 * Time: 9:32
 */

namespace Common\Traits\Response;


trait Send
{
    /**
     * 接口统一成功输出
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function resultSuccess($data = null, $msg = 'Success')
    {
        return $this->result(18000, $msg, $data);
    }

    /**
     * 接口统一输出
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function result($code = 18000, $msg = '', $data = null)
    {
        # 统一国际化
        if ($msg != '') {
            $msg = t($msg, 'exception');
        }
        # 终端约定，data无值时，默认返回null
        # data为字符串时，终端会崩，需转null
        if (empty($data) || is_string($data) || !$data) {
            //$data = null;
        }
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }

    /**
     * 接口统一失败输出
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function resultFail($msg = '', $data = null)
    {
        return $this->result(13000, $msg, $data);
    }

    public function resultSuccessOrigin($data = null, $msg = '')
    {
        return [
            'code' => 18000,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
