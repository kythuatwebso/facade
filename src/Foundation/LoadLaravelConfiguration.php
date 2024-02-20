<?php

namespace Websovn\Facades;

use Illuminate\Config\Repository;

class LoadLaravelConfiguration
{
    public function bootstrap(LaraApp $app)
    {
        $items = [];

        $app->instance('config', $config = new Repository($items));

        // set config default
        foreach (DefaultLaravel::configs()->toArray() as $key => $value) {

            if (isset($app['config'][$key])) {
                $app['config'][$key] = array_merge(
                    $app['config'][$key],
                    $value
                );

                continue;
            }

            $app['config'][$key] = $value;
        }

        date_default_timezone_set($config->get('app.timezone', 'Asia/Ho_Chi_Minh'));

        mb_internal_encoding('UTF-8');
    }
}
