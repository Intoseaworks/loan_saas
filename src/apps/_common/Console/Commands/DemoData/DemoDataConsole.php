<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\DemoData;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoDataConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo-data:run {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成演示数据';

    /**
     * @return void
     */
    public function handle()
    {
        if (app()->environment() !== 'local') {
            $this->error("改命令只能在`local`环境执行");
            return;
        }

        // 重置数据
        $reset = $this->option('reset');

        if ($reset) {
            if (!glob(storage_path('demo-sql/backup/*.zip'))) {
                if (!$this->confirm("没有备份的SQL,是否需要重置数据库")) {
                    $this->info("取消重置数据库");
                    return;
                }
            }

            $database = $this->getDatabaseName();
            $sqls = DB::select("select CONCAT('truncate TABLE ',table_schema,'.',TABLE_NAME, ';') c from INFORMATION_SCHEMA.TABLES where  table_schema in ('{$database}');");

            foreach ($sqls as $sql) {
                DB::statement(current($sql));
            }
            $this->info("重置数据库完成,请手动把`storage/demo-sql/backup/*.zip`导入到数据库");

        } else {
            $this->call('backup:clean');
            $this->call('backup:run', ['--only-db' => true]);
            $this->info("生成演示数据完成,请手动把`storage/demo-sql/backup/*.zip`文件加入到git");
        }
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return DB::connection()->getDatabaseName();
    }
}
