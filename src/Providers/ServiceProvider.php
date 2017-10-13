<?php

namespace Dukhanin\Support\Providers;

use Dukhanin\Support\HTMLGenerator;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->app->singleton(HTMLGenerator::class);
    }
}
