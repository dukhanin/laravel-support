<?php
namespace Dukhanin\Support;

use TrueBV\Punycode as TrueBVPunycode;

class Punycode
{

    protected static $instance;


    public static function instance()
    {
        if (empty( static::$instance )) {
            static::$instance = new TrueBVPunycode;
        }

        return static::$instance;
    }
}
