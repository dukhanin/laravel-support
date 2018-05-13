<?php
namespace Dukhanin\Support\Menu;

use Dukhanin\Support\Traits\ClearableCollection;
use Illuminate\Support\Collection;

class MenuCollection extends Collection
{
    use ClearableCollection;

    /**
     * Класс элементов меню, используемы в этой
     * и внутренних коллекциях
     *
     * @var string
     */
    protected $itemClass = MenuItem::class;

    /**
     * MenuCollection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * @param string|integer $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $keySegments = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments) {
            if (! $this->offsetExists($firstKeySegment)) {
                $this->put($firstKeySegment, []);
            }

            $this->offsetGet($firstKeySegment)->items()->offsetSet(implode('.', $keySegments), $value);
        } else {
            parent::offsetSet($key, $this->resolve($value, $key));
        }
    }

    /**
     * @param string|integer $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        $keySegments = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            return parent::offsetGet($firstKeySegment)->items()->offsetExists(implode('.', $keySegments));
        } else {
            return parent::offsetExists($key);
        }
    }

    /**
     * @param string|integer $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        $keySegments = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            return parent::offsetGet($firstKeySegment)->items()->offsetGet(implode('.', $keySegments));
        } else {
            $item = parent::offsetGet($key);
            $item->set('key', $key);

            return $item;
        }
    }

    /**
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $keySegments = explode('.', $key);
        $firstKeySegment = array_shift($keySegments);

        if ($keySegments && $this->offsetExists($firstKeySegment)) {
            parent::offsetGet($firstKeySegment)->items()->offsetUnset(implode('.', $keySegments));
        } else {
            parent::offsetUnset($key);
        }
    }

    /**
     * Установить класс для элементов меню
     *
     * @param string $class
     */
    public function setItemClass($class)
    {
        $this->itemClass = $class;
    }

    /**
     * Получить объект элемента меню на основе данных
     * из $item
     *
     * @param mixed $item
     * @param string|integer $key
     *
     * @return mixed
     */
    protected function resolve($item, $key)
    {
        $className = $this->itemClass;

        if ($item instanceof $className) {
            return $item;
        }

        return new $className($item);
    }

    /**
     * Отфильтровать текущий уровень меню, оставив в нем только
     * enabled-элементы
     *
     * @return static
     */
    public function enabled()
    {
        return $this->filter(function ($item) {
            return $item->enabled;
        });
    }

    /**
     * Есть ли активные элементы?
     *
     * @return bool
     */
    public function hasActive()
    {
        return $this->contains(function ($item) {
            return $item->active || $item->items()->hasActive();
        });
    }

    /**
     * Есть ли enabled-элементы?
     *
     * @return bool
     */
    public function hasEnabled()
    {
        return $this->contains(function ($item) {
            return $item->enabled || $item->items()->hasEnabled();
        });
    }

    /**
     * Добавить элемент меню в начала этой ветки
     *
     * @param mixed $value
     * @param string|integer|null $key
     *
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        return parent::prepend($this->resolve($value, $key), $key);
    }

    /**
     * Добавить элемент меню $item в эту ветку с ключем $key
     * до элемента с ключем $keyBefore
     *
     * Если $keyBefore не указан - элемент добавляется в начало.
     *
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $keyBefore
     *
     * @return $this|\Dukhanin\Support\Menu\MenuCollection
     */
    public function before($key, $value, $keyBefore = null)
    {
        if (is_null($keyBefore)) {
            return $this->prepend($value, $key);
        }

        $keySegments = explode('.', $keyBefore);
        $lastKey = array_pop($keySegments);
        $nestedKey = implode('.', $keySegments);

        if ($nestedKey && $this->offsetExists($nestedKey)) {
            return $this->offsetGet($nestedKey)->items()->before($key, $value, $lastKey);
        }

        $key = str_replace('.', '_', $key);
        $value = $this->resolve($value, $key);
        $this->items = array_before($this->items, $key, $value, $keyBefore);

        return $this;
    }

    /**
     * Добавить элемент меню $item в эту ветку с ключем $key
     * после элемента с ключем $keyBefore
     *
     * Если $keyBefore не указан - элемент добавляется в конец.
     *
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $keyAfter
     *
     * @return $this
     */
    public function after($key, $value, $keyAfter = null)
    {
        if (is_null($keyAfter)) {
            return $this->put($key, $value);
        }

        $keySegments = explode('.', $keyAfter);
        $lastKey = array_pop($keySegments);
        $nestedKey = implode('.', $keySegments);

        if ($nestedKey && $this->offsetExists($nestedKey)) {
            return $this->offsetGet($nestedKey)->items()->after($key, $value, $lastKey);
        }

        $key = str_replace('.', '_', $key);
        $value = $this->resolve($value, $key);
        $this->items = array_after($this->items, $key, $value, $keyAfter);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function chunk($size)
    {
        $chunks = collect();

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks->push(new self($chunk));
        }

        return $chunks;
    }
}

