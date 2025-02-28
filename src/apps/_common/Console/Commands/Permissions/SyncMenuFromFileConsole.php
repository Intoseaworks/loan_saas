<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Permissions;

use Common\Services\Rbac\Models\Menu;
use Common\Services\Rbac\Utils\Helper;
use Illuminate\Console\Command;

class SyncMenuFromFileConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-menu:sync-from-file {--isInit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '从`storage/permissions/menu.json`同步菜单到`rbac_menus`表';


    public function handle()
    {
        // 如果是初始化，判断是否已有目录，有则不处理
        $isInit = $this->option('isInit');
        if ($isInit && (new Menu())->first()) {
            return true;
        }

        $path = storage_path('permissions/menu.json');
        if (!file_exists($path)) {
            $this->error($path . ' 文件不存在,无法同步');
            return;
        }
        $menuJson = file_get_contents($path);
        $menus = json_decode($menuJson, true);
        if (!$menus) {
            $this->error('菜单不存在');
            return;
        }

        // 同步菜单
        (new Menu())->syncMenu($menus);

        // 同步权限
        $this->call('rbac:permission:push', [
            'module' => Helper::getGuardName(),
        ]);

        $this->info('同步菜单成功');
    }

}
