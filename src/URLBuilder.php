<?php
namespace Dukhanin\Support;

use Illuminate\Http\Request;
use TrueBV\Punycode;

class URLBuilder
{
    /**
     * Учитывать / не учитывать регистр символов в операциях над path
     *
     * @var bool
     */
    protected $caseSensitive;

    /**
     * Массив содержащий компоненты разобронного url:
     * 'scheme'
     * 'host'
     * 'port'
     * 'user'
     * 'pass'
     * 'path'
     * 'query'
     * 'fragment'
     *
     * @var array
     */
    protected $components;

    /**
     * Находится ли url в состоянии encoded (будучи закодированным
     * например, с помощью punycode или urlencode)
     *
     * @var bool
     */
    protected $encoded;

    /**
     * Текущий request
     *
     * @var
     */
    protected static $request;

    /**
     * URLBuilder constructor.
     *
     * @param string|null $url
     */
    public function __construct($url = null)
    {
        if (is_null(static::$request)) {
            static::$request = Request::capture();
        }

        $this->caseSensitive = false;

        $this->encoded = true;

        $this->setUrl($url);
    }

    /**
     * Возвращает или устанавливает schme для текущего url
     *
     * @param string|null $scheme
     *
     * @return $this|mixed
     */
    public function scheme($scheme = null)
    {
        if (func_num_args() === 0) {
            return $this->components['scheme'];
        }

        $this->components['scheme'] = $this->sanitizeScheme($scheme);

        return $this;
    }

    /**
     * Возвращает или устанавливает хост для текущего url
     *
     * @param string|null $host
     *
     * @return $this|mixed
     */
    public function host($host = null)
    {
        if (func_num_args() === 0) {
            return $this->encoded ? $this->punycode()->encode($this->components['host']) : $this->components['host'];
        }

        $this->components['host'] = $this->sanitizeHost($host);

        return $this;
    }

    /**
     * Возвращает или устанавливает имя пользователя для текущего url
     *
     * @param string|null $user
     *
     * @return $this|mixed
     */
    public function user($user = null)
    {
        if (func_num_args() === 0) {
            return $this->components['user'];
        }

        $this->components['user'] = $this->sanitizeUser($user);

        return $this;
    }

    /**
     * Возвращает или устанавливает пароль для текущего url
     *
     * Пароль добвится к результирующему url только в случае
     * указания имени пользователя
     *
     * @param string|null $pass
     *
     * @return $this|mixed
     */
    public function pass($pass = null)
    {
        if (func_num_args() === 0) {
            return $this->components['pass'];
        }

        $this->components['pass'] = $this->sanitizePass($pass);

        return $this;
    }

    /**
     * Возвращает или устанавливает path для текущего url
     *
     * @param string|array|null $path
     *
     * @return $this|mixed|string
     */
    public function path($path = null)
    {
        if (func_num_args() === 0) {
            return $this->encoded ? implode('/',
                array_map('urlencode', explode('/', $this->components['path']))) : $this->components['path'];
        }

        $this->components['path'] = $this->sanitizePath($path);

        return $this;
    }

    /**
     * Возвращает или устанавливает get-переменные для текущего url
     *
     * Если не указано ни одного аттрибута - возвращает весь
     * массив get-переменных текущего url
     *
     * Если указан только один аттрибут $key - возвращает значение
     * get-переменной с ключем $key у текущего url
     *
     * Если первым аттрибутом указан false - сбрасывает все get-переменные
     * для текущего url
     *
     * Если первый аттрибут представлен массивом - устанавливает все ключи
     * и значения этого массива как get-переменные для текущего url
     *
     * @param string|array|false $key
     * @param mixed $default
     *
     * @return $this|array|mixed|\Dukhanin\Support\URLBuilder
     */
    public function query($key = null, $default = null)
    {
        if (func_num_args() === 0) {
            return $this->sanitizeQuery($this->components['query']);
        }

        if (is_string($key) || is_numeric($key)) {
            return array_get($this->sanitizeQuery($this->components['query']), $key, $default);
        }

        if ($key === false) {
            return $this->clearQuery();
        }

        if (is_array($key)) {
            foreach ($key as $_key => $value) {
                array_set($this->components['query'], $_key, $value);
            }
        }

        return $this;
    }

    /**
     * Возвращает сгенерированную строку, содержащую список переменных
     * для текущего url
     *
     * @return string
     */
    public function queryString()
    {
        if (empty($this->components['query'])) {
            return '';
        }

        $queryString = http_build_query($this->components['query']);

        return $this->encoded ? $queryString : urldecode($queryString);
    }

    /**
     * Возвращает или устанавливает фрагмент (#) текущего url
     *
     * @param string|nuull $fragment
     *
     * @return $this|mixed
     */
    public function fragment($fragment = null)
    {
        if (func_num_args() === 0) {
            return $this->components['fragment'];
        }

        $this->components['fragment'] = $this->sanitizeFragment($fragment);

        return $this;
    }

