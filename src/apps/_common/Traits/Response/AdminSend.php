<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/16
 * Time: 9:32
 */

namespace Common\Traits\Response;


trait AdminSend
{
    /**
     * 接口统一输出
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function result($code = 18000, $msg = '', $data = [])
    {
        # 统一国际化
        if ($msg != '') {
            $msg = t($msg, 'exception');
        }

        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
