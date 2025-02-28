<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        {{ $title }}
    </title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
{{--<link href="https://cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">--}}
{{--<link rel="stylesheet" href="{{asset("/adminlte/bootstrap/css/bootstrap.min.css")}}">--}}
<!-- Font Awesome -->
    {{--<link href="https://cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">--}}
    <style type="text/css">
    </style>
</head>
<body id="app-layout" style="background-color: rgb(51, 51, 51)">
<div class="container" style="margin:0 auto;padding-top:60px">
    <div style="text-align: center;font-size: 20px;color: #ffffff">{{ $title }}</div>
    <div class="modal-body" id="login_container" style="mmargin:10 auto;text-align:center">
        <input type="hidden" id="goto" value="{{ $goto }}">
        <input type="hidden" id="window_location_href" value="{{ $ding_url }}">
    </div>
    <div onclick='location.href="/oa/api/sso/pwd_login"' style="text-align: center;font-size: 16px;color: #ffffff">
        {{ trans('login.账号登录') }}
    </div>
</div>

<!-- JavaScripts -->
<!-- jQuery 2.2.3 -->
<script src="https://cdn.bootcss.com/jquery/2.2.2/jquery.min.js"></script>
{{--<script src="/js/jquery.min.js"></script>--}}
<!-- Bootstrap 3.3.6 -->
<script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.min.js"></script>
{{--<script src="/js/jquery.form.min.js"></script>--}}
<script src="https://cdn.bootcss.com/moment.js/2.11.2/moment.js"></script>
{{--<script src="/js/moment.js"></script>--}}
{{--<script src="{{asset("/adminlte/bootstrap/js/bootstrap.min.js")}}"></script>--}}

{{--<script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.min.jscdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>--}}

<script src="https://g.alicdn.com/dingding/dinglogin/0.0.5/ddLogin.js"></script>
<script>
    var goto = $('#goto').val();
    var window_location_href = $('#window_location_href').val();
    var obj = DDLogin({
        id: "login_container",//这里需要你在自己的页面定义一个HTML标签并设置id，例如<div id="login_container"></div>或<span id="login_container"></span>
        goto: goto,
        style: "border:none;background-color:#FFFFFF",
        width: "335",
        height: "300"
    });

    var hanndleMessage = function (event) {
        var origin = event.origin;
        // 这些需要对origin进行判断下，如果origin的domain 和 嵌入二维码页面url的domain相等的情况下再去取loginTmpCode 并进行跳转
        if (origin == "https://login.dingtalk.com") { //判断是否来自ddLogin扫码事件。
            var loginTmpCode = event.data; //拿到loginTmpCode后就可以在这里构造跳转链接进行跳转了
            console.log(loginTmpCode);return false;
            window.location.href = window_location_href + "&loginTmpCode=" + loginTmpCode;
        }
    };
    if (typeof window.addEventListener != 'undefined') {
        window.addEventListener('message', hanndleMessage, false);
    } else if (typeof window.attachEvent != 'undefined') {
        window.attachEvent('onmessage', hanndleMessage);
    }
</script>
</body>
</html>
