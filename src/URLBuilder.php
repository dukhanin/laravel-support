<?php
namespace Dukhanin\Support;

use Illuminate\Http\Request;
use TrueBV\Punycode;

class URLBuilder
{

    protected static $punycode;

    protected static $request;

    protected $caseSensitive;

    protected $components;

    protected $encoded;


    public function __construct($url = null)
    {
        if (empty(static::$request)) {
            static::$request = Request::capture();
        }

        $this->caseSensitive = false;

        $this->encoded = true;

        $this->components = [
            'scheme'   => static::$request->secure() ? 'https' : 'http',
            'host'     => null,
            'port'     => null,
            'user'     => null,
            'pass'     => null,
            'path'     => '',
            'query'    => [],
            'fragment' => null
        ];

        if ( ! is_null($url) && ( $parsed = parse_url($url) )) {
            // $this->components = array_merge($this->components, parse_url($url));

            if (preg_match('#(^.+://|^//|^)(.+?(:.+)?@)?(.*?)([/:\#\?]|$)#', $url, $pock)) {
                $parsed['host'] = $pock[4];
            }

            if (preg_match('#/(.*?)([\?\#]|$)#', preg_replace('#(^.+://|^//)#', '', $url), $pock)) {
                $parsed['path'] = $pock[1];
            }

            if ( ! empty($parsed['scheme'])) {
                $this->scheme($parsed['scheme']);
            }

            if ( ! empty($parsed['host'])) {
                $this->host($parsed['host']);
            }

            if ( ! empty($parsed['user'])) {
                $this->user($parsed['user']);
            }

            if ( ! empty($parsed['pass'])) {
                $this->pass($parsed['pass']);
            }

            if ( ! empty($parsed['path'])) {
                $this->path($parsed['path']);
            }

            if ( ! empty($parsed['query'])) {
                parse_str($parsed['query'], $parsed['query']);
                $this->query($parsed['query']);
            }

            if ( ! empty($parsed['fragment'])) {
                $this->fragment($parsed['fragment']);
            }
        }
    }


    public function caseSensitive($caseSensitive = null)
    {
        if (is_null($caseSensitive)) {
            return $this->caseSensitive;
        }

        $this->caseSensitive = (bool) $caseSensitive;

        return $this;
    }


    public function encoded($encoded = null)
    {
        if (is_null($encoded)) {
            return $this->encoded;
        }

        $this->encoded = (bool) $encoded;

        return $this;
    }


    public function secure($secure = null)
    {
        if (is_null($secure)) {
            return $this->scheme() === 'https';
        }

        return $this->scheme($secure ? 'https' : 'http');
    }


    public function scheme($scheme = null)
    {
        if (is_null($scheme)) {
            return $this->components['scheme'];
        }

        $this->components['scheme'] = $this->sanitizeScheme($scheme);

        return $this;
    }


    public function user($user = null)
    {
        if (is_null($user)) {
            return $this->components['user'];
        }

        $this->components['user'] = $this->sanitizeUser($user);

        return $this;
    }


    public function pass($pass = null)
    {
        if (is_null($pass)) {
            return $this->components['pass'];
        }

        $this->components['pass'] = $this->sanitizePass($pass);

        return $this;
    }


    public function append($path)
    {
        $path = $this->sanitizePath($path);

        $this->components['path'] = ( $this->components['path'] ? $this->components['path'] . '/' : '' ) . $path;

        return $this;
    }


    public function prepend($path)
    {
        $path = $this->sanitizePath($path);

        $this->components['path'] = $path . ( $this->components['path'] ? '/' . $this->components['path'] : '' );

        return $this;
    }


    public function shift($path = null)
    {
        if ( ! is_null($path)) {
            return $this->shiftPath($path);
        }

        return $this->shiftSegment();
    }


    public function pop($path = null)
    {
        if ( ! is_null($path)) {
            return $this->popPath($path);
        }

        return $this->popSegment();
    }


