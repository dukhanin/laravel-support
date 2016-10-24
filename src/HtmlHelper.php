<?php

namespace Dukhanin\Support;

class HTMLHelper
{

    protected static $instance;


    public function openTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open'  => true,
            'tag-close' => false
        ];

        return $this->renderTag($tag, ...$overwrites);
    }


    public function closeTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open'  => false,
            'tag-close' => true
        ];

        return $this->renderTag($tag, ...$overwrites);
    }


    public function renderAttributes($attributes)
    {
        $this->validateAttributes($attributes);
        $html = [ ];

        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            $value  = $value = is_scalar($value) ? addslashes(value($value)) : gettype($value);
            $html[] = "{$key}='{$value}'";
        }

        return implode(" ", $html);
    }


    public function renderTag($tag, ...$overwrites)
    {
        $tag = $this->merge($tag, ...$overwrites);

        $this->preprocess($tag);

        $this->validateTagName($tag['tag-name']);

        $tagName    = array_get($tag, 'tag-name');
        $tagOpen    = ! array_get($tag, 'tag-close');
        $tagClose   = ! array_get($tag, 'tag-open');
        $attributes = array_get($tag, 'attributes');
        $content    = array_get($tag, 'content');
        $data       = array_get($tag, 'data');

        if (array_get($tag, 'tag-plural')) {
            $tagType = 'plural';
        } elseif (array_get($tag, 'tag-singular')) {
            $tagType = 'singular';
        } else {
            $tagType = 'auto';
        }

        array_forget($tag,
            [ 'tag-name', 'tag-plural', 'tag-singular', 'tag-open', 'tag-close', 'attributes', 'content' ]);

        if( is_array($data) ) {
            foreach ($data as $key => $value) {
                array_set($attributes, "data-{$key}", $value);
            }

            array_forget($tag, 'data');
        }

        foreach ($tag as $key => $value) {
            array_set($attributes, "data-{$key}", $value);
        }

        $attributes = $this->renderAttributes($attributes);
        $attributes = empty( $attributes ) ? '' : ' ' . $attributes;

        if ($tagType === 'singular' || ( $tagType === 'auto' && $content === null && $tagOpen && $tagClose )) {
            return "<{$tagName}{$attributes} />";
        }

        if ($tagOpen) {
            $tagOpen = "<{$tagName}{$attributes}>";
        }

        if ($tagClose) {
            $tagClose = "</{$tagName}>";
        }

        return $tagOpen . ( $tagOpen && $tagClose ? $this->renderContent($content) : '' ) . $tagClose;
    }


    public function renderContent($content)
    {
        return strval($content);
    }


    public function merge($tag, ...$overwrites)
    {
        $this->validateTag($tag);

        foreach ($overwrites as $overwrite) {
            $this->validateTag($overwrite);

            // $this->preprocess($overwrite); @todo @dukhanin really remove?

            if ($class = array_get($overwrite, 'attributes.class')) {
                $this->addClass($tag, $class);
                array_forget($overwrite, 'attributes.class');
            }

            $overwrite = array_filter($overwrite, function ($value) {
                return ! is_null($value);
            });

            foreach (array_dot($overwrite) as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                array_set($tag, $key, $value);
            }

            if (isset( $tag['attributes']['class'] )) {
                $this->validateClass($tag['attributes']['class']);
            }
        }

        return $tag;
    }


    public function addClass(&$tag, $class)
    {
        $this->validateTag($tag);
        $this->validateClass($class);

        $tagClass = array_get($tag, 'attributes.class');

        array_set($tag, 'attributes.class', trim($tagClass . ' ' . $class));

        return $tag;
    }


    public function append(&$tag, $content)
    {
        $this->validateTag($tag);
        $this->validateContent($content);

        $tag['content'] .= $content;

        return $tag;
    }


    public function prepend(&$tag, $content)
    {
        $this->validateTag($tag);
        $this->validateContent($content);

        $tag['content'] = $content . $tag['content'];

        return $tag;
    }


    public function validateTag(&$tag)
    {
        if (is_string($tag)) {
            $tag = $this->stringToTag($tag);
        }

        if ( ! is_array($tag)) {
            $tag = [ ];
        }

        if ( ! array_has($tag, 'tag-name')) {
            array_set($tag, 'tag-name', null);
        }

        if ( ! array_has($tag, 'content')) {
            array_set($tag, 'content', null);
        }

        if ( ! array_has($tag, 'attributes')) {
            array_set($tag, 'attributes', null);
        }

        $this->validateContent($tag['tag-name']);
        $this->validateAttributes($attributes);

        return $tag;
    }


    public function validateTagName(&$tagName)
    {
        if ( ! is_string($tagName) || trim($tagName) == '') {
            $tagName = 'span';
        }

        $tagName = trim($tagName);
        $tagName = strtolower($tagName);

        return $tagName;
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
        if ( ! is_array($attributes)) {
            $attributes = [ ];
        }

        if ( ! array_has($attributes, 'class')) {
            array_set($attributes, 'class', null);
        }

        $this->validateClass($attributes['class']);

        return $attributes;
    }


    public function validateClass(&$class)
    {
        if ( ! is_array($class)) {
            $class = strval($class);
            $class = explode(' ', $class);
        }

        $class = array_flatten($class);
        $class = array_map('strval', $class);
        $class = array_map('strtolower', $class);
        $class = array_map('trim', $class);
        $class = array_unique($class);
        $class = implode(' ', $class);
        $class = trim($class);

        if ($class == '') {
            $class = null;
        }

        return $class;
    }


    public static function instance()
    {
        if (empty( static::$instance )) {
            static::$instance = new static;
        }

        return static::$instance;
    }


    protected function preprocess(&$tag)
    {
        $this->preprocessTitle($tag);
        $this->preprocessIcon($tag);
        $this->preprocessClass($tag);
        $this->preprocessUrl($tag);
        $this->preprocessId($tag);

        return $tag;
    }


    protected function preprocessTitle(&$tag)
    {
        if ($title = array_get($tag, 'label', array_get($tag, 'title'))) {
            if ( ! array_get($tag, 'icon-only') && ( array_get($tag, 'content') === null )) {
                $this->append($tag, $title);
            }

            if (array_get($tag, 'attributes.title') === null) {
                array_set($tag, 'attributes.title', $title);
            }
        }

        array_forget($tag, [ 'label', 'title' ]);

        return $tag;
    }


    protected function preprocessIcon(&$tag)
    {
        if (array_get($tag, 'icon-only')) {
            $tag['content'] = null;

            if (array_get($tag, 'attributes.title')) {
                if (array_get($tag, 'toggle') === null && array_get($tag, 'attributes.data-toggle') === null) {
                    array_set($tag, 'attributes.data-toggle', 'tooltip');
                }

                if (array_get($tag, 'placement') === null && array_get($tag, 'attributes.data-placement') === null) {
                    array_set($tag, 'attributes.data-placement', 'auto');
                }
            }
        }

        if ($icon = array_get($tag, 'icon')) {
            $icon = strval($icon);
            $this->prepend($tag, " <i class='{$icon}' ></i> ");
        }

        array_forget($tag, [ 'icon-only', 'icon' ]);

        return $tag;
    }


    protected function preprocessClass(&$tag)
    {
        if ($class = array_get($tag, 'class')) {
            $this->addClass($tag, $class);
        }

        array_forget($tag, 'class');

        return $tag;
    }


    protected function preprocessUrl(&$tag)
    {
        if ($url = array_get($tag, 'url')) {
            if (in_array(array_get($tag, 'tag-name'), [ null, 'a' ], true) && array_get($tag,
                    'attributes.href') === null
            ) {
                array_set($tag, 'tag-name', 'a');
                array_set($tag, 'attributes.href', $url);
                array_forget($tag, 'url');
            }
        }

        return $tag;
    }


    protected function preprocessId(&$tag)
    {
        if ($id = array_get($tag, 'id') && array_get($tag, 'attributes.id') === null) {
            array_set($tag, 'attributes.id', $id);
        }

        array_forget($tag, 'id');

        return $tag;
    }


    protected function preprocessData(&$tag)
    {
        return $tag;
    }


    protected function stringToTag($string)
    {
        $selector = strval($string);
        $selector = trim($selector);
        list( $selector ) = explode(' ', $selector);

        $tag = [ ];

        if (preg_match('/^([a-z0-9-_]+)/', $selector, $pock)) {
            array_set($tag, 'tag-name', $pock[1]);
        }

        if (preg_match('/#([a-z0-9-_]+)/', $selector, $pock)) {
            array_set($tag, 'attributes.id', $pock[1]);
        }

        if (preg_match_all('/\.([a-z0-9-_]+)/', $selector, $pock)) {
            $this->addClass($tag, $pock[1]);
        }

        return $tag;
    }
}
