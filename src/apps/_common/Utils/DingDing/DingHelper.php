<?php

namespace Common\Utils\DingDing;

use Common\Jobs\DingNoticeJob;

class DingHelper
{
    const AT_SOLIANG = '15700767943';
    const AT_CXS = '18816781114';

    const KEYWORD = [
        self::ROBOT_APP_EXCEPTION => '终端异常上报',
    ];

    const ROBOT_APP_EXCEPTION = 'app_exception';

    public static function notice($content, $title = null, $atMobiles = null, $queue = true, $robot = '')
    {
        if (is_array($content)) {
            $content = json_encode($content, 256);
        }

        // 检查闭包序列化报错start
        try {
            $job = (new DingNoticeJob($content, $title, $atMobiles, $robot));
            serialize(clone $job);
        } catch (\Exception $e) {
            $array = debug_backtrace();

            $debugInfo = array_only(current($array), ['file', 'line', 'function', 'class']);
            self::addQueueSend($debugInfo, '检查闭包序列化报错', self::AT_SOLIANG, false);
            return false;
        }
        // 检查闭包序列化报错end

        if ($queue) {
            self::addQueueSend($content, $title, $atMobiles, $robot);
            return true;
        }

        $keyword = self::KEYWORD[$robot] ?? '埃及SaaS业务-' . app()->environment();
        $content = <<<CONTENT
$keyword -- $title
$content
CONTENT;

        try {
            $dingInstance = dingNotice($robot)->setTextMessage($content);
            if (isset($atMobiles)) {
                $dingInstance->setAtMobiles((array)$atMobiles);
            }
            $dingInstance->send();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param $content
     * @param $title
     * @param $atMobiles
     * @param $robot
     * @return mixed
     */
    private static function addQueueSend($content, $title = null, $atMobiles = null, $robot = '')
    {
        //走推送队列
        return dispatch((new DingNoticeJob($content, $title, $atMobiles, $robot)));
    }
}
