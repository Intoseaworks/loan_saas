<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 11:24
 */

namespace Common\Console\Commands;


use Common\Console\Services\System\SystemServer;
use Common\Utils\Email\EmailHelper;
use Illuminate\Console\Command;

class HeartBeat extends command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'heart-beat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '心跳检测';

    public function handle()
    {
        $sysServer = SystemServer::server();
        /** 数据库 & Redis 连接 */
        $data = $sysServer->storeCheck();
        if ($sysServer->isError()) {
            EmailHelper::send($data->getData(), '心跳检测异常');
        }
        /** Risk风控 */
        /** Services印牛服务 */
    }
}
