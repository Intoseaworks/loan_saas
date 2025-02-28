---

## **Common**

#### **请求**

开发环境：http://services.dev.indiaox.in

#### **签名方式**

请求数据中添加[app_key,secret_key,random_str,time]字段，根据key升序排序(值为数组的遍历进行升序排序)后先进行http_build_query()，然后再md5()得到sign放入参数中。注意最后剔除secret_key键。

app_key:平台提供app_key     
secret_key:平台提供secret_key       
random_str:16位随机字符串     
time:请求时时间戳     

    // 签名示例
    public static function sign(&$params, $apiSecretKey = null)
    {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        if (empty($params['app_key'])) {
            throw new \Exception('app_key不存在');
        }
        if (!isset($params['random_str'])) {
            $params['random_str'] = self::getRandom();
        }
        if (!isset($params['time'])) {
            $params['time'] = self::getTime();
        }
        $params['secret_key'] = $apiSecretKey;
        self::ksortArray($params);
        $sign = md5(http_build_query($params));
        unset($params['secret_key']);
        return $sign;
    }
    
    public static function ksortArray(&$params)
    {
        foreach ($params as &$value) {
            if (is_array($value)) {
                self::ksortArray($value);
            }
        }
        ksort($params);
        return $params;
    }
           
#### **请求示例**
    {
        "app_key":"X0p1jrEG3750hmuBru",
        "notice_url":"",
        "order_no":"test132456",
        "random_str":"e7se5QDkxIESqbTZTNF6jYfOog0et3Ei",
        "time":1585545466,
        "user_id":"123",
        "sign":"6ec0190881bfb530d497a3ae55e810a0"
    }

#### **返回格式**

字段 | 类型 | 描述
---|---|---
status|int|请求状态 18000:成功 13000:失败 全局状态
msg|String|信息
data|Array|返回数据
