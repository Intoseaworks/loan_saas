# loan_saas
贷款项目，目标国外用户，类似国内的第三方贷款

# lumen-app

## 公共文档
- [GIT使用规范](https://github.com/YunhanTech/overview/blob/master/rule/git.md)
- [PHP开发规范](https://github.com/YunhanPHP/overview/blob/master/dev/rule.md)
- [Lumen最佳实践](https://github.com/YunhanPHP/overview/blob/master/lumen/lumen.md)
    - [开发环境集成](https://github.com/YunhanPHP/lumen-require-dev)
    - [生产环境集成](https://code.aliyun.com/jqb-php/lumen-require)：暂时仅供内部使用
## 项目文档 
- [composer包说明](docs/composer.md)

# Lumen 开发环境集成
## 注意事项
- windows环境下将 /dev/.htaccess 文件复制到 public 目录下面

## 常用命令
- swagger：API文档
    - 部署：php artisan swagger-lume:publish
    - json：php artisan swagger-lume:generate
- ddoc：数据库字典
    - 部署：php artisan vendor:publish
- ide-helper
    ````
    php artisan ide-helper:generate
    php artisan ide-helper:meta
    
    # 生成model注释
    php artisan ide-helper:models -W

## 集成模块
- barryvdh/laravel-ide-helper: 用于智能提示，生成`model`注释
- doctrine/dbal: 用于`laravel-ide-helper`生成`model`时使用
- laravelista/lumen-vendor-publish: 用于兼容命令 `php artisan vendor:publish`
- phan/phan: 用于静态代码检测
- yunhanphp/lumen-dev-db-doc: 用于查看数据库文档
- yunhanphp/lumen-dev-yaml-swagger: 用于写接口文档和测试用例
