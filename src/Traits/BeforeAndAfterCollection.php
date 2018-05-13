<?php
namespace Dukhanin\Support\Traits;

trait BeforeAndAfterCollection
{
    /**
     * Добавляет значение $value с ключем $key в коллекцию
     * до элемента с ключем $beforeKey
     *
     * В случае, если $beforeKey не указан — добавляет новый
     * элемент в начало коллекции
     *
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $beforeKey
     *
     * @return $this
     */
    public function before($key, $value, $beforeKey = null)
    {
        array_before($this->items, $key, $value, $beforeKey);

        return $this;
    }

    /**
     * Добавляет значение $value с ключем $key в коллекцию
     * после элемента с ключем $afterKey
     *
     * В случае, если $afterKey не указан — добавляет новый
     * элемент в конец коллекции
     *
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $afterKey
     *
     * @return $this
     */
    public function after($key, $value, $afterKey = null)
    {
        array_after($this->items, $key, $value, $afterKey);

        return $this;
    }
}