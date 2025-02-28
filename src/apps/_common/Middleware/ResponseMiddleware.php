<?php

namespace Common\Middleware;

use Closure;
use Common\Jobs\Log\ResponseLogJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cache;

class ResponseMiddleware {

    public function handle(Request $request, Closure $next, $prefix = null) {
        $uri = $request->getRequestUri();
        // 触发频繁访问
        if ($this->frequentLock($request->request->get('token'))) {
            // 设置json格式
            header('content-type:application/json;charset=utf-8');
            print('{"code":13000,"msg":"' . t('操作过于频繁, 请稍后再试!', 'exception') . '","data":null}');
            exit;
        }
        $response = $next($request);
        $files = [];
        if (isset($request->files)) {
            foreach ($request->files as $key => $file) {
                $files[$key] = $file->getClientOriginalName();
            }
        }
        $this->log($uri, array_merge($request->request->all(), $files), $response, $prefix);
        return $response;
    }

    private function log($uri, $request, $response, $prefix) {
        if (isset($response->original['code']) && $uri) {
            $code = $response->original['code'];
            $arrayUri = explode('?', $uri);
            $url = $uri;
            if (isset($arrayUri[0])) {
                $url = $arrayUri[0];
            }
            $request['app-key'] = app(Request::class)->header("app-key");
            # 需要记录日志的名单
            $logUriList = [
                "13000" => [
                    "/app/order/sign",
                    "/app/pwd-login",
                    "/app/register",
                    "/app/pwd-reg",
                    "/app/register-web",
                    "/app/login"
                ],
                "18000" => [
                    "/app/register",
                    "/app/pwd-reg",
                    "/app/register-web",
                    "/app/login",
                    "/app/change-pwd"
                ]
            ];
            $ifRecord = false;
            $user = Auth::guard('api')->user();
            if (isset($logUriList[$code]) && in_array($url, $logUriList[$code])) {
                $ifRecord = true;
            }
//            if($user && in_array($user->merchant_id,[5,4])) {
//                $ifRecord = true;
//            }
            if ($ifRecord) {
                $uid = 0;
                if ($user) {
                    $uid = $user->id;
                }
                $data = [
                    'user_id' => $uid,
                    "uri" => $uri,
                    "request" => json_encode($request),
                    "code" => $response->original['code'],
                    "msg" => $response->original['msg'],
                    "data" => json_encode($response->original['data']),
                ];
                dispatch(new ResponseLogJob($data));
            }
        }
    }

    public function frequentLock($token) {
        return false;
        $key = 'frequent-lock-' . md5($token);
//        echo $key;
        Cache::delete($key);
        if ($val = Cache::get($key)) {
//            echo time().'-'.$val;
            if ((time() - $val) <= 4) {
                return true;
            }
        }
        Cache::set($key, time(), 1);
        return false;
    }

}
