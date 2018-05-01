## 通过 gmf 安装器
首先，通过使用 Composer 安装 Gmf 安装器：
```
composer global require "ggoop/gmf-installer"
```

### 创建应用
安装完成后， gmf create-project 命令会在您指定的目录创建一个全新的 gmf 项目。例如， gmf create-project blog 将会创建一个名为 blog 的目录，并已安装好所有的 gmf 依赖项：

```
gmf create-project blog
```

创建完应用后，进入应用目录，如
```
cd blog
```

启动应用，在应用目录下，运行命令 php artisan serve，将启动应用.
```
php artisan serve
```
在浏览器中，输入 htp://localhost:8000，将看到默认应用，8000为默认的端口，可以指定其它端口.
```
php artisan serve --port=8000
```

### 创建应用包
通过 gmf create-package 命令，创建一个应用包，应命令可以指定一个项目，如 gmf create-package mypackage --project=blog 将自动在当前目录下，创建一个名为mypackage的应用包，并会自动将mypackage应用包配置在应用blog上。  
> 建议在应用的上级目录下，运行gmf命令，如blog的上级目录。
```
gmf create-package mypackage --project=blog
```

#### 自动配置
如果自动配置了应用，由可以在浏览器中，输入 http://localhost:8000/site/mypackage，可以看到应用包的名称mypackage。
#### 手动配置
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
composer dump-autoload
```

#### 删除应用包
1. 直接删除应用包的目录。
2. 进入应用目录，修改composer.json的引用。
3. 进入应用目录，删除/config/app.php文件的服务提供者节点。
4. 重新自动加载类 composer dump-autoload。