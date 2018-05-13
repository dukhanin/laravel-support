<?php

namespace Dukhanin\Support;

class HTMLGenerator
{
    /**
     * Возвращает html-код открывающего тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
    public function openTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open' => true,
            'tag-close' => false,
        ];

        return $this->renderTag($tag, ...$overwrites);
    }

    /**
     * Возвращает html-код закрывающего тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
    public function closeTag($tag, ...$overwrites)
    {
        $overwrites[] = [
            'tag-open' => false,
            'tag-close' => true,
        ];

        return $this->renderTag($tag, ...$overwrites);
    }

    /**
     * Возвращает html-код тега $tag со всеми
     * дополнительными параметрами из ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return string
     */
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

        $attributes = $this->renderAttributes($tag);
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

    /**
     * Возвращает html-код аттрибутов для html-тега
     *
     * @param mixed $attributes
     *
     * @return string
     */
    public function renderAttributes($attributes)
    {
        $this->validateAttributes($attributes);
        $html = [];

        foreach ($this->attributes($attributes) as $key => $value) {
            if (is_null($value) || ! is_scalar($value)) {
                continue;
            }

            $key = ltrim($key, '\\');
            $value = str_replace("'", "\\'", strval($value));
            $html[] = "{$key}='{$value}'";
        }

        return implode(" ", $html);
    }

    /**
     * Возвращает html-код содержимого $content
     *
     * @param mixed $content
     *
     * @return string
     */
    public function renderContent($content)
    {
        return strval($content);
    }

    /**
     * Добавляет к содержимому тега $tag контент $content
     *
     * @param mixed $tag
     * @param mixed $content
     */
    public function append(&$tag, $content)
    {
        $this->validateTag($tag);

        $this->validateContent($content);

        $tag['content'] .= $content;
    }

    /**
     * Добавляет к содержимому тега $tag контент $content
     * перед его уже существующим контентом
     *
     * @param mixed $tag
     * @param mixed $content
     */
    public function prepend(&$tag, $content)
    {
        $this->validateTag($tag);

        $this->validateContent($content);

        $tag['content'] = $content.$tag['content'];
    }

    /**
     * Перенакрывает настройки $tag указанынми в ...$overwrites
     *
     * @param mixed $tag
     * @param array ...$overwrites
     *
     * @return array
     */
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

    /**
     * Добавляет класс $class (или классы) к тегу $tag
     *
     * @param mixed $tag
     * @param string|array $class
     */
    public function addClass(&$tag, $class)
    {
        $this->validateTag($tag);

        $this->validateClass($class);

        $tagClass = array_get($tag, 'class');

        array_set($tag, 'class', trim($tagClass.' '.$class));
    }

    /**
     * Преобразует содержимое переменной $tag в корректный
     * массив, представляющий html-тег
     *
     * @param mixed $tag
     */
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
    }

    /**
     * Валидация tag-name. Если переменная $tagName
     * пуста или не представляет имя тега - задается span
     * по-умолчанию
     *
     * @param mixed $tagName
     */
    public function validateTagName(&$tagName)
    {
        if (! is_string($tagName) || trim($tagName) == '') {
            $tagName = 'span';
        }

        $tagName = trim($tagName);
        $tagName = strtolower($tagName);
    }

    /**
     * Приводит содержимое переменной $class к
     * валидному списку классов в виде строки
     *
     * @param string|array $class
     */
    public function validateClass(&$class)
    {
        $class = is_array($class) ? $class : [$class];

        $class = array_flatten($class);
        $class = array_map('strval', $class);
        $class = array_map('trim', $class);
        $class = array_filter($class);
        $class = array_unique($class);
        $class = implode(' ', $class);

        $class = empty($class) ? null : $class;
    }

    /**
     * Приводит содержимое переменной $attributes к
     * валидному списку аттрибутов в виде массива,
     * включая специфическую обработку аттрибута class (если
     * он представлен).
     *
     * @param mixed $attributes
     */
    public function validateAttributes(&$attributes)
    {
        if (! is_array($attributes)) {
            $attributes = [];
        }

        if (! array_has($attributes, 'class')) {
            array_set($attributes, 'class', null);
        }

        $this->validateClass($attributes['class']);
    }

    /**
     * Преобразует содержимое переменной $content в
     * html-код
     *
     * @param string|array $content
     */
    public function validateContent(&$content)
    {
        if (is_array($content)) {
            $content = $this->renderTag($content);
        }

        $content = value($content);
    }

    /**
     * Запускает предрендеринговые обработки
     * тега $tag
     *
     * @param array $tag
     */
    protected function preprocess(array &$tag)
    {
        $this->preprocessTitle($tag);

        $this->preprocessIcon($tag);

        $this->preprocessUrl($tag);
    }

    /**
     * Предрендеринговая инициализация и обработка
     * полей title, label
     *
     * @param array $tag
     */
    protected function preprocessTitle(array &$tag)
    {
        if ($title = array_get($tag, 'label')) {
            if (! array_get($tag, 'icon-only') && (array_get($tag, 'content') === null)) {
                $this->append($tag, $title);
            }

            if (array_get($tag, 'title') === null) {
                array_set($tag, 'title', $title);
            }
        }

        array_forget($tag, 'label');
    }

    /**
     * Предрендеринговая инициализация и обработка
     * полей icon, icon-only для добавления к содержимому тега
     * иконки и всплывающих к ней подсказок
     *
     * @param array $tag
     */
    protected function preprocessIcon(array &$tag)
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
            $this->prepend($tag, " <i class='{$icon}'></i> ");
        }

        array_forget($tag, ['icon-only', 'icon']);
    }

    /**
     * Предрендеринговая инициализация и обработка
     * полей url и href
     *
     * @param mixed $tag
     */
    protected function preprocessUrl(&$tag)
    {
        if ($url = array_get($tag, 'url')) {
            if (in_array(array_get($tag, 'tag-name'), [null, 'a'], true) && array_get($tag, 'href') === null) {
                array_set($tag, 'tag-name', 'a');
                array_set($tag, 'href', $url);
                array_forget($tag, 'url');
            }
        }
    }

    /**
     * Валидирует и возвращает валидный массив аттрибутов для тега
     * $tag.
     *
     * @param array $tag
     *
     * @return array
     */
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

    /**
     * Разбирает и возвращает тег, основанный на разборе строки $string
     * в виде css-селектора
     *
     * Метод разбирает только id, class и имя тега:
     * <code>
     * $this->stringToTag('a')
     * $this->stringToTag('a.class1')
     * $this->stringToTag('a#id1')
     * $this->stringToTag('a#id1.class1.class2')
     * </code>
     *
     * @param string $string
     *
     * @return array
     */
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
}
