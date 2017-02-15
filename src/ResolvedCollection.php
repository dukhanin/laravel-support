<?php
namespace Dukhanin\Support;

use Illuminate\Support\Collection;
use Closure;
use ArrayIterator;

class ResolvedCollection extends Collection
{

    protected $resolverOnGet;

    protected $resolverOnSet;

    protected $resolverOnIteration;

    protected $touched = false;


    public function __construct($items = [ ])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }


    public function raw()
    {
        return collect($this->items);
    }


    public function resolved()
    {
        return collect($this->items)->map(function (&$item, $key) {
            return $this->resolveItemOnIteration($key, $this->resolveItemOnGet($key, $item));
        });
    }


    public function touch()
    {
        $this->touched = true;
    }


    public function touched()
    {
        return $this->touched;
    }


    public function offsetGet($key)
    {
        return $this->resolveItemOnGet($key, parent::offsetGet($key));
    }


    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $this->resolveItemOnSet($key, $value));

        $this->touch();
    }


    public function offsetUnset($key)
    {
        parent::offsetUnset($key);

        $this->touch();
    }


    public function prepend($value, $key = null)
    {
        $value = $this->resolveItemOnSet($key, $value);

        return parent::prepend($value, $key);
    }


    public function before($key, $value, $beforeKey = null)
    {
        array_before($this->items, $key, $this->resolveItemOnSet($key, $value), $beforeKey);

        $this->touch();

        return $this;
    }


    public function after($key, $value, $afterKey = null)
    {
        array_after($this->items, $key, $this->resolveItemOnSet($key, $value), $afterKey);

        $this->touch();

        return $this;
    }


    public function resolverOnSet(Closure $resolver)
    {
        $this->resolverOnSet = $resolver;
    }


    public function resolverOnGet(Closure $resolver)
    {
        $this->resolveItemOnGet = $resolver;
    }


    public function resolverOnIteration(Closure $resolver)
    {
        $this->resolveItemOnIteration = $resolver;
    }


    public function resolveItemOnSet($key, $item)
    {
        if (empty( $this->resolverOnSet )) {
            return $item;
        }

        return call_user_func($this->resolverOnSet, $key, $item);
    }


    public function resolveItemOnGet($key, $item)
    {
        if (empty( $this->resolveItemOnGet )) {
            return $item;
        }

        return call_user_func($this->resolveItemOnGet, $key, $item);
    }


    public function resolveItemOnIteration($key, $item)
    {
        if (empty( $this->resolverOnIteration )) {
            return $item;
        }

        return call_user_func($this->resolverOnIteration, $key, $item);
    }


    public function getIterator()
    {
        $items = $this->getArrayableItems($this->items);

        foreach ($items as $key => &$item) {
            $item = $this->resolveItemOnIteration($key, $item);
        }

        return new ArrayIterator($items);
    }


    protected function getArrayableItems($items)
    {
        $items = parent::getArrayableItems($items);

        foreach ($items as $key => &$item) {
            $item = $this->resolveItemOnGet($key, $item);
        }

        return $items;
    }
}