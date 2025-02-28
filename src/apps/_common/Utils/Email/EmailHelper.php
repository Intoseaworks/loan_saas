<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/25
 * Time: 11:03
 * @author ChangHai Zhan
 */

namespace Common\Utils\Email;

use Common\Jobs\SendEmailJob;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Host\HostHelper;
use Exception;
use Illuminate\Http\Request;
use JMD\JMD;
use JMD\Libs\Services\EmailServers;

/**
 * Class Email
 * @package Common\Common\Response
 * @author ChangHai Zhan
 */
class EmailHelper
{
    /**
     * @param $content
     * @param $title
     * @param $receiver
     * @param bool $queue
     * @param null $attachments
     * @return bool|mixed
     */
    public static function send($content, $title, $receiver = [], $queue = true, $attachments = null)
    {
        $projectName = env('PROJECT_NAME', 'SaaS');
        $title = "{$projectName}|" . $title . ' - ' . app()->environment();
        $content = ArrayHelper::arrayToJson($content);
        if ($queue) {
            return static::addQueueSend($content, $title, $receiver, $attachments);
        }
        return static::mailer($content, $title, $receiver, $attachments);
    }

    /**
     * 发送异常邮件
     * @param Exception $exception
     * @param $title
     * @param array $receiver
     * @param bool $queue
     * @param null $attachments
     * @return bool|mixed
     */
    public static function sendException(
        Exception $exception,
        $title = '',
        $receiver = [],
        $queue = true,
        $attachments = null
    ) {
        $content = self::warpException($exception);
        $projectName = env('PROJECT_NAME', 'SaaS');
        $title = "{$projectName}|" . $title . ' - ' . app()->environment();
        $content = ArrayHelper::arrayToJson($content);
        if ($queue) {
            return static::addQueueSend($content, $title, $receiver, $attachments);
        }
        return static::mailer($content, $title, $receiver);
    }

    /**
     * @param $content
     * @param $title
     * @param $receiver
     * @param $attachments
     * @return mixed
     */
    private static function addQueueSend($content, $title = null, $receiver = null, $attachments = null)
    {
        //邮箱走email队列
        return dispatch((new SendEmailJob($content, $title, $receiver, $attachments)));
    }

    /**
     * 发送邮件
     * @param string $content 邮件内容
     * @param string $title 邮件标题
     * @param array|string $receiver 邮件接收者
     * @param string|null $attachments
     * @return bool
     */
    public static function mailer($content, $title = null, $receiver = [], $attachments = null)
    {
        if ($title === null) {
            $title = "Saas系统邮件 - " . app()->environment();
        }
        $content = ArrayHelper::arrayToJson($content);

        # 切换钉钉推送
        DingHelper::notice($content, $title);
        return true;

        $receiver = self::getMail($receiver);

        /** 加入印牛服务逻辑Start */
        try {
            JMD::init(['projectType' => 'lumen']);
            $res = EmailServers::sendEmail($content, $title, $receiver, $attachments);
            if ($res->isSuccess()) {
                return true;
            }
            throw new Exception($res->getMsg());
        } catch (\Exception $e) {
            DingHelper::notice([
                'title' => $title,
                'content' => $content,
                'receiver' => $receiver,
                'exception' => $e->getMessage()
            ], '【异常】邮件印牛服务发送异常');
        }
        /** 加入印牛服务逻辑End */

        /*try {
            Mail::html($content, function ($message) use ($title, $receiver, $attachments) {
                $message->setSubject($title);
                $message->to($receiver);
                if (!empty($attachments)) {
                    $attachments = (array)$attachments;
                    array_map(function ($attachment) use ($message) {
                        $message->attach($attachment);
                    }, $attachments);
                }
            });
        } catch (\Exception $e) {
            //echo $e->getMessage();exit();
            Log::error($e->getCode(), [$e->getMessage(), $e->getLine(), $e->getTraceAsString()]);
            return false;
        }*/
        return true;
    }

    /**
     * @param array $develops
     * @return mixed|null
     */
    public static function getMail($develops = [])
    {
        if (empty($develops)) {
            $develops = config('config.develops');
        }
        # 临时email地址转换
        if (!is_array($develops)) {
            $develops = (array)$develops;
        }
        $change = [
            'chengxusheng@jiumiaodai.com' => 'chengxusheng1114@dingtalk.com',
            'cxs' => 'chengxusheng1114@dingtalk.com',
            'zfc@jiumiaodai.com' => 'zhongfucheng1139@dingtalk.com',
            'chensibei@jiumiaodai.com' => 'wka8154@dingtalk.com',
            'gushenghao@jiumiaodai.com' => 'gushenghao@jinqianbao0755.onaliyun.com',
            'nio' => 'nio.wang@scoreonetech.com',
        ];
        foreach ($develops as $key => $email) {
            if (array_key_exists($email, $change)) {
                $develops[$key] = array_get($change, $email);
            }
        }
        return $develops;
    }

    /**
     * @param Exception $exception
     * @return string
     */
    public static function warpException(Exception $exception)
    {
        $request = Request::capture();

        $requestMethod = $request->getRealMethod();
        $messages = [
            'User-Agent' => $request->userAgent(),
            'Url' => $request->url(),
            'Ip-Address' => HostHelper::getAddressByIp(HostHelper::getIp()),
            'Method' => $requestMethod,
            'Message' => $exception->getMessage(),
            'File' => $exception->getFile() . ':' . $exception->getLine(),
            'Get' => ArrayHelper::arrayToJson($request->query()),
            'Post' => ArrayHelper::arrayToJson($request->post()),
            'Trace' => PHP_EOL . $exception->getTraceAsString(),
        ];
        if ($requestMethod === 'GET') {
            unset($messages['Post']);
        }
        foreach ($messages as $key => $message) {
            $messages[$key] = '[' . $key . ']: ' . $message;
        }

        return implode(PHP_EOL, array_values($messages));
    }
}
