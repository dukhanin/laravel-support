<?php

namespace Dukhanin\Support;

class HTMLGenerator
{
    protected static $instance;

    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function openTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open' => true,
            'tag-close' => false,
        ];

        return $this->renderTag($tag, ...$overwrites);
    }

    public function renderTag($tag, ...$overwrites)
    {
        $tag = $this->merge($tag, ...$overwrites);

        $this->preprocess($tag);

        $this->validateTagName($tag['tag-name']);

        $tagOpen = ! array_get($tag, 'tag-close');
        $tagClose = ! array_get($tag, 'tag-open');

        if (array_get($tag, 'tag-plural')) {
            $tagType = 'plural';
        } elseif (array_get($tag, 'tag-singular')) {
            $tagType = 'singular';
        } else {
            $tagType = 'auto';
        }

        $attributes = $this->renderAttributes($this->attributes($tag));
        $attributes = empty($attributes) ? '' : ' '.$attributes;

        if ($tagType === 'singular' || ($tagType === 'auto' && $tag['content'] === null && $tagOpen && $tagClose)) {
            return "<{$tag['tag-name']}{$attributes} />";
        }

        if ($tagOpen) {
            $tagOpen = "<{$tag['tag-name']}{$attributes}>";
        }

        if ($tagClose) {
            $tagClose = "</{$tag['tag-name']}>";
        }

        return $tagOpen.($tagOpen && $tagClose ? $this->renderContent(array_get($tag, 'content')) : '').$tagClose;
    }

    public function merge($tag, ...$overwrites)
    {
        $this->validateTag($tag);

        foreach ($overwrites as $overwrite) {
            $this->validateTag($overwrite);

            if ($class = array_get($overwrite, 'class')) {
                $this->addClass($tag, $class);
                array_forget($overwrite, 'class');
            }

            $overwrite = array_filter($overwrite, function ($value) {
                return ! is_null($value);
            });

            $tag = array_replace_recursive($tag, $overwrite);

            if (array_has($tag, 'class')) {
                $this->validateClass($tag['class']);
            }
        }

        return $tag;
    }

    public function validateTag(&$tag)
    {
        if (is_string($tag)) {
            $tag = $this->stringToTag($tag);
        }

        if (! is_array($tag)) {
            $tag = [];
        }

        if (! array_has($tag, 'tag-name')) {
            array_set($tag, 'tag-name', null);
        }

        if (! array_has($tag, 'content')) {
            array_set($tag, 'content', null);
        }

        foreach ($tag as $key => $value) {
            if (! str_contains($key, '.')) {
                continue;
            }

            array_set($tag, $key, $value);
            unset($tag[$key]);
        }

        $this->validateContent($tag['tag-name']);

        $this->validateAttributes($attributes);

        return $tag;
    }

    protected function stringToTag($string)
    {
        $selector = strval($string);
        $selector = trim($selector);
        list($selector) = explode(' ', $selector);

        $tag = [];

        if (preg_match('/^([a-z0-9-_]+)/', $selector, $pock)) {
            array_set($tag, 'tag-name', $pock[1]);
        }

        if (preg_match('/#([a-z0-9-_]+)/', $selector, $pock)) {
            array_set($tag, 'id', $pock[1]);
        }

        if (preg_match_all('/\.([a-z0-9-_]+)/', $selector, $pock)) {
            $this->addClass($tag, $pock[1]);
        }

        return $tag;
    }

    public function addClass(&$tag, $class)
    {
        $this->validateTag($tag);

        $this->validateClass($class);

        $tagClass = array_get($tag, 'class');

        array_set($tag, 'class', trim($tagClass.' '.$class));

        return $tag;
    }

    public function validateClass(&$class)
    {
        if (is_array($class)) {
            $class = array_flatten($class);
            $class = array_map('strval', $class);
            $class = array_map('trim', $class);
            $class = implode(' ', $class);
        } else {
            $class = strval($class);
        }

        if (empty($class)) {
            $class = null;
        }

        return $class;
    }

    public function validateContent(&$content)
    {
        if (is_array($content)) {
            return $this->renderTag($content);
        }

        $content = value($content);

        return $content;
    }

    public function validateAttributes(&$attributes)
    {
        if (! is_array($attributes)) {
            $attributes = [];
        }

        if (! array_has($attributes, 'class')) {
            array_set($attributes, 'class', null);
        }

        $this->validateClass($attributes['class']);

        return $attributes;
    }

    protected function preprocess(&$tag)
    {
        $this->preprocessTitle($tag);

        $this->preprocessIcon($tag);

        $this->preprocessUrl($tag);

        return $tag;
    }

    protected function preprocessTitle(&$tag)
    {
        if ($title = array_get($tag, 'label', array_get($tag, 'title'))) {
            if (! array_get($tag, 'icon-only') && (array_get($tag, 'content') === null)) {
                $this->append($tag, $title);
            }

            if (array_get($tag, 'title') === null) {
                array_set($tag, 'title', $title);
            }
        }

        array_forget($tag, 'label');

        return $tag;
    }

    public function append(&$tag, $content)
    {
        $this->validateTag($tag);

        $this->validateContent($content);

        $tag['content'] .= $content;

        return $tag;
    }

    protected function preprocessIcon(&$tag)
    {
        if (array_get($tag, 'icon-only')) {
            $tag['content'] = null;

            if (array_get($tag, 'title')) {
                if (array_get($tag, 'toggle') === null && array_get($tag, 'data-toggle') === null) {
                    array_set($tag, 'data-toggle', 'tooltip');
                }

                if (array_get($tag, 'placement') === null && array_get($tag, 'data-placement') === null) {
                    array_set($tag, 'data-placement', 'auto');
                }
            }
        }

        if ($icon = array_get($tag, 'icon')) {
            $icon = strval($icon);
            $this->prepend($tag, " <i class='{$icon}' ></i> ");
        }

        array_forget($tag, ['icon-only', 'icon']);

        return $tag;
    }

    public function prepend(&$tag, $content)
    {
        $this->validateTag($tag);

        $this->validateContent($content);

        $tag['content'] = $content.$tag['content'];

        return $tag;
    }

    protected function preprocessUrl(&$tag)
    {
        if ($url = array_get($tag, 'url')) {
            if (in_array(array_get($tag, 'tag-name'), [null, 'a'], true) && array_get($tag, 'href') === null) {
                array_set($tag, 'tag-name', 'a');
                array_set($tag, 'href', $url);
                array_forget($tag, 'url');
            }
        }

        return $tag;
    }

    public function validateTagName(&$tagName)
    {
        if (! is_string($tagName) || trim($tagName) == '') {
            $tagName = 'span';
        }

        $tagName = trim($tagName);
        $tagName = strtolower($tagName);

        return $tagName;
    }

    public function renderAttributes($attributes)
    {
        $this->validateAttributes($attributes);
        $html = [];

        foreach ($attributes as $key => $value) {
            if (is_null($value) || ! is_scalar($value)) {
                continue;
            }

            $value = str_replace("'", "\\'", strval($value));
            $html[] = "{$key}='{$value}'";
        }

        return implode(" ", $html);
    }

    protected function attributes(array $tag)
    {
        $attributes = array_except($tag, [
            'tag-name',
            'tag-plural',
            'tag-singular',
            'tag-close',
            'tag-open',
            'content',
            'data',
            'meta',
        ]);

        if (is_array($data = array_get($tag, 'data'))) {
            foreach ($data as $key => $value) {
                $attributes["data-{$key}"] = $value;
            }
        }

        return $attributes;
    }

    public function renderContent($content)
    {
        return strval($content);
    }

    public function closeTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open' => false,
            'tag-close' => true,
        ];

        return $this->renderTag($tag, ...$overwrites);
    }

    protected function preprocessData(&$tag)
    {
        return $tag;
    }
}
