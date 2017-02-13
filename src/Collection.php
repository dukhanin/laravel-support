<?php
namespace Dukhanin\Support;

use Illuminate\Support\Collection as BaseCollection;
use \Closure;

class Collection extends BaseCollection
{

    protected $resolver;

    protected $touched = false;


    public function __construct($items = [ ])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->offsetSet($key, $value);
        }

        $this->resolver = function ($key, $item) {
            return $item;
        };
    }


    public function offsetSet($key, $value)
    {
        parent::offsetSet($key, $this->resolveItem($key, $value));

        $this->touch();
    }


    public function offsetUnset($key)
    {
        parent::offsetUnset($key);

        $this->touch();
    }


    public function prepend($value, $key = null)
    {
        $value = $this->resolveItem($key, $value);

        return parent::prepend($value, $key);
    }


    public function before($key, $value, $beforeKey = null)
    {
        array_before($this->items, $key, $this->resolveItem($value), $beforeKey);

        $this->touch();

        return $this;
    }


    public function after($key, $value, $afterKey = null)
    {
        array_after($this->items, $key, $this->resolveItem($value), $afterKey);

        $this->touch();

        return $this;
    }


    public function touch()
    {
        $this->touched = true;
    }


    public function touched()
    {
        return $this->touched;
    }


    public function setResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }


    public function resolveItem($key, $item)
    {
        return call_user_func($this->resolver, $key, $item);
    }


    protected function getArrayableItems($items)
    {
        $items = parent::getArrayableItems($items);

        $items = array_map([ $this, 'resolveItem' ], $items);

        return $items;
    }
}