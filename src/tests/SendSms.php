<?php

require "TelephoneList.php";

function request($data) {
    $username = "ApiUserAdmin";
    $password = "abc12345678";
    $url = "http://202.124.134.242:6756/API/SendSMS";
    $header = array(
        'Content-Type: application/json',
        "charset='utf-8'",
        'Accept: application/json'
    );
    $ch = curl_init();
    $json = json_encode($data, JSON_UNESCAPED_SLASHES);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //不自动输出任何内容到浏览器
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);   //只需要设置一个秒的数量就可以
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    // 设置header头
    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $output = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($code == 200) {
        return json_decode($output, TRUE);
    }
    echo $error;
    return false;
}

function getPortId() {
    $configFilePath = "./" . date("Ymd") . "port_check.cfg";
    if (file_exists($configFilePath)) {
        $portFile = file_get_contents($configFilePath);
        $list = json_decode($portFile);
    } else {
        $list = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => 0,
            12 => 0,
            13 => 0,
            14 => 0,
            15 => 0,
            16 => 0,
            17 => 0,
            18 => 0,
            19 => 0,
            20 => 0,
            21 => 0,
            12 => 0,
            23 => 0,
            24 => 0,
            25 => 0,
            26 => 0,
            27 => 0,
            28 => 0,
            29 => 0,
            30 => 0,
            31 => 0,
            32 => 0,
        );
    }
    foreach ($list as $key => $value) {
        # 每日不能超过400 避免封卡
        if ($value >= 399) {
            unset($list->$key);
        }
    }
    if ($list && count($list) > 0) {
        $port = array_rand((array) $list, 1);
        $list->$port += 1;
        file_put_contents($configFilePath, json_encode($list));
        return $port;
    }
    return false;
}

foreach ($telephones as $item) {
    echo $item['telephone'] . "#" . date("H:i:s") . PHP_EOL;
    $telephone = $item['telephone'];
    $smsContent = ($sendId ? "[{$sendId}]" : "") . $item['content'];
    $data = [
        "event" => "txsms",
        "num" => "0" . $telephone,
        "port" => "-1",
        "encoding" => "0",
        "smsinfo" => $smsContent,
    ];
    $res = request($data);
    if ($res['result'] == 'error') {
        echo 'error sleep 40 sec'.PHP_EOL;
        sleep(40);
    }
    echo json_encode($res).PHP_EOL;
    echo "over".PHP_EOL;
}