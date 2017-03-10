<?php
namespace Dukhanin\Support\Traits;

use App\Support\Facades\Aliases;

trait HasUrl
{

    public $url;


    public function initUrl()
    {

    }


    public function url()
    {
        if (is_null($this->url)) {
            $this->initUrl();
        }

        return $this->url;
    }
}