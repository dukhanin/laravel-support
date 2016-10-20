<?php
use Dukhanin\Support\Arr;
use Dukhanin\Support\URLBuilder;
use Dukhanin\Support\HTMLHelper;

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

if ( ! function_exists('urlbuilder')) {
    function urlbuilder($url = null)
    {
        if ($url instanceof URLBuilder) {
            return $url->copy();
        }

        return new URLBuilder($url);
    }
}

if ( ! function_exists('html_tag')) {
    function html_tag(...$args)
    {
        return HTMLHelper::instance()->renderTag(...$args);
    }
}

if ( ! function_exists('html_tag_open')) {
    function html_tag_open(...$args)
    {
        return HTMLHelper::instance()->openTag(...$args);
    }
}

if ( ! function_exists('html_tag_close')) {
    function html_tag_close(...$args)
    {
        return HTMLHelper::instance()->closeTag(...$args);
    }
}

if ( ! function_exists('html_tag_attr')) {
    function html_tag_attr(...$args)
    {
        return HTMLHelper::instance()->renderAttributes(...$args);
    }
}

if ( ! function_exists('html_tag_add_class')) {
    function html_tag_add_class(&$tag, ...$args)
    {
        return HTMLHelper::instance()->addClass($tag, ...$args);
    }
}