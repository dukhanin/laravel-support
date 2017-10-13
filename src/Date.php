<?php
namespace Dukhanin\Support;

use \Jenssegers\Date\Date as BaseDate;

class Date extends BaseDate
{
    protected $format;

    protected $as = 'datetime';

    protected $hideEmptyTime = true;

    public function setFormat($format)
    {
        $this->format = $format;
    }

    protected function initFormat()
    {
        $this->format = config("dates.{$this->as}.format", $this->as == 'date' ? 'j F Y' : 'j F Y H:i:s');
    }

    public function getFormat()
    {
        if (is_null($this->format)) {
            $this->initFormat();
        }

        return $this->format;
    }

    public function render()
    {
        return (string) is_callable($format = $this->getFormat()) ? $format($this) : $this->format($format);
    }

    public function isTimeEmpty()
    {
        return $this->format('H:i:s') === '00:00:00';
    }

    public function asDate()
    {
        $this->as = 'date';

        $this->format = null;

        return $this;
    }

    public function asDateTime()
    {
        $this->as = 'datetime';

        $this->format = null;

        return $this;
    }

    public function hideEmptyTime(bool $hideEmptyTime = true)
    {
        $this->hideEmptyTime = $hideEmptyTime;
    }

    public function __toString()
    {
        return $this->render();
    }
}