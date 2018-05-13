<?php

namespace Dukhanin\Support\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Dukhanin\Support\HTMLGenerator;
use TrueBV\Punycode;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap service
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(HTMLGenerator::class);

        $this->app->singleton(Punycode::class);
    }
}
