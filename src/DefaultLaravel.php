<?php

namespace Websovn\Facades;

use Illuminate\Support\Str;

class DefaultLaravel
{
    public const FACADE   = 'facade';
    public const PROVIDER = 'provider';
    public const ALIAS    = 'alias';

    protected static $servicesAllow = [
        'File' => [
            self::PROVIDER => \Illuminate\Filesystem\FilesystemServiceProvider::class,
            self::FACADE   => \Illuminate\Support\Facades\File::class,
            self::ALIAS    => [
                'files'            => [\Illuminate\Filesystem\Filesystem::class],
                'filesystem'       => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
                'filesystem.disk'  => [\Illuminate\Contracts\Filesystem\Filesystem::class],
                'filesystem.cloud' => [\Illuminate\Contracts\Filesystem\Cloud::class],
            ],
        ],
        'Cache' => [
            self::PROVIDER => [\Illuminate\Cache\CacheServiceProvider::class, \Illuminate\Filesystem\FilesystemServiceProvider::class],
            self::FACADE   => \Illuminate\Support\Facades\Cache::class,
            self::ALIAS    => [
                'cache'       => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
                'cache.store' => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class, \Psr\SimpleCache\CacheInterface::class],
            ],
        ],
        'Response' => [
            self::PROVIDER => [\Illuminate\View\ViewServiceProvider::class, \Illuminate\Filesystem\FilesystemServiceProvider::class],
            self::FACADE   => \Illuminate\Support\Facades\Response::class,
            self::ALIAS    => [
                'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
            ],
        ],
        'View' => [
            self::PROVIDER => [\Illuminate\View\ViewServiceProvider::class, \Illuminate\Filesystem\FilesystemServiceProvider::class],
            self::FACADE   => \Illuminate\Support\Facades\View::class,
            self::ALIAS    => [
                'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
            ],
        ],
        'Blade' => [
            self::PROVIDER => [\Illuminate\View\ViewServiceProvider::class, \Illuminate\Filesystem\FilesystemServiceProvider::class],
            self::FACADE   => \Illuminate\Support\Facades\Blade::class,
            self::ALIAS    => [
                'blade.compiler' => [\Illuminate\View\Compilers\BladeCompiler::class],
            ],
        ],
        'Crypt' => [
            self::PROVIDER => \Illuminate\Encryption\EncryptionServiceProvider::class,
            self::FACADE   => \Illuminate\Support\Facades\Crypt::class,
            self::ALIAS    => [
                'encrypter' => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\StringEncrypter::class],
            ],
        ],
        'Hash' => [
            self::PROVIDER => \Illuminate\Hashing\HashServiceProvider::class,
            self::FACADE   => \Illuminate\Support\Facades\Hash::class,
            self::ALIAS    => [
                'hash'        => [\Illuminate\Hashing\HashManager::class],
                'hash.driver' => [\Illuminate\Contracts\Hashing\Hasher::class],
            ],
        ],
        'Session' => [
            self::PROVIDER => [\Illuminate\Session\SessionServiceProvider::class, \Illuminate\Filesystem\FilesystemServiceProvider::class],
            self::FACADE   => \Illuminate\Support\Facades\Session::class,
            self::ALIAS    => [
                'session'       => [\Illuminate\Session\SessionManager::class],
                'session.store' => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
            ],
        ],
        'Storage' => [
            self::PROVIDER => \Illuminate\Filesystem\FilesystemServiceProvider::class,
            self::FACADE   => \Illuminate\Support\Facades\Storage::class,
            self::ALIAS    => [],
        ],
        'Validator' => [
            self::PROVIDER => [
                \Illuminate\Validation\ValidationServiceProvider::class,
                \Illuminate\Translation\TranslationServiceProvider::class,
                \Illuminate\Filesystem\FilesystemServiceProvider::class,
            ],
            self::FACADE => \Illuminate\Support\Facades\Validator::class,
            self::ALIAS  => [
                'validator'  => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
                'translator' => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
            ],
        ],
    ];

    protected static $providers = [];
    protected static $facades   = [];
    protected static $aliases   = [];

    public static function useService(string|array|null $services)
    {
        if (is_null($services)) {
            $services = [];
        }

        if (is_string($services)) {
            $services = [$services];
        }

        $collect = collect(static::$servicesAllow);

        foreach ($services as $service) {

            $collect
                ->when(! in_array('*', $services), fn ($collect) => $collect->filter(fn ($items, $key) => str($key)->lower()->exactly(strtolower($service))))
                ->each(function ($items, $key) {

                    if (is_array($providers = $items[self::PROVIDER])) {
                        static::$providers = array_merge(
                            static::$providers,
                            $providers
                        );
                    } else {
                        static::$providers[] = $providers;
                    }

                    if (is_array($facades = $items[self::FACADE])) {
                        static::$facades = array_merge(
                            static::$facades,
                            $facades
                        );
                    } else {
                        static::$facades[$key] = $facades;
                    }

                    if (is_array($aliases = $items[self::ALIAS])) {
                        static::$aliases = array_merge(
                            static::$aliases,
                            $aliases
                        );
                    } else {
                        static::$aliases[] = $aliases;
                    }
                });
        }

        static::$providers = collect(static::$providers)->unique()->values()->toArray();
        static::$facades   = collect(static::$facades)->unique()->toArray();
    }

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
        return static::$providers;
    }

    public static function facades()
    {
        return array_merge(static::$facades, [
            'App'    => \Illuminate\Support\Facades\App::class,
            'Config' => \Illuminate\Support\Facades\Config::class,
        ]);
    }

    public static function aliases()
    {
        return array_merge(
            [
                'app'    => [self::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
                'config' => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
            ],
            static::$aliases
        );
    }
}
