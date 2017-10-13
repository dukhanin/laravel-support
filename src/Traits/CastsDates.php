<?php
namespace Dukhanin\Support\Traits;

use Dukhanin\Support\Date;

trait CastsDates
{
    protected function asDate($value)
    {
        return ($date = $this->asDateTime($value)) ? $date->asDate() : null;
    }

    protected function asDateTime($value)
    {
        if (in_array($value, ['', null, '0000-00-00', '0000-00-00 00:00:00'], true)) {
            return null;
        }

        if ($value instanceof Date) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return new Date($value->format('Y-m-d H:i:s.u'), $value->getTimezone());
        }

        if (is_numeric($value)) {
            return Date::createFromTimestamp($value);
        }

        if ($this->isStandardDateFormat($value)) {
            return Date::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return Date::createFromFormat($this->getDateFormat(), $value);
    }
}