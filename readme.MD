# Facades

## Install

```txt
composer require friendsofwebso/facade
```

## Use

- Paste to end file bootstrap.php
- [Old Document](https://github.com/kythuatwebso/facade/tree/f9d35378700b725341a7dd4a4d9cf62f72e40b0e)

```php
use Illuminate\Filesystem\Filesystem;
use Websovn\Facades\LaraApp;

require_once 'vendor/autoload.php';

$laraApp = new LaraApp(
    dirname(__DIR__),
    [
        'File',
        'Response',
        'Storage',
        'Validator',
        'Http',
        'Request',
        'URL',
        'Date',
    ]
);

// Đăng ký Smarty (nếu cần)
$laraApp->withSmartyRegister();

/**
 * Tạo File providers.php để tải thêm dịch vụ (nếu có)
 */

// foreach ((new Filesystem())->requireOnce(__DIR__.'/providers.php') as $provider) {
//     $laraApp->registerDeferredProvider($provider);
// }

$laraApp->bootstrap();
```

- providers.php

```php
<?php

return [
    PipelineServiceProvider::class,
];
```
