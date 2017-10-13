<?php
namespace Dukhanin\Support\Traits;

trait ClearableCollection
{
    public function clear()
    {
        foreach (array_keys($this->items) as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }
}