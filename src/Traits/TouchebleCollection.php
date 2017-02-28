<?php
namespace Dukhanin\Support\Traits;

trait TouchebleCollection
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