    public function host($host = null)
    {
        if (is_null($host)) {
            return $this->encoded ? $this->punycode()->encode($this->components['host']) : $this->components['host'];
        }

        $this->components['host'] = $this->sanitizeHost($host);

        return $this;
    }


    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->encoded ? implode('/',
                array_map('urlencode', explode('/', $this->components['path']))) : $this->components['path'];
        }

        $this->components['path'] = $this->sanitizePath($path);

        return $this;
    }


    public function shiftPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, -1) === '/';

        $regexp = '#^' . preg_quote($path) . ( $strict ? '(/|$)' : '' ) . '#u' . ( $this->caseSensitive ? '' : 'i' );

        if ( ! preg_match($regexp, $this->components['path'], $pock)) {
            return false;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }


    public function popPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, 0, 1) === '/';

        $regexp = '#' . ( $strict ? '(^|/)' : '' ) . preg_quote($path) . '$#u' . ( $this->caseSensitive ? '' : 'i' );

        if ( ! preg_match($regexp, $this->components['path'], $pock)) {
            return false;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }


    public function segment($index)
    {
        return array_get($this->segments(), $index, false);
    }


    public function segments()
    {
        return $this->sanitizeSegments(explode('/', $this->components['path']));
    }


    public function shiftSegment()
    {
        $segment  = null;
        $segments = $this->segments();

        if ( ! empty($segments)) {
            $segment = array_shift($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }


    public function popSegment()
    {
        $segment  = null;
        $segments = $this->segments();

        if ( ! empty($segments)) {
            $segment = array_pop($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }


    public function query($key = null, $default = null)
    {
        if (is_null($key)) {
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


    public function queryString()
    {
        if (empty($this->components['query'])) {
            return '';
        }

        $queryString = http_build_query($this->components['query']);

        return $this->encoded ? $queryString : urldecode($queryString);
    }


    public function clearQuery()
    {
        $this->components['query'] = [];

        return $this;
    }


    public function fragment($fragment = null)
    {
        if (is_null($fragment)) {
            return $this->components['fragment'];
        }

        $this->components['fragment'] = $this->sanitizeFragment($fragment);

        return $this;
    }


    public function copy()
    {
        return clone $this;
    }


    public function compile()
    {
        $url = [];

        if ( ! empty($this->components['host'])) {

            if ( ! empty($scheme = $this->scheme())) {
                $url[] = $scheme . '://';
            }

            if ( ! empty($user = $this->user())) {
                $url[] = $user;

                if ( ! empty($pass = $this->pass())) {
                    $url[] = ':' . $pass;
                }

                $url[] = '@';
            }

            $url[] = $this->host();
        }

        if ( ! empty($path = $this->path())) {
            $url[] = '/' . $path;
        }

        if ( ! empty($query = $this->queryString())) {
            $url[] = '?' . $query;
        }

        if ( ! empty($fragment = $this->fragment())) {
            $url[] = '#' . $fragment;
        }

        return implode($url);
    }


    public function punycode()
    {
        if (is_null(static::$punycode)) {
            static::$punycode = new Punycode;
        }

        return static::$punycode;
    }


    public function __toString()
    {
        return $this->compile();
    }


    protected function sanitizeScheme($scheme)
    {
        return strtolower(preg_replace('#://$#', '', $scheme));
    }


    protected function sanitizeUser($user)
    {
        return $user === false ? false : strval($user);
    }


    protected function sanitizePass($pass)
    {
        return $pass === false ? false : strval($pass);
    }


    protected function sanitizeHost($host)
    {
        if (preg_match('/(^|\.)xn--/', $host)) {
            $host = $this->punycode()->decode($host);
        }

        return $host;
    }


    protected function sanitizePath($path)
    {
        if (is_array($path)) {
            $path = implode('/', $this->sanitizeSegments($path));
        }

        return urldecode(trim(preg_replace('#/+#', '/', $path), '/'));
    }


    protected function sanitizeSegments($segments)
    {
        return array_filter($segments, function ($segment) {
            return ! in_array($segment, [ '', null, false ], true);
        });
    }


    protected function sanitizeQuery($query)
    {
        return array_filter($query, function ($item) {
            return ! is_null($item);
        });
    }


    protected function sanitizeFragment($fragment)
    {
        return str_replace('#', '', $fragment);
    }

}
