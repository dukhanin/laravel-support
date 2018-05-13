<?php
namespace Dukhanin\Support\Tests;

use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Route;

class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = new Application;

        $app->singleton('request', function () {
            $request = new Request();

            $request->headers->set('HOST', 'test.host');

            return $request;
        });

        $app->singleton('router', function () {
            $router = new Router(new Dispatcher);

            $router->name('testRouteName')->get('testUrl/{action?}', 'TestController@testMethod');

            return $router;
        });

        $app->singleton(UrlGeneratorContract::class, function ($app) {
            return new UrlGenerator($app->router->getRoutes(), $app->request);
        });

        $app->bind('url', UrlGeneratorContract::class);

        Route::setFacadeApplication($app);

        return $app;
    }
}
