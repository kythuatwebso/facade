# Facades

## Install

```txt
composer require friendsofwebso/facade
```

## Use

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

/**
 * Tạo File providers.php để tải thêm dịch vụ (nếu có)
 */

// foreach ((new Filesystem())->requireOnce(__DIR__.'/providers.php') as $provider) {
//     $laraApp->registerDeferredProvider($provider);
// }

$laraApp->bootstrap();

// Đăng ký Smarty (nếu cần)
$laraApp->registerSmarty(smarty());
```

- providers.php

```php
<?php

return [
    PipelineServiceProvider::class,
];
```
