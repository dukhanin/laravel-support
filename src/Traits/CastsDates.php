<?php
namespace Dukhanin\Support\Traits;

use Dukhanin\Support\Date;

/**
 * Трейт расширяет стандартный функционал класса Illuminate\Database\Eloquent\Model для
 * работы с датами (для cast attributes).
 *
 * date- и datetime-аттрибуты начинают возвращать расширенные объекты Dukhanin\Support\Date,
 * которым задаются свои форматы отображения даты/времени для __toString().
 */
trait CastsDates
{
    /**
     * Класс обертка для аттрибутов типов
     * date и datetime
     *
     * @var string
     */
    protected $castDateClass;

    /**
     * Преобразует входящее $value в дату и возвращает виде
     * объекта-обертки для аттрибутов типа date
     *
     * @param  mixed $value
     *
     * @return \Dukhanin\Support\Date
     */
    protected function asDate($value)
    {
        if (method_exists($date = $this->resolveDateObject($value)->startOfDay(), 'setCustomToStringFormat')) {
            $date->setCustomToStringFormat(config('date.to_string.date', 'j F Y'));
        }

        return $date;
    }

    /**
     * Преобразует входящее $value в дату и возвращает виде
     * объекта-обертки для аттрибутов типа datetime
     *
     * @param  mixed $value
     *
     * @return \Dukhanin\Support\Date
     */

    protected function asDateTime($value)
    {
        if (method_exists($date = $this->resolveDateObject($value), 'setCustomToStringFormat')) {
            $date->setCustomToStringFormat(config('date.to_string.datetime', 'j F Y H:i'));
        }

        return $date;
    }

    /**
     * Преобразует входящее $value в дату и возвращает виде
     * объекта-обертки для аттрибутов типа date/datetime
     *
     * @param $value
     *
     * @return \Dukhanin\Support\Date
     */
    protected function resolveDateObject($value)
    {
        $class = $this->getCastDateClass();

        if ($value instanceof $class) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return new $class($value->format('Y-m-d H:i:s.u'), $value->getTimezone());
        }

        if (is_numeric($value)) {
            return $class::createFromTimestamp($value);
        }

        if ($this->isStandardDateFormat($value)) {
            return $class::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return $class::createFromFormat($this->getDateFormat(), $value);
    }

    /**
     * Возвращает имя класса-обертки
     * для аттрибута типа date или datetime
     *
     * @return string
     */
    protected function getCastDateClass()
    {
        if (is_null($this->castDateClass)) {
            $this->castDateClass = app()->bound('Date') ? app()->make('Date') : Date::class;
        }

        return $this->castDateClass;
    }
}