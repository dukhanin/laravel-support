<?php

namespace Dukhanin\Support\Menu;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class MenuItem
{
    /**
     * @var \Dukhanin\Support\Menu\MenuCollection
     */
    protected $items;

    /**
     * Все параметры эемента меню, включая label, url
     * и все остальные дополнительные ключи-значения,
     * которые пришли в конструктор
     *
     * @var array
     */
    protected $params = [];

    /**
     * MenuItem constructor.
     *
     * @param mixed $item
     */
    public function __construct($item = [])
    {
        $item = $this->validate($item);

        $this->params = array_except($item, 'items');

        $this->items = new MenuCollection($item['items']);

        $this->items->setItemClass(static::class);
    }

    /**
     * Получить список вложенных элементов меню
     *
     * @return \Dukhanin\Support\Menu\MenuCollection
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Получить url этого элемента меню
     *
     * @return string
     */
    public function url()
    {
        if (!is_null($this->params['url'])) {
            return $this->params['url'];
        }

        if (is_array($route = $this->route)) {
            return route(array_shift($route), $route);
        } elseif (!is_null($this->route)) {
            return route($this->route);
        }

        if (is_array($action = $this->action)) {
            return action(array_shift($action), $action);
        } elseif (!is_null($this->action)) {
            return action($this->action);
        }

        return null;
    }

    /**
     * Получить html-код тега <a> для этого
     * элемента меню
     *
     * @param array $attributes
     *
     * @return string
     */
    public function a($attributes = [])
    {
        return html_tag('a', [
            'href' => $this->url(),
            'content' => $this->label(),
        ], $attributes);
    }

    /**
     * @param string|integer $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (method_exists($this, $key)) {
            return $this->{$key}();
        }

        return $this->value(array_get($this->params, $key));
    }

    /**
     * @param string|integer $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->__get($name);
    }

    /**
     * Преобразовать содержимое переменное $value
     * в конечное значение (актуально для Closure- и callable-переменных)
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function value($value)
    {
        if (is_callable($value)) {
            $value = call_user_func($value, $this);
        }

        return $value;
    }

    /**
     * Получить укомплектованный массив элемента меню
     *
     * @param $item
     *
     * @return array|string
     */
    protected function validate($item)
    {
        $this->value($item);

        if (is_string($item)) {
            $item = ['label' => $item];
        }

        return $item + [
                'active' => [get_class($this), 'itemActive'],
                'route' => null,
                'action' => null,
                'url' => null,
                'enabled' => [get_class($this), 'hasAccess'],
                'items' => $this->value(array_get($item, 'items')) ?? [],
            ];
    }

    /**
     * Метод проверки активности пункта меню по-умолчанию.
     *
     * @param MenuItem|static $item
     *
     * @return bool
     */
    public static function itemActive(MenuItem $item)
    {
        if (!is_null($item->route)) {
            return Route::current() && Route::current()->getName() == $item->route;
        }

        if (!is_null($item->action)) {
            return Route::current() && ends_with(Route::current()->getActionName(), $item->action);
        }

        if (!is_null($item->url)) {
            $currentPath = trim(Request::path(), '/');

            $path = trim(parse_url($item->url, PHP_URL_PATH), '/');

            return $currentPath == $path || starts_with($currentPath, $path);
        }

        return false;
    }

    /**
     * Метод проверки доступа роли к элементу меню
     *
     * @param MenuItem|static $item
     *
     * @return bool
     */
    public static function hasAccess(MenuItem $item)
    {
        if (!is_null($item->access) && $user = Auth::user()) {
            if (!in_array($user->role, array_merge((array)$item->access, ['root']))) {
                return false;
            }
        }

        return true;
    }
}