    /**
     * Добавляет кусок $path в конец path текущего url
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function append($path)
    {
        $path = $this->sanitizePath($path);

        $this->components['path'] = ($this->components['path'] ? $this->components['path'].'/' : '').$path;

        return $this;
    }

    /**
     * Добавляет кусок $path в начало path текущего url
     *
     * @param string|array $path
     *
     * @return $this
     */
    public function prepend($path)
    {
        $path = $this->sanitizePath($path);

        $this->components['path'] = $path.($this->components['path'] ? '/'.$this->components['path'] : '');

        return $this;
    }

    /**
     * Отрезает подстроку (string) или набор сегментов (array) $path
     * от начала path текущего url, в случае если $path указан и найден
     *
     * Если $path не указан, отрезает первый сегмент
     * path текущего url и возвращает его
     *
     * @param string|array|null $path
     *
     * @return string|null
     */
    public function shift($path = null)
    {
        if (! is_null($path)) {
            return $this->shiftPath($path);
        }

        return $this->shiftSegment();
    }

    /**
     * Отрезает подстроку (string) или набор сегментов (array) $path
     * от начала path текущего url и возвращает его
     *
     * @param string|array $path
     *
     * @return string|null
     */
    public function shiftPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, -1) === '/';

        $regexp = '#^'.preg_quote($path).($strict ? '(/|$)' : '').'#u'.($this->caseSensitive ? '' : 'i');

        if (! preg_match($regexp, $this->components['path'], $pock)) {
            return null;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }

    /**
     * Отрезает первый сегмент от path текущего url и
     * возвращает его
     *
     * @return string|null
     */
    public function shiftSegment()
    {
        $segment = null;
        $segments = $this->segments();

        if (! empty($segments)) {
            $segment = array_shift($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }

    /**
     * Возвращает список сегментов path текущего url
     *
     * @return array
     */
    public function segments()
    {
        return $this->sanitizeSegments(explode('/', $this->components['path']));
    }

    /**
     * Отрезает подстроку (string) или набор сегментов (array) $path
     * с конца path текущего url, в случае если $path указан и найден
     *
     * Если $path не указан, отрезает последний сегмент
     * path текущего url и возвращает его
     *
     * @param string|array|null $path
     *
     * @return string|null
     */
    public function pop($path = null)
    {
        if (! is_null($path)) {
            return $this->popPath($path);
        }

        return $this->popSegment();
    }

    /**
     * Отрезает подстроку (string) или набор сегментов (array) $path
     * с конца path текущего url, в случае если $path указан и найден
     *
     * @param mixed $path
     *
     * @return string|null
     */
    public function popPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, 0, 1) === '/';

        $regexp = '#'.($strict ? '(^|/)' : '').preg_quote($path).'$#u'.($this->caseSensitive ? '' : 'i');

        if (! preg_match($regexp, $this->components['path'], $pock)) {
            return null;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }

    /**
     * Отрезает последний сегмент от path текущего url и
     * возвращает его
     *
     * @return string|null
     */
    public function popSegment()
    {
        $segment = null;
        $segments = $this->segments();

        if (! empty($segments)) {
            $segment = array_pop($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }

    /**
     * Получить сегмент path текущего url по порядковому
     * номеру $index (начиная с 0)
     *
     * @param int $index
     *
     * @return string|null
     */
    public function segment($index)
    {
        return array_get($this->segments(), $index, null);
    }

    /**
     * Клонирует и возвращает текущий объект
     *
     * @return \Dukhanin\Support\URLBuilder
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Возвращает сгенерированную url-строку
     *
     * @return string
     */
    public function compile()
    {
        $url = [];

        if (! empty($this->components['host'])) {

            if (! empty($scheme = $this->scheme())) {
                $url[] = $scheme.'://';
            }

            if (! empty($user = $this->user())) {
                $url[] = $user;

                if (! empty($pass = $this->pass())) {
                    $url[] = ':'.$pass;
                }

                $url[] = '@';
            }

            $url[] = $this->host();
        }

        if (! empty($path = $this->path())) {
            $url[] = '/'.$path;
        }

        if (! empty($query = $this->queryString())) {
            $url[] = '?'.$query;
        }

        if (! empty($fragment = $this->fragment())) {
            $url[] = '#'.$fragment;
        }

        return implode($url);
    }

    /**
     * Возвращает сгенерированную url-строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->compile();
    }

    /**
     * Удаляет все get-переменные текущего url
     *
     * @return $this
     */
    public function clearQuery()
    {
        $this->components['query'] = [];

        return $this;
    }

    /**
     * Возвращает или устанавливает текущее состояние зависимости
     * от регистра символов. Используется в методах работы с path и
     * segments.
     *
     * @param bool|null $caseSensitive
     *
     * @return $this|bool
     */
    public function caseSensitive($caseSensitive = null)
    {
        if (func_num_args() === 0) {
            return $this->caseSensitive;
        }

        $this->caseSensitive = (bool)$caseSensitive;

        return $this;
    }

    /**
     * Возвращает или устанавливает состояние текущего url.
     *
     * Используется при сборе разборе url-строк
     *
     * @param bool|null $encoded
     *
     * @return $this|bool
     */
    public function encoded($encoded = null)
    {
        if (func_num_args() === 0) {
            return $this->encoded;
        }

        $this->encoded = (bool)$encoded;

        return $this;
    }

    /**
     * Возвращает или устанавливает состояние текущего url.
     *
     * Используется для указания scheme http-ссылки
     *
     * @param bool|null $secure
     *
     * @return bool|mixed|\Dukhanin\Support\URLBuilder
     */
    public function secure($secure = null)
    {
        if (func_num_args() === 0) {
            return $this->scheme() === 'https';
        }

        return $this->scheme($secure ? 'https' : 'http');
    }

    /**
     * Возвращает объект punycode-энкодера
     *
     * @return \TrueBV\Punycode
     */
    public function punycode()
    {
        return app(Punycode::class);
    }

    /**
     * Возвращаяет "очищенный" $scheme
     *
     * @param mixed $scheme
     *
     * @return string
     */
    protected function sanitizeScheme($scheme)
    {
        return strtolower(preg_replace('#://$#', '', $scheme));
    }

    /**
     * Возвращаяет "очищенный" $host
     *
     * @param mixed $host
     *
     * @return mixed
     */
    protected function sanitizeHost($host)
    {
        if (preg_match('/(^|\.)xn--/', $host)) {
            $host = $this->punycode()->decode($host);
        }

        return $host;
    }

    /**
     * Возвращаяет "очищенное" имя пользователя $user
     *
     * @return bool|string
     */
    protected function sanitizeUser($user)
    {
        return $user === false ? false : strval($user);
    }

    /**
     * Возвращаяет "очищенный" пароль пользователя $pass
     *
     * @param mixed $pass
     *
     * @return bool|string
     */
    protected function sanitizePass($pass)
    {
        return $pass === false ? false : strval($pass);
    }

    /**
     * Возвращаяет "очищенный" path
     *
     * @param mixed $path
     *
     * @return string
     */
    protected function sanitizePath($path)
    {
        if (is_array($path)) {
            $path = implode('/', $this->sanitizeSegments($path));
        }

        return urldecode(trim(preg_replace('#/+#', '/', $path), '/'));
    }

    /**
     * Возвращаяет "очищенный" массив $segments
     *
     * @param mixed $segments
     *
     * @return array
     */
    protected function sanitizeSegments($segments)
    {
        return array_filter($segments, function ($segment) {
            return ! in_array($segment, ['', null, false], true);
        });
    }

    /**
     * Возвращаяет "очищенный" $fragment (#)
     *
     * @param mixed $fragment
     *
     * @return mixed
     */
    protected function sanitizeFragment($fragment)
    {
        return ($fragment = str_replace('#', '', $fragment)) ? $fragment : null;
    }

    /**
     * Возвращаяет "очищенный" массив get-переменный $query
     *
     * @param mixed $query
     *
     * @return array
     */
    protected function sanitizeQuery($query)
    {
        return array_filter($query, function ($item) {
            return ! is_null($item);
        });
    }

    /**
     * Возвращает значения компонент по-умолчанию для текущего url
     *
     * @return array
     */
    protected function componentsDefaults()
    {
        return [
            'scheme' => static::$request->secure() ? 'https' : 'http',
            'host' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
            'path' => '',
            'query' => [],
            'fragment' => null,
        ];
    }

    /**
     * Возвращает массив (components) разобранного url
     *
     * @param string $url
     *
     * @return array
     */
    protected function parseUrl($url)
    {
        if ($parsed = parse_url($url)) {
            $pock = null;

            // С некоторыми url возникают трудности при разборе с помощью
            // parse_url(), поэтому делаем небольшую доводку результата

            if (preg_match('#(^.+://|^//|^)(.+?(:.+)?@)?(.*?)([/:\#\?]|$)#', $url, $pock)) {
                $parsed['host'] = $pock[4];
            }

            if (preg_match('#/(.*?)([\?\#]|$)#', preg_replace('#(^.+://|^//)#', '', $url), $pock)) {
                $parsed['path'] = $pock[1];
            }

            if (! empty($parsed['query'])) {
                parse_str($parsed['query'], $parsed['query']);

                return $parsed;
            }
        }

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Устанавливает url адресс
     *
     * @param $url
     */
    protected function setUrl($url)
    {
        $parsed = $this->parseUrl($url) + $this->componentsDefaults();

        $this->scheme($parsed['scheme']);
        $this->host($parsed['host']);
        $this->user($parsed['user']);
        $this->pass($parsed['pass']);
        $this->path($parsed['path']);
        $this->query($parsed['query']);
        $this->fragment($parsed['fragment']);
    }
}
