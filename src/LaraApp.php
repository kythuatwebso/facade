<?php

namespace Websovn\Facades;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LaraApp extends Container
{
    use Macroable;

    protected $basePath;
    protected $hasBeenBootstrapped  = false;
    protected $booted               = false;
    protected $bootingCallbacks     = [];
    protected $bootedCallbacks      = [];
    protected $terminatingCallbacks = [];
    protected $serviceProviders     = [];
    protected $loadedProviders      = [];
    protected $deferredServices     = [];
    protected $bootstrapPath;
    protected $appPath;
    protected $configPath;
    protected $databasePath;
    protected $langPath;
    protected $publicPath;
    protected $storagePath;
    protected $environmentPath;
    protected $environmentFile = '.env';
    protected $isRunningInConsole;
    protected $namespace;
    protected $absoluteCachePathPrefixes    = ['/', '\\'];
    protected string|array|null $useService = null;

    protected $bootstrappers = [
        \Websovn\Facades\LoadLaravelConfiguration::class,
        \Websovn\Facades\RegisterFacades::class,
        \Websovn\Facades\RegisterProviders::class,
        \Websovn\Facades\BootProviders::class,
    ];

    /**
     * @param string|array|null $useService
     * 'File', 'Cache', 'Response', 'View', 'Blade'
     * 'Crypt', 'Hash', 'Session', 'Storage', 'Validator'
     * 'Http', 'Request', 'URL', 'Date'
     */
    public function __construct($basePath = null, string|array|null $useService = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->makeStorageDirectory();

        DefaultLaravel::useService($useService);

        $this->useService = $useService;

        $this->registerBaseBindings();

        if (! is_null($useService)) {
            $this->registerBaseServiceProviders();
        }

        $this->registerCoreContainerAliases();
    }

    /**
     * Đăng ký Facade vào smarty
     *
     * Sử dụng: `{Facade\Date::now()}`
     */
    public function registerSmarty($smarty_class_object)
    {
        $prefix     = 'Facade';
        $method     = 'registerClass';
        $useService = is_null($useService = $this->useService)
            ? null
            : (array) $useService;

        $servicesDefault = [
            'Arr'      => \Illuminate\Support\Arr::class,
            'Str'      => \Illuminate\Support\Str::class,
            'Number'   => \Illuminate\Support\Number::class,
            'Date'     => \Illuminate\Support\Facades\Date::class,
            'Http'     => \Illuminate\Support\Facades\Http::class,
            'Redirect' => \Illuminate\Support\Facades\Redirect::class,
            'Request'  => \Illuminate\Support\Facades\Request::class,
            'URL'      => \Illuminate\Support\Facades\URL::class,
        ];

        $classRegisters = [];
        if (! is_null($useService)) {
            $classRegisters = array_merge(
                $servicesDefault,
                DefaultLaravel::facades()
            );
        }

        if (count($classRegisters) > 0) {
            foreach ($classRegisters as $name => $class) {
                $smarty_class_object->{$method}(
                    "{$prefix}\\{$name}",
                    $class
                );
            }
        }
    }

    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));

        // $this->register(new LogServiceProvider($this));

        $this->requestFacade(\Illuminate\Http\Request::capture());

        $this->register(new RoutingServiceProvider($this));
    }

    protected function requestFacade($request)
    {
        $this->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();
    }

    protected function makeStorageDirectory()
    {
        $files = new Filesystem();

        $directories = [
            $this->basePath.'/storage/cache/views',
            $this->basePath.'/storage/cache/sessions',
        ];

        foreach ($directories as $dir) {
            if (! $files->isDirectory($dir)) {
                $files->makeDirectory($dir, 0755, true);
            }
        }
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.storage', $this->storagePath());

        $this->useLangPath(
            $this->basePath('lang')
        );
    }

    public function path($path = '')
    {
        return $this->joinPaths($this->appPath ?: $this->basePath('app'), $path);
    }

    public function useLangPath($path)
    {
        $this->langPath = $path;

        $this->instance('path.lang', $path);

        return $this;
    }

    public function configPath($path = '')
    {
        return $this->joinPaths($this->configPath ?: $this->basePath('config'), $path);
    }

    public function useConfigPath($path)
    {
        $this->configPath = $path;

        $this->instance('path.config', $path);

        return $this;
    }

    public function storagePath($path = '')
    {
        if (isset($_ENV['LARAVEL_STORAGE_PATH'])) {
            return $this->joinPaths($this->storagePath ?: $_ENV['LARAVEL_STORAGE_PATH'], $path);
        }

        return $this->joinPaths($this->storagePath ?: $this->basePath('storage'), $path);
    }

    public function useStoragePath($path)
    {
        $this->storagePath = $path;

        $this->instance('path.storage', $path);

        return $this;
    }

    public function isLocal()
    {
        return $this['env'] === 'local';
    }

    public function isProduction()
    {
        return $this['env'] === 'production';
    }

    public function hasDebugModeEnabled()
    {
        return (bool) $this['config']->get('app.debug');
    }

    public function basePath($path = '')
    {
        return $this->joinPaths($this->basePath, $path);
    }

    public function joinPaths($basePath, $path = '')
    {
        return \Illuminate\Filesystem\join_paths($basePath, $path);
    }

    public function bootstrap()
    {
        if (! $this->hasBeenBootstrapped()) {
            $this->bootstrapWith($this->bootstrappers());
        }
    }

    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);
    }

    protected function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    public function registerConfiguredProviders()
    {
        $providers = Collection::make(DefaultLaravel::providers())
            ->partition(fn ($provider) => str_starts_with($provider, 'Illuminate\\'));

        (new ProviderRepository($this, new Filesystem(), $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
    }

    protected function cachePath($path = '')
    {
        $cachePath = $this->basePath.'/storage/cache';

        if ($path) {
            return $cachePath.DIRECTORY_SEPARATOR.$path;
        }

        return $cachePath;
    }

    protected function getCachedServicesPath()
    {
        return $this->cachePath('services.php');
    }

    public function getCachedPackagesPath()
    {
        return $this->cachePath('packages.php');
    }

    public function getCachedConfigPath()
    {
        return $this->cachePath('config.php');
    }

    public function configurationIsCached()
    {
        return is_file($this->getCachedConfigPath());
    }

    public function addAbsoluteCachePathPrefix($prefix)
    {
        $this->absoluteCachePathPrefixes[] = $prefix;

        return $this;
    }

    public function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message, null, 0, $headers);
        }

        throw new HttpException($code, $message, null, $headers);
    }

    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }

    public function terminate()
    {
        $index = 0;

        while ($index < count($this->terminatingCallbacks)) {
            $this->call($this->terminatingCallbacks[$index]);

            $index++;
        }
    }

    public function providerIsLoaded(string $provider)
    {
        return isset($this->loadedProviders[$provider]);
    }

    public function getDeferredServices()
    {
        return $this->deferredServices;
    }

    public function setDeferredServices(array $services)
    {
        $this->deferredServices = $services;
    }

    public function isDeferredService($service)
    {
        return isset($this->deferredServices[$service]);
    }

    public function provideFacades($namespace)
    {
        AliasLoader::setFacadeNamespace($namespace);
    }

    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    public function currentLocale()
    {
        return $this->getLocale();
    }

    public function getFallbackLocale()
    {
        return $this['config']->get('app.fallback_locale');
    }

    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        $this['translator']->setLocale($locale);
    }

    public function setFallbackLocale($fallbackLocale)
    {
        $this['config']->set('app.fallback_locale', $fallbackLocale);

        $this['translator']->setFallback($fallbackLocale);
    }

    public function resolveProvider($provider)
    {
        return new $provider($this);
    }

    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    public function flush()
    {
        parent::flush();

        $this->buildStack                     = [];
        $this->loadedProviders                = [];
        $this->bootedCallbacks                = [];
        $this->bootingCallbacks               = [];
        $this->deferredServices               = [];
        $this->reboundCallbacks               = [];
        $this->serviceProviders               = [];
        $this->resolvingCallbacks             = [];
        $this->terminatingCallbacks           = [];
        $this->beforeResolvingCallbacks       = [];
        $this->afterResolvingCallbacks        = [];
        $this->globalBeforeResolvingCallbacks = [];
        $this->globalResolvingCallbacks       = [];
        $this->globalAfterResolvingCallbacks  = [];
    }

    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($this->basePath('composer.json')), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath($this->basePath($pathChoice))) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new \RuntimeException('Unable to detect application namespace.');
    }

    public function register($provider, $force = false)
    {
        if (($registered = $this->getProvider($provider)) && ! $force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        $provider->register();

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons as $key => $value) {
                $key = is_int($key) ? $value : $key;

                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->isBooted()) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    protected function markAsRegistered($provider)
    {
        $this->serviceProviders[] = $provider;

        $this->loadedProviders[get_class($provider)] = true;
    }

    public function getProvider($provider)
    {
        return array_values($this->getProviders($provider))[0] ?? null;
    }

    public function getProviders($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return Arr::where($this->serviceProviders, fn ($value) => $value instanceof $name);
    }

    public function addDeferredServices(array $services)
    {
        $this->deferredServices = array_merge($this->deferredServices, $services);
    }

    public function loadDeferredProviders()
    {
        // We will simply spin through each of the deferred providers and register each
        // one and boot them if the application has booted. This should make each of
        // the remaining services available to this application for immediate use.
        foreach ($this->deferredServices as $service => $provider) {
            $this->loadDeferredProvider($service);
        }

        $this->deferredServices = [];
    }

    public function loadDeferredProvider($service)
    {
        if (! $this->isDeferredService($service)) {
            return;
        }

        $provider = $this->deferredServices[$service];

        // If the service provider has not already been loaded and registered we can
        // register it with the application and remove the service from this list
        // of deferred services, since it will already be loaded on subsequent.
        if (! isset($this->loadedProviders[$provider])) {
            $this->registerDeferredProvider($provider, $service);
        }
    }

    public function registerDeferredProvider($provider, $service = null)
    {
        // Once the provider that provides the deferred service has been registered we
        // will remove it from our local list of the deferred services with related
        // providers so that this container does not try to resolve it out again.
        if ($service) {
            unset($this->deferredServices[$service]);
        }

        $this->register($instance = new $provider($this));

        if (! $this->isBooted()) {
            $this->booting(function () use ($instance) {
                $this->bootProvider($instance);
            });
        }
    }

    public function make($abstract, array $parameters = [])
    {
        $this->loadDeferredProviderIfNeeded($abstract = $this->getAlias($abstract));

        return parent::make($abstract, $parameters);
    }

    protected function resolve($abstract, $parameters = [], $raiseEvents = true)
    {
        $this->loadDeferredProviderIfNeeded($abstract = $this->getAlias($abstract));

        return parent::resolve($abstract, $parameters, $raiseEvents);
    }

    protected function loadDeferredProviderIfNeeded($abstract)
    {
        if ($this->isDeferredService($abstract) && ! isset($this->instances[$abstract])) {
            $this->loadDeferredProvider($abstract);
        }
    }

    public function bound($abstract)
    {
        return $this->isDeferredService($abstract) || parent::bound($abstract);
    }

    public function boot()
    {
        if ($this->isBooted()) {
            return;
        }

        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    protected function bootProvider(ServiceProvider $provider)
    {
        $provider->callBootingCallbacks();

        if (method_exists($provider, 'boot')) {
            $this->call([$provider, 'boot']);
        }

        $provider->callBootedCallbacks();
    }

    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $callback($this);
        }
    }

    public function isBooted()
    {
        return $this->booted;
    }

    protected function fireAppCallbacks(array &$callbacks)
    {
        $index = 0;

        while ($index < count($callbacks)) {
            $callbacks[$index]($this);

            $index++;
        }
    }

    protected function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    public function registerCoreContainerAliases()
    {
        foreach (DefaultLaravel::aliases() as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    public function useAppPath($path)
    {
        $this->appPath = $path;

        $this->instance('path', $path);

        return $this;
    }
}
