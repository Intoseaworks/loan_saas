<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Common\Console\Services\System;


use Common\Utils\Email\EmailHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemServer extends \Common\Services\BaseService
{
    /**
     * 系统存储连接检测
     */
    public function storeCheck()
    {
        $data = [
            'mysql' => [
                config('database.connections.mysql.database') => [
                    'host' => config('database.connections.mysql.host'),
                    'port' => config('database.connections.mysql.port'),
                    'username' => config('database.connections.mysql.username'),
                    'connection_status' => $this->tryCatch(function () {
                        return DB::select('SELECT 1');
                    }),
                ],
            ],
            'redis' => [
                'host' => config('database.redis.default.host'),
                'port' => config('database.redis.default.port'),
                'database' => config('database.redis.default.database'),
                'connection_status' => $this->tryCatch(function () {
                    return Redis::select(config('database.redis.default.database'));
                }),
            ],
        ];
        if ($this->checkStatus($data) == false) {
            return $this->outputError('检查异常', $data);
        }
        return $this->outputSuccess('检查正常', $data);
    }

    /**
     * @param $fun
     * @return int
     */
    protected function tryCatch($fun)
    {
        try {
            return $fun() ? 1 : 0;
        } catch (\Exception $e) {
            EmailHelper::sendException($e, '心跳检测');
            return 0;
        }
    }

    protected function checkStatus($array)
    {
        $json = json_encode($array);
        $needle = '"connection_status":0';
        return strpos($json, $needle) === false;
    }
}
