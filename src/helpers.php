<?php
use Dukhanin\Support\Arr;
use Dukhanin\Support\URLBuilder;
use Dukhanin\Support\HTMLGenerator;

if (! function_exists('array_before')) {
    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * до элемента с ключем $beforeKey
     *
     * $beforeKey может быть указан в dot-notation формате, таким
     * образом новый элемент может быть вставлен в любой уровень
     * многомерного массива.
     *
     * При использовании dot-notation в $beforeKey, в $key указывается
     * ключ внутри последнего уровня массива, без dot-notation
     *
     * В случае, если $beforeKey не указан — добавляет новый
     * элемент в начало массива
     *
     * <code>
     * $arr = [
     *     'one' => 'One',
     *     'two' => 'Two',
     *     'three' => [
     *         'a' => 'A',
     *         'c' => 'C'
     *     ],
     * ];
     *
     * array_before('b', 'B', 'three.c');
     * </code>
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyBefore
     *
     * @return array
     */
    function array_before(&$array, $key, $value, $keyBefore = null)
    {
        return Arr::before($array, $key, $value, $keyBefore);
    }
}

if (! function_exists('array_after')) {
    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * после элемента с ключем $keyAfter
     *
     * $keyAfter может быть указан в dot-notation формате, таким
     * образом новый элемент может быть вставлен в любой уровень
     * многомерного массива
     *
     * При использовании dot-notation в $keyAfter, в $key указывается
     * ключ внутри последнего уровня массива, без dot-notation
     *
     * В случае, если $keyAfter не указан — добавляет новый
     * элемент в конец массива
     *
     * <code>
     * $arr = [
     *     'one' => 'One',
     *     'two' => 'Two',
     *     'three' => [
     *         'a' => 'A',
     *         'c' => 'C'
     *     ],
     * ];
     *
     * array_after('b', 'B', 'three.a');
     * </code>
     *
     * @param mixed $array
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $keyAfter
     *
     * @return array
     */
    function array_after(&$array, $key, $value, $keyAfter = null)
    {
        return Arr::after($array, $key, $value, $keyAfter);
    }
}

if (! function_exists('urlbuilder')) {
    /**
     * Возвращает объект \Dukhanin\Support\URLBuilder
     * для ссылки  $url
     *
     * @param string|null $url
     *
     * @return \Dukhanin\Support\URLBuilder
     */
    function urlbuilder($url = null)
    {
        if ($url instanceof URLBuilder) {
            return $url->copy();
        }

        return new URLBuilder($url);
    }
}

if (! function_exists('html')) {
    /**
     * Возвращает объект html-генератора \Dukhanin\Support\HTMLGenerator
     *
     * @return \Dukhanin\Support\HTMLGenerator
     */
    function html()
    {
        return app(HTMLGenerator::class);
    }
}

if (! function_exists('html_tag')) {
    /**
     * Возвращает html-код тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
    function html_tag($tag, ...$overwrites)
    {
        return app(HTMLGenerator::class)->renderTag($tag, ...$overwrites);
    }
}

if (! function_exists('html_tag_open')) {
    /**
     * Возвращает html-код открывающего тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
    function html_tag_open($tag, ...$overwrites)
    {
        return app(HTMLGenerator::class)->openTag($tag, ...$overwrites);
    }
}

if (! function_exists('html_tag_close')) {
    /**
     * Возвращает html-код закрывающего тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
    function html_tag_close($tag, ...$overwrites)
    {
        return app(HTMLGenerator::class)->closeTag($tag, ...$overwrites);
    }
}

if (! function_exists('html_tag_attr')) {
    /**
     * Возвращает html-код аттрибутов для html-тега
     *
     * @param mixed $attributes
     *
     * @return string
     */
    function html_tag_attr($attributes)
    {
        return app(HTMLGenerator::class)->renderAttributes($attributes);
    }
}

if (! function_exists('html_tag_add_class')) {
    /**
     * Добавляет класс $class (или классы) к тегу $tag
     *
     * @param mixed $tag
     * @param string|array $class
     */
    function html_tag_add_class(&$tag, $class)
    {
        return app(HTMLGenerator::class)->addClass($tag, $class);
    }
}