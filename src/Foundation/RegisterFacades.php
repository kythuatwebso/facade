<?php

namespace Websovn\Facades;

use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    public function bootstrap(LaraApp $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(DefaultLaravel::facades())->register();
    }
}
