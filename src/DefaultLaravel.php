<?php

namespace Websovn\Facades;

use Illuminate\Support\Str;

class DefaultLaravel
{
    public static function configs()
    {
        return collect([
            'app' => [
                'name'            => env('APP_NAME', 'Laravel'),
                'env'             => env('APP_ENV', 'production'),
                'debug'           => (bool) env('APP_DEBUG', false),
                'url'             => env('APP_URL', 'http://localhost'),
                'asset_url'       => env('ASSET_URL'),
                'timezone'        => 'Asia/Ho_Chi_Minh',
                'locale'          => 'vi',
                'fallback_locale' => 'vi',
                'faker_locale'    => 'vi_VN',
                'key'             => 'base64:PPgGcyM5MP6Wg38E3hEpV5Y/9wVFOucBpeqshdGuYi4=',
                'cipher'          => 'AES-256-CBC',
                'maintenance'     => ['driver' => 'file'],
                'providers'       => self::providers(),
                'aliases'         => self::aliases(),
            ],
            'cache' => [
                'default' => 'file',
                'stores'  => [
                    'file' => [
                        'driver'    => 'file',
                        'path'      => storage_path('cache'),
                        'lock_path' => storage_path('cache'),
                    ],
                ],
                'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_',
            ],
            'session' => [
                'driver'          => 'file',
                'lifetime'        => 120,
                'expire_on_close' => false,
                'encrypt'         => false,
                'files'           => storage_path('cache/sessions'),
                'connection'      => null,
                'table'           => 'sessions',
                'store'           => null,
                'lottery'         => [2, 100],
                'cookie'          => 'laravel_session',
                'path'            => '/',
                'domain'          => null,
                'secure'          => null,
                'http_only'       => true,
                'same_site'       => 'lax',
                'partitioned'     => false,
            ],
            'filesystems' => [
                'default' => env('FILESYSTEM_DISK', 'local'),
                'disks'   => [
                    'local' => [
                        'driver' => 'local',
                        'root'   => app()->basePath(),
                        'throw'  => false,
                    ],
                    'public' => [
                        'driver'     => 'local',
                        'root'       => app()->basePath(),
                        'url'        => env('APP_URL').'/storage',
                        'visibility' => 'public',
                        'throw'      => false,
                    ],
                ],
            ],
            'view' => [
                'paths' => [
                    app()->basePath('views'),
                ],
                'compiled' => env(
                    'VIEW_COMPILED_PATH',
                    realpath(storage_path('cache/views'))
                ),
            ],
        ]);
    }

    public static function providers()
    {
        return [
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Cookie\CookieServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
            \Illuminate\Encryption\EncryptionServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Session\SessionServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            \Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
        ];
    }

    public static function facades()
    {
        return [
            'App'       => \Illuminate\Support\Facades\App::class,
            'Config'    => \Illuminate\Support\Facades\Config::class,
            'File'      => \Illuminate\Support\Facades\File::class,
            'Cache'     => \Illuminate\Support\Facades\Cache::class,
            'Cookie'    => \Illuminate\Support\Facades\Cookie::class,
            'Redirect'  => \Illuminate\Support\Facades\Redirect::class,
            'Request'   => \Illuminate\Support\Facades\Request::class,
            'Response'  => \Illuminate\Support\Facades\Response::class,
            'View'      => \Illuminate\Support\Facades\View::class,
            'Blade'     => \Illuminate\Support\Facades\Blade::class,
            'Crypt'     => \Illuminate\Support\Facades\Crypt::class,
            'Date'      => \Illuminate\Support\Facades\Date::class,
            'Hash'      => \Illuminate\Support\Facades\Hash::class,
            'File'      => \Illuminate\Support\Facades\File::class,
            'Http'      => \Illuminate\Support\Facades\Http::class,
            'Js'        => \Illuminate\Support\Js::class,
            'Session'   => \Illuminate\Support\Facades\Session::class,
            'Storage'   => \Illuminate\Support\Facades\Storage::class,
            'URL'       => \Illuminate\Support\Facades\URL::class,
            'Validator' => \Illuminate\Support\Facades\Validator::class,
        ];
    }

    public static function aliases()
    {
        return [
            'app'              => [self::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
            'auth.driver'      => [\Illuminate\Contracts\Auth\Guard::class],
            'config'           => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            'files'            => [\Illuminate\Filesystem\Filesystem::class],
            'filesystem'       => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
            'filesystem.disk'  => [\Illuminate\Contracts\Filesystem\Filesystem::class],
            'filesystem.cloud' => [\Illuminate\Contracts\Filesystem\Cloud::class],
            'cache'            => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
            'cache.store'      => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class, \Psr\SimpleCache\CacheInterface::class],
            'cookie'           => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
            'redirect'         => [\Illuminate\Routing\Redirector::class],
            'request'          => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
            'view'             => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
            'blade.compiler'   => [\Illuminate\View\Compilers\BladeCompiler::class],
            'encrypter'        => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\StringEncrypter::class],
            'hash'             => [\Illuminate\Hashing\HashManager::class],
            'hash.driver'      => [\Illuminate\Contracts\Hashing\Hasher::class],
            'session'          => [\Illuminate\Session\SessionManager::class],
            'session.store'    => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
            'url'              => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
            'validator'        => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
            'translator'       => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
        ];
    }
}
