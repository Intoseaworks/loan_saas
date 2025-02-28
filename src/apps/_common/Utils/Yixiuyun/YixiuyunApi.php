<?php

namespace Common\Utils\Yixiuyun;

use Yunhan\Utils\Env;

class YixiuyunApi {
    /* PROD 配置 */
    const PROD_PUSH_URL = "http://os.yixiu-cloud.com/api/v2/open/push";
    const PROD_STOP_URL = "http://os.yixiu-cloud.com/api/v2/open/paid";
    const PROD_APP_ID = "phpcash";
    const PROD_APP_SECRET = "15d99ac8e4244c3d8c555015ea19a141";

    /* TEST 配置 */
    const TEST_PUSH_URL = "http://test.os.yixiu-cloud.com/api/v2/open/push";
    const TEST_STOP_URL = "http://test.os.yixiu-cloud.com/api/v2/open/paid";
    const TEST_APP_ID = "testapi";
    const TEST_APP_SECRET = "testsecret";

    /* 当前配置 */
    public $_push_url = "";
    public $_stop_url = "";
    public $_app_id = "";
    public $_app_secret = "";

    /**
     * 初始化配置
     */
    public function __construct() {
        if (Env::isProd()) {
            $this->_push_url = self::PROD_PUSH_URL;
            $this->_stop_url = self::PROD_STOP_URL;
            $this->_app_id = self::PROD_APP_ID;
            $this->_app_secret = self::PROD_APP_SECRET;
        } else {
            $this->_push_url = self::TEST_PUSH_URL;
            $this->_stop_url = self::TEST_STOP_URL;
            $this->_app_id = self::TEST_APP_ID;
            $this->_app_secret = self::TEST_APP_SECRET;
        }
    }

    public function post_coll(Array $data, $url) {
        /* 加密 */
        $_sign = $this->sign($this->_app_id, $this->_app_secret);
        $new_data = $data;
        // 组装参数
        $data = ["paramList" =>
            ["appid" => $this->_app_id, "timestamp" => time(), "sign" => $_sign]];
        $data['paramList']['list'][] = $new_data;
        $data['paramList'] = json_encode($data['paramList']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type: application/json;charset=UTF-8"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//        print_r("请求报文：".json_encode($data).PHP_EOL);
        $response = curl_exec($ch);
//        print_r("返回报文：".$response.PHP_EOL);

        curl_close($ch);
        return json_decode($response, true);
    }

    public function post_coll_list(Array $data, $url) {
        /* 加密 */
        $_sign = $this->sign($this->_app_id, $this->_app_secret);
        $new_data = $data;
        // 组装参数
        $data = ["paramList" =>
            ["appid" => $this->_app_id, "timestamp" => time(), "sign" => $_sign]];
        $data['paramList']['list'] = $new_data;
        $data['paramList'] = json_encode($data['paramList']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type: application/json;charset=UTF-8"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//        print_r("请求报文：".json_encode($data).PHP_EOL);
        $response = curl_exec($ch);
//        print_r("返回报文：".$response.PHP_EOL);

        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * 签名
     * @param $app_id
     * @param $app_secret
     * @return sign
     */
    protected function sign($app_id, $app_secret) {
        $data = $app_id . $app_secret . date("Ymd");
        return md5($data);
    }

}
