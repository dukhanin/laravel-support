<?php
namespace Dukhanin\Support\Tests\Menu;

use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Route;
use Dukhanin\Support\Menu\MenuCollection;
use Dukhanin\Support\Menu\MenuItem;
use Dukhanin\Support\Tests\TestCase;

class MenuItemTest extends TestCase
{
    public function testItems()
    {
        $item = new MenuItem(['items' => [1 => 'one', 2 => 'two', 3 => 'three']]);

        $this->assertInstanceOf(MenuCollection::class, $item->items());
        $this->assertCount(3, $item->items());
        $this->assertEquals('one', $item->items()[1]->label);
        $this->assertEquals('two', $item->items()[2]->label);
        $this->assertEquals('three', $item->items()[3]->label);
    }

    public function testUrl()
    {
        $item = new MenuItem;
        $this->assertNull($item->url);

        $item = new MenuItem(['url' => 'hello/world']);
        $this->assertEquals('hello/world', $item->url);

        $item = new MenuItem(['action' => 'TestController@testMethod']);
        $this->assertEquals('http://test.host/testUrl', $item->url);

        $item = new MenuItem(['action' => ['TestController@testMethod', 'testAction']]);
        $this->assertEquals('http://test.host/testUrl/testAction', $item->url);

        $item = new MenuItem(['route' => 'testRouteName']);
        $this->assertEquals('http://test.host/testUrl', $item->url);

        $item = new MenuItem(['route' => ['testRouteName', 'testAction']]);
        $this->assertEquals('http://test.host/testUrl/testAction', $item->url);
    }

    public function testA()
    {
        $item = new MenuItem(['label' => 'hello world', 'url' => '/testUrl']);

        $this->assertEquals("<a href='/testUrl'>hello world</a>", $item->a());
    }

    public function testGetSet()
    {
        $item = new MenuItem;

        $item->label = 'hello world';
        $this->assertEquals('hello world', $item->label);
    }

    public function testValue()
    {
        $item = new MenuItem([
            'label' => function () {
                return 'hello world';
            },
        ]);

        $this->assertEquals('hello world', $item->label);
    }

    public function testItemActive()
    {
        // Пункт меню без адреса не моежет быть активным
        $this->assertFalse(MenuItem::itemActive(new MenuItem));

        // Точное соответсвтие с текущим адресом
        RequestFacade::shouldReceive('path')->andReturn('hello/world')->byDefault();
        $this->assertTrue(MenuItem::itemActive(new MenuItem(['url' => 'hello/world'])));

        // Текущий адрес начинается так же как и пункт меню
        RequestFacade::shouldReceive('path')->andReturn('hello/world/world')->byDefault();
        $this->assertTrue(MenuItem::itemActive(new MenuItem(['url' => 'hello/world'])));

        // Текущий адрес НЕ начинается так же как и пункт меню
        RequestFacade::shouldReceive('path')->andReturn('hello')->byDefault();
        $this->assertFalse(MenuItem::itemActive(new MenuItem(['url' => 'hello/world'])));

        // Указание url в виде route и action
        $testRoute = $this->app->router->getRoutes()->getByName('testRouteName');
        $routeItem = new MenuItem(['route' => $testRoute->getName()]);
        $actionItem = new MenuItem(['action' => 'TestController@testMethod']);

        // Текущий route и action не соответствуют указанным у пункта меню
        $this->assertFalse(MenuItem::itemActive($routeItem));
        $this->assertFalse(MenuItem::itemActive($actionItem));

        // Текущий route и action соответствуют указанным у пункта меню
        Route::shouldReceive('current')->andReturn($testRoute)->byDefault();
        $this->assertTrue(MenuItem::itemActive($routeItem));
        $this->assertTrue(MenuItem::itemActive($actionItem));
    }
}
