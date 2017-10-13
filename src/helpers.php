<?php
use Dukhanin\Support\Arr;
use Dukhanin\Support\URLBuilder;
use Dukhanin\Support\HTMLGenerator;

if (! function_exists('array_before')) {
    function array_before(&$array, $key, $value, $keyBefore = null)
    {
        return Arr::before($array, $key, $value, $keyBefore);
    }
}

if (! function_exists('array_after')) {
    function array_after(&$array, $key, $value, $keyAfter = null)
    {
        return Arr::after($array, $key, $value, $keyAfter);
    }
}

if (! function_exists('urlbuilder')) {
    function urlbuilder($url = null)
    {
        if ($url instanceof URLBuilder) {
            return $url->copy();
        }

        return new URLBuilder($url);
    }
}

if (! function_exists('html')) {
    function html()
    {
        return app(HTMLGenerator::class);
    }
}

if (! function_exists('html_tag')) {
    function html_tag(...$args)
    {
        return app(HTMLGenerator::class)->renderTag(...$args);
    }
}

if (! function_exists('html_tag_open')) {
    function html_tag_open(...$args)
    {
        return app(HTMLGenerator::class)->openTag(...$args);
    }
}

if (! function_exists('html_tag_close')) {
    function html_tag_close(...$args)
    {
        return app(HTMLGenerator::class)->closeTag(...$args);
    }
}

if (! function_exists('html_tag_attr')) {
    function html_tag_attr(...$args)
    {
        return app(HTMLGenerator::class)->renderAttributes(...$args);
    }
}

if (! function_exists('html_tag_add_class')) {
    function html_tag_add_class(&$tag, ...$args)
    {
        return app(HTMLGenerator::class)->addClass($tag, ...$args);
    }
}