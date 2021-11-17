### 安装

```shell
composer require church/monitor
```

### 配置

在`config/process.php`中加入以下配置。

```php
<?php

use Church\Monitor\UDPWorker;

return [
    //...
    'status-monitor' => [
       'enable' => true,
       'handler' => UDPWorker::class,
       'constructor' => [
           'host' => '127.0.0.1',
           'port' => 3000,
           'interval' => 10,
           'site' => 'test.com',
       ]
   ]
]
```

### 启动

```shell
php bin/start start
```

