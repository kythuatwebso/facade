<?php

namespace Websovn\Facades;

class RegisterProviders
{
    public function bootstrap(LaraApp $app)
    {
        $app->registerConfiguredProviders();
    }
}
