<?php
use Dukhanin\Support\Arr;

if ( ! function_exists('array_before')) {
    function array_before(&$array, $key, $value, $keyBefore = null)
    {
        return Arr::before($array, $key, $value, $keyBefore);
    }
}

if ( ! function_exists('array_after')) {
    function array_after(&$array, $key, $value, $keyAfter = null)
    {
        return Arr::after($array, $key, $value, $keyAfter);
    }
}