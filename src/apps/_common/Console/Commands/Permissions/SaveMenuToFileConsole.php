<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Permissions;

use Common\Services\Rbac\Models\Menu;
use Illuminate\Console\Command;

class SaveMenuToFileConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-menu:save-menu-file';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '把`rbac_menus`表数据保存到文件';


    public function handle()
    {
        $path = storage_path('permissions/menu.json');
        if (!file_exists($path)) {
            $dir = pathinfo($path)['dirname'];
            mkdir($dir, 0777, true);
        }

        $menus = Menu::get()->toArray();
        $formatMenus = [];
        foreach ($menus as $menu) {
            $formatMenus[] = [
                'key' => $menu['route'],
                'parent' => str_replace('admin.', '', $menu['parent_path']),
                'sort' => $menu['sort'],
                'name' => $menu['name'],
            ];
        }

        file_put_contents($path, json_encode($formatMenus, JSON_UNESCAPED_UNICODE));
        $this->info("文件同步成功,注意提交`{$path}`文件到git");
    }

}
