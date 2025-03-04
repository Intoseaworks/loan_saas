define({
    "name": "风控文档",
    "version": "1.0.0",
    "description": "风控文档",
    "title": "风控文档",
    "sampleUrl": "http://services.dev.indiaox.in",
    "url": "",
    "header": {
        "title": "",
        "content": "<hr>\n<h2><strong>Common</strong></h2>\n<h4><strong>请求</strong></h4>\n<p>开发环境：http://services.dev.indiaox.in</p>\n<h4><strong>签名方式</strong></h4>\n<p>请求数据中添加[app_key,secret_key,random_str,time]字段，根据key升序排序(值为数组的遍历进行升序排序)后先进行http_build_query()，然后再md5()得到sign放入参数中。注意最后剔除secret_key键。</p>\n<p>app_key:平台提供app_key<br>\nsecret_key:平台提供secret_key<br>\nrandom_str:16位随机字符串<br>\ntime:请求时时间戳</p>\n<pre><code>// 签名示例\npublic static function sign(&amp;$params, $apiSecretKey = null)\n{\n    if (isset($params['sign'])) {\n        unset($params['sign']);\n    }\n    if (empty($params['app_key'])) {\n        throw new \\Exception('app_key不存在');\n    }\n    if (!isset($params['random_str'])) {\n        $params['random_str'] = self::getRandom();\n    }\n    if (!isset($params['time'])) {\n        $params['time'] = self::getTime();\n    }\n    $params['secret_key'] = $apiSecretKey;\n    self::ksortArray($params);\n    $sign = md5(http_build_query($params));\n    unset($params['secret_key']);\n    return $sign;\n}\n\npublic static function ksortArray(&amp;$params)\n{\n    foreach ($params as &amp;$value) {\n        if (is_array($value)) {\n            self::ksortArray($value);\n        }\n    }\n    ksort($params);\n    return $params;\n}\n</code></pre>\n<h4><strong>请求示例</strong></h4>\n<pre><code>{\n    &quot;app_key&quot;:&quot;X0p1jrEG3750hmuBru&quot;,\n    &quot;notice_url&quot;:&quot;&quot;,\n    &quot;order_no&quot;:&quot;test132456&quot;,\n    &quot;random_str&quot;:&quot;e7se5QDkxIESqbTZTNF6jYfOog0et3Ei&quot;,\n    &quot;time&quot;:1585545466,\n    &quot;user_id&quot;:&quot;123&quot;,\n    &quot;sign&quot;:&quot;6ec0190881bfb530d497a3ae55e810a0&quot;\n}\n</code></pre>\n<h4><strong>返回格式</strong></h4>\n<table>\n<thead>\n<tr>\n<th>字段</th>\n<th>类型</th>\n<th>描述</th>\n</tr>\n</thead>\n<tbody>\n<tr>\n<td>status</td>\n<td>int</td>\n<td>请求状态 18000:成功 13000:失败 全局状态</td>\n</tr>\n<tr>\n<td>msg</td>\n<td>String</td>\n<td>信息</td>\n</tr>\n<tr>\n<td>data</td>\n<td>Array</td>\n<td>返回数据</td>\n</tr>\n</tbody>\n</table>\n"
    },
    "footer": {
        "content": "<hr>\n<p>Copyright</p>\n"
    },
    "template": {
        "withCompare": true,
        "withGenerator": false
    },
    "order": [
        "Common",
        "Api",
        "SendData",
        "Callback"
    ],
    "defaultVersion": "0.0.0",
    "apidoc": "0.3.0",
    "generator": {
        "name": "apidoc",
        "time": "2020-07-19T02:05:52.373Z",
        "url": "http://apidocjs.com",
        "version": "0.23.0"
    }
});
