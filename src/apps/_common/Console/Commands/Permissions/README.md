- 首先在权限管理-菜单管理 执行同步菜单
- 再在本地环境执行 `php artisan sync-menu:save-menu-file`生成菜单文件`storage/permissions/menu.json`并同步到git
- 正式环境发布代码后进入`Docker`执行 `php artisan init:config menu`同步菜单到正式环境
