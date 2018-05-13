<?php

namespace Dukhanin\Support;

use Jenssegers\Date\Date as BaseDate;

/**
 * Класс расширяет Jenssegers\Date\Date, добавляя возможность
 * рендерить дату в строку (__toString()) как в стандартном
 * формате (\Carbon\Carbon::$toStringFormat), так и указать формат
 * рендеринга для отдельного объекта
 */
class Date extends BaseDate
{
    /**
     * Формат рендеринга даты для текущего объекта
     *
     * @var
     */
    protected $customToStringFormat;

    /**
     * Сбрасывает формат рендеринга даты для текущего объекта
     */
    public function resetCustomToStringFormat()
    {
        $this->customToStringFormat = null;
    }

    /**
     * Устанавливает формат рендеринга даты для текущего объекта
     *
     * @param $format
     */
    public function setCustomToStringFormat($format)
    {
        $this->customToStringFormat = $format;
    }

    /**
     * Рендерит дату в формате текущего объекта (если указан),
     * либо в стандартном форате (\Carbon\Carbon::$toStringFormat)
     *
     * @return mixed|string
     */
    public function __toString()
    {
        return $this->format($this->customToStringFormat ?? static::$toStringFormat);
    }
}