<?php
namespace Dukhanin\Support\Traits;

trait Toucheble
{
    protected $touched = false;

    public function touch()
    {
        $this->touched = true;
    }

    public function touched()
    {
        return $this->touched;
    }
}