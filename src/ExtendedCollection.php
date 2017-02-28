<?php
namespace Dukhanin\Support;

use Illuminate\Support\Collection;
use Dukhanin\Support\Traits\TouchebleCollection;
use Dukhanin\Support\Traits\ResolvedCollection;

class ExtendedCollection extends Collection
{

    use TouchebleCollection, ResolvedCollection {
        ResolvedCollection::offsetSet as resolvedOffsetSet;
        ResolvedCollection::offsetUnset as resolvedOffsetUnset;
    }


    public function offsetSet($key, $value)
    {
        $this->resolvedOffsetSet($key, $value);

        $this->touch();
    }


    public function offsetUnset($key)
    {
        $this->resolvedOffsetUnset($key);

        $this->touch();
    }


    public function before($key, $value, $beforeKey = null)
    {
        array_before($this->raw, $key, $value, $beforeKey);

        array_before($this->items, $key, $this->resolve($value, $key), $beforeKey);

        $this->touch();

        return $this;
    }


    public function after($key, $value, $afterKey = null)
    {
        array_after($this->raw, $key, $value, $afterKey);

        array_after($this->items, $key, $this->resolve($value, $key), $afterKey);

        $this->touch();

        return $this;
    }

}