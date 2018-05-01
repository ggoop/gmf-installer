# Gmf 使用手册
## 环境准备
> Gmf 使用 Composer 来管理项目依赖。因此，在使用 Gmf 之前，请确保你的机器已经安装了 PHP7, Composer。  
* PHP >= 7.0
* OpenSSL PHP
* PHP PDO 扩展
* PHP Mbstring 扩展
* PHP XML 扩展
* PHP JSON 扩展  
* [NodeJs](https://nodejs.org/en/)，选择LTS版本进行安装。
* [Composer](https://getcomposer.org/download/)
* [Mariadb](https://downloads.mariadb.org/)

## 安装 Gmf 安装器
首先，通过使用 Composer 安装 Gmf 安装器：
```
/*使用命令行工具，执行命令：*/
composer global require "ggoop/gmf-installer"
```

## 创建应用

安装完成后，建议为应用创建一个文件夹==project==，用来存放应用和应用包的代码 ， gmf create-project 命令会在您指定的目录创建一个全新的 gmf 项目。例如， gmf create-project blog 将会创建一个名为 blog 的目录，并已安装好所有的 gmf 依赖项：  

```
[root@~project/]#
gmf create-project blog  
```

创建完应用后，进入应用目录，如

```
[root@~project/]#
cd blog
```

启动应用，在应用目录下，运行命令 php artisan serve，将启动应用.
```
[root@~project/blog/]#
php artisan serve
```
在浏览器中，输入 htp://localhost:8000，将看到默认应用，8000为默认的端口，可以指定其它端口.
```
[root@~project/blog/]#
php artisan serve --port=8000
```

修改配置文件，在应用目录，.env为应用配置文件，包括数据库、应用名称等信息
```
...
APP_NAME=应用名称
...
```
### 应用文件夹结构
* database：数据库相关
    * migrations：数据表结构目
    * preseeds：数据升级前代码目
    * postseeds：数据升级后代码目
    * seeds：测试数据填充代码目录
* resources：前端资源以及代码
    * assets
        * js：前端 Vue资源文件目录
            * vendor：应用包资源发布目录，包括其它应用包发布的资源。
            * app.js：应用启动文件
        * sass：前端样式文件目录
    * public：静态资源目录，该目录下的文件，会直接拷贝到**ghub-laravel\public\assets\vendor**目录下。
    * views
* routes：后端路由，包括API和WEB路由
* config：包含应用程序所有的配置文件
* app：应用程序的核心代码位于 app 目录内
    * Models：后端模型文件目录
    * Http：目录包含了控制器、中间件和表单请求，几乎所有的进入应用的请求的处理逻辑都被放在这里
        * Controllers：控制器目录
        * Resources：模型序列化目录
* storage：目录包含编译的 Blade 模板、基于文件的会话和文件缓存、以及框架生成的其他文件。
    * app ：目录可以用来存储应用生成的任何文件
    * framework ：目录用来存储框架生成的文件和缓存
    * logs ：目录包含应用的日志文件
* composer.json：后端代码依赖配置
* package.json：前端代码依赖配置
* .env：环境配置信息

## 创建应用包
通过 gmf create-package 命令，创建一个应用包，应命令可以指定一个项目，如 gmf create-package mypackage --project=blog 将自动在当前目录下，创建一个名为mypackage的应用包，并会自动将mypackage应用包配置在应用blog上。  
> 建议在应用的上级目录下，运行gmf命令，如blog的上级目录。
```
[root@~project/]#
gmf create-package mypackage --project=blog
```

### 自动配置
如果自动配置了应用，由可以在浏览器中，输入 http://localhost:8000/site/mypackage，可以看到应用包的名称mypackage。
### 手动配置
1.  进入应用目录，如blog目录，修改配置文件 composer.json。加入自动加载配置。
```
 "autoload":
  {
    "psr-4":
    {
      "App\\": "app/",
      "Mypackage\\":"../mypackage/src/"
    }
  },
 ```
 2. 进入应用目录/config/app.php,增加服务提供者配置。
 ```
'providers' => [
    ...
    /*DummyProviderPlaced*/
    Mypackage\ServiceProvider::class,
    ...
]
 ```
 3. 自动加载下本地应用包，在应用目录blog下，行动命令 composer dump-autoload。
```
[root@~project/blog/]#
composer dump-autoload
```

### 删除应用包
1. 直接删除应用包的目录。
2. 进入应用目录，修改composer.json的引用。
3. 进入应用目录，删除/config/app.php文件的服务提供者节点。
4. 重新自动加载类 composer dump-autoload。

### 应用包文件夹结构
* database：数据库相关
    * migrations：数据表结构目
    * preseeds：数据升级前代码目
    * postseeds：数据升级后代码目
    * seeds：测试数据填充代码目录
* resources：前端资源以及代码
    * assets
        * js：前端 Vue资源文件目录
            * components：包含了应用包所有基本组件的目录。
            * pages：包含了应用包页面目录。
            * routes：路由配置目录
            * index.js：应用包前端代码入口。
        * sass：前端样式文件目录
    * public：静态资源目录，该目录下的文件，会直接拷贝到**应用\public\assets\vendor**目录下。
    * views
* routes：后端路由，包括API和WEB路由
* src：后端代码，主要是PHP代码
    * Models：后端模型文件目录
    * Http：Http请求处理目录
        * Controllers：控制器目录
        * Resources：模型序列化目录
* composer.json：后端代码依赖配置
* package.json：前端代码依赖配置


## 数据库
### 基本配置
修改配置文件，在应用目录，.env为应用配置文件，修改数据库配置信息
```
...
DB_DATABASE=databaseName
DB_USERNAME=root
DB_PASSWORD=root
...
```
在应用目录下，运行 php artisan gmf:install --force 初始化数据库。
```
[root@~project/blog/]#
php artisan gmf:install --force
```
### 生成迁移
在应用目录下，使用命令 php artisan gmf:create-md 创建迁移。
```
[root@~project/blog/]#
php artisan gmf:create-md UserTag
```
新的迁移位于 database/migrations 目录下,--package 选项可用来指定为某个应用包创建迁移，生成的迁移，将位于应用包目录中。
```
[root@~project/blog/]#
php artisan gmf:create-md UserTag --package=packageName
```
其它更多选项


选择 | 说明
---|---
--package=packageName | 指定为某个应用包创建
--path=pathName | 为文件创建一个目录
--model | 自动创建模型，创建的模型位于Models目录中
--controller | 自动创建控制器，创建的控制器，将位于Http\Controllers 目录中
--all | 自动创建控制器和模型

## 模型
### 生成模型
在应用目录下，使用命令 php artisan gmf:create-model 创建模型，位于Models目录中。
```
[root@~project/blog/]#
php artisan gmf:create-model UserTag
```
其它更多选项


选择 | 说明
---|---
--package=packageName | 指定为某个应用包创建
--path=pathName | 为文件创建一个目录


## 控制器
### 生成控制器
在应用目录下，使用命令 php artisan gmf:create-controller 创建控制器，位于Http\Controllers目录中。
```
[root@~project/blog/]#
php artisan gmf:create-controller UserTag
```
其它更多选项


选择 | 说明
---|---
--package=packageName | 指定为某个应用包创建
--path=pathName | 为文件创建一个目录

## 路由
### 后端路由
路由配置文件位于：\routes。  
api.php为api提供路由。  
web.php为web页面提供路由。
### 前端路由
路由配置文件位于：\resources\assets\js\routes

## 前端
### 安装依赖
如果第一次安装，则需要需要命令npm install 安装对应的依赖。
```
[root@~project/blog/]#
npm install
```
### 生成页面
在应用目录下，使用命令 php artisan gmf:create-page 创建页面，位于resources\assets\js\pages目录中。
```
[root@~project/blog/]#
php artisan gmf:create-page UserTag
```
其它更多选项


选择 | 说明
---|---
--package=packageName | 指定为某个应用包创建
--path=pathName | 为文件创建一个目录

### 配置页面路由
在\resources\assets\js\routes配置文件中，增加页面路由。
```
const routeList = [
...
{
    path: '/sample/home',
    name: 'sample.home',
    component: () =>
      import ('../pages/Home/Home')
  },
  ...
];
export default routeList;
```

### 构建前端
在应用目录下，运行 php artisan gmf:publish --force ，将发布前端资源，然后运行 npm run dev 进行构建。
```
[root@~project/blog/]#
php artisan gmf:publish --force
npm run dev
```

