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

if (! function_exists('html_generator')) {
    function html_generator()
    {
        return HTMLGenerator::instance();
    }
}

if (! function_exists('html_tag')) {
    function html_tag(...$args)
    {
        return html_generator()->renderTag(...$args);
    }
}

if (! function_exists('html_tag_open')) {
    function html_tag_open(...$args)
    {
        return html_generator()->openTag(...$args);
    }
}

if (! function_exists('html_tag_close')) {
    function html_tag_close(...$args)
    {
        return html_generator()->closeTag(...$args);
    }
}

if (! function_exists('html_tag_attr')) {
    function html_tag_attr(...$args)
    {
        return html_generator()->renderAttributes(...$args);
    }
}

if (! function_exists('html_tag_add_class')) {
    function html_tag_add_class(&$tag, ...$args)
    {
        return html_generator()->addClass($tag, ...$args);
    }
}