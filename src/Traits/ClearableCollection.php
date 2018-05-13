<?php
namespace Dukhanin\Support\Traits;

trait ClearableCollection
{
    /**
     * Очищает коллекцию
     * 
     * @return $this
     */
    public function clear()
    {
        foreach (array_keys($this->items) as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }
}