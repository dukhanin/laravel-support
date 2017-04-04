<?php
namespace Dukhanin\Support\Traits;

trait BeforeAndAfterCollection
{
    public function before($key, $value, $beforeKey = null)
    {
        array_before($this->items, $key, $value, $beforeKey);

        return $this;
    }


    public function after($key, $value, $afterKey = null)
    {
        array_after($this->items, $key, $value, $afterKey);

        return $this;
    }
}