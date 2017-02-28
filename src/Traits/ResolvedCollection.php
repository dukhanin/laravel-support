<?php
namespace Dukhanin\Support\Traits;

use Closure;
use Dukhanin\Support\Arr;

trait ResolvedCollection
{

    protected $resolver;

    protected $raw = [ ];


    public function __construct($items = [ ])
    {
        foreach ($this->getArrayableItems($items) as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }


    /**
     * @param  mixed $key
     * @param  mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->raw[]   = $value;
            $this->items[] = $this->resolve($value, $key);
        } else {
            $this->raw[$key]   = $value;
            $this->items[$key] = $this->resolve($value, $key);;
        }
    }


    protected function resolve($value, $key)
    {
        if (empty( $callback = $this->resolver )) {
            return $value;
        }

        return $callback($value, $key);
    }


    public function raw()
    {
        return collect($this->raw);
    }


    public function resolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }


    /**
     * @param  string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset( $this->raw[$key], $this->items[$key] );
    }


    /**
     * @return mixed
     */
    public function pop()
    {
        array_pop($this->raw);

        return array_pop($this->items);
    }


    /**
     * @param  mixed $value
     * @param  mixed $key
     *
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        $this->raw = Arr::prepend($this->raw, $value, $key);

        $this->items = Arr::prepend($this->items, $this->resolve($value, $key), $key);

        return $this;
    }


    /**
     * @param  mixed $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        Arr::pull($this->raw, $key);

        return Arr::pull($this->items, $key, $default);
    }


    /**
     * @return mixed
     */
    public function shift()
    {
        array_shift($this->raw);

        return array_shift($this->items);
    }
}