<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/5
 * Time: 19:21
 */

namespace Common\Utils\Push\Services;

class GooglePush
{
    const TOPIC_ALL = 'all';
    const TOPIC_APP = 'app_';

    //private static $serverKey = 'AAAAW7bXMe4:APA91bFUtrJHlhdhnsuyqN_sqvu68Z62pBX0nYuoWRMqO0vIBqnk5K8SKvxNWDj6DibFXr0J0RW9Ia1BnhWbHOM7J3A8-IwkF41lYnBP2lBsoGnrGIXsoIGYSoXlzFYk1ld-r2ujonBC';
    private static $serverKey = 'AIzaSyBHo8KHb4M6TYISAqolVla0mR3GO1svo-4';
    private static $url = 'https://fcm.googleapis.com/fcm/send';

    //对所有用户发送主题通知 ，，主题名称：CashNow
    public static function sendNotificationForTopic($title, $body, $topic = 'CashNow', $link = '')
    {
        //TODO 未测试，等待客户端接入获取token后再测试
        //return self::sendMessage($title, $body, $topic, $link);
        $data = [
            'topic' => $topic,
            'notification' => [
                "title" => $title,
                "body" => $body,
                //"icon" => "https://...../icons/android-icon-192x192.png",
                "click_action" => $link
            ]
        ];
        $data_string = json_encode($data);

        $ch = curl_init(self::$url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type:application/json',
                'Authorization:key=' . self::$serverKey
            ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //对指定token用户发送通知
    public static function sendMessage($token, $title, $body, $custom = [], $serverKey = '')
    {
        $SERVER_KEY = $serverKey;
        //echo $SERVER_KEY;exit();
        if (count($custom) == 0) {
            $custom = ['type' => ''];
        }

        $data = [
            "to" => $token,
            /*"data" => [
                $custom
            ],*/
            "data" => $custom,
            //"content_available"   => false,
            "notification" => array_merge([
                "title" => $title,
                "body" => $body,
            ], $custom)
        ];

        //var_dump($data);exit();
        $data_string = json_encode($data);

        $ch = curl_init(self::$url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Authorization: key=' . $SERVER_KEY
            ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 将用户添加道主题
     *
     * @param $googleToken
     * @param $topic
     * @return mixed
     */
    public static function subscribeToTopic($googleToken, $topic, $serverKey)
    {
        $url = 'https://iid.googleapis.com/iid/v1/' . $googleToken . '/rel/topics/' . $topic;
        $header = [
            'Content-Type: application/json',
            'Authorization: key=' . $serverKey,
            'Content-Length: 0',
        ];
        $data = [];
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 向主题发送消息
     *
     * @param $topic
     * @return mixed
     */
    public static function sendMessageToGroup($topic, $title, $body, $serverKey)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $header = [
            'Content-Type: application/json',
            'Authorization: key=' . $serverKey,
            //'project_id: 196236249110',
        ];
        $data = [
            'to' => '/topics/' . $topic,
            "notification" => [
                'title' => $title,
                'body' => $body,
            ],
        ];
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 获取自己的主题内容
     *
     * @param $googleToken
     * @return mixed
     */
    public static function myTopic($googleToken)
    {
        $url = 'https://iid.googleapis.com/iid/info/' . $googleToken . '?details=true';
        $header = [
            //'Content-Type: application/json',
            'Authorization: key=' . self::$serverKey,
            'details:true',
        ];
        $data = '';
        $data_string = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
