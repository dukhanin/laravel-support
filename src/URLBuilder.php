<?php
namespace Dukhanin\Support;

use Illuminate\Support\Facades\Request;
use TrueBV\Punycode;

/**
 * @todo @dukhanin resolve encoding problems with cyrillic hosts and
 *       parse_url function
 */

class URLBuilder
{

    protected static $punycode;

    protected $caseSensitive;

    protected $components;

    protected $encoded;


    public function __construct($url = null)
    {
        $this->caseSensitive = false;

        $this->encoded = true;

        $this->components = [
            'scheme'   => Request::secure() ? 'https' : 'http',
            'host'     => null,
            'port'     => null,
            'user'     => null,
            'pass'     => null,
            'path'     => '',
            'query'    => [ ],
            'fragment' => null
        ];

        if ( ! is_null($url) && ( $parsed = parse_url($url) )) {
            $this->components = array_merge($this->components, parse_url($url));

            $this->components['scheme'] = $this->sanitizeScheme($this->components['scheme']);
            $this->components['query']  = $this->sanitizeQuery($this->components['query']);
            $this->components['path']   = $this->sanitizePath($this->components['path']);
            $this->components['host']   = $this->sanitizeHost($this->components['host']);
        }
    }


    protected function sanitizeScheme($scheme)
    {
        return strtolower(preg_replace('#://$#', '', $scheme));
    }


    protected function sanitizeQuery($query)
    {
        if (is_string($query)) {
            parse_str($query, $query);
        } else {
            $query = [ ];
        }

        return $query;
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


    protected function sanitizeHost($host)
    {
        return empty( $host ) ? false : $this->punycode()->decode($host);
    }


    public function punycode()
    {
        if (is_null(static::$punycode)) {
            static::$punycode = new Punycode;
        }

        return static::$punycode;
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


    protected function sanitizeUser($user)
    {
        return $user === false ? false : strval($user);
    }


    public function pass($pass = null)
    {
        if (is_null($pass)) {
            return $this->components['pass'];
        }

        $this->components['pass'] = $this->sanitizePass($pass);

        return $this;
    }


    protected function sanitizePass($pass)
    {
        return $pass === false ? false : strval($pass);
    }


    public function segment($index)
    {
        return array_get($this->segments(), $index, false);
    }


    public function segments()
    {
        return $this->sanitizeSegments(explode('/', $this->components['path']));
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


    public function shiftPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, -1) === '/';

        $path = $this->sanitizePath($path);

        $regexp = '#^' . preg_quote($path) . ( $strict ? '(/|$)' : '' ) . '#u' . ( $this->caseSensitive ? '' : 'i' );

        if ( ! preg_match($regexp, $this->components['path'], $pock)) {
            return false;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }


    public function shiftSegment()
    {
        $segment  = null;
        $segments = $this->segments();

        if ( ! empty( $segments )) {
            $segment = array_shift($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }


    public function pop($path = null)
    {
        if ( ! is_null($path)) {
            return $this->popPath($path);
        }

        return $this->popSegment();
    }


    public function popPath($path)
    {
        $path = $this->sanitizePath($path);

        $strict = mb_substr($path, 0, 1) === '/';

        $path = $this->sanitizePath($path);

        $regexp = '#' . ( $strict ? '(^|/)' : '' ) . preg_quote($path) . '$#u' . ( $this->caseSensitive ? '' : 'i' );

        if ( ! preg_match($regexp, $this->components['path'], $pock)) {
            return false;
        }

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));

        return $pock[0];
    }


    public function popSegment()
    {
        $segment  = null;
        $segments = $this->segments();

        if ( ! empty( $segments )) {
            $segment = array_pop($segments);

            $this->components['path'] = implode('/', $segments);
        }

        return $segment;
    }


    public function query($query = null, $clearPreviousQuery = false)
    {
        if (is_null($query)) {
            return $this->components['query'];
        }

        if (is_string($query) || is_numeric($query)) {
            return array_get($this->components['query'], $query);
        }

        if ($clearPreviousQuery || $query === false) {
            $this->clearQuery();
        }

        if (is_array($query)) {
            foreach ($query as $key => $value) {
                array_set($this->components['query'], $key, $value);
            }
        }

        return $this;
    }


    public function clearQuery()
    {
        $this->components['query'] = [ ];

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


    protected function sanitizeFragment($fragment)
    {
        return str_replace('#', '', $fragment);
    }


    public function copy()
    {
        return clone $this;
    }


    public function __toString()
    {
        return $this->compile();
    }


    public function compile()
    {
        $url = [ ];

        if ( ! empty( $this->components['host'] )) {

            if ( ! empty( $this->components['scheme'] )) {
                $url[] = $this->components['scheme'] . '://';
            }

            if ( ! empty( $this->components['user'] )) {
                $url[] = $this->components['user'];

                if ( ! empty( $this->components['pass'] )) {
                    $url[] = ':' . $this->components['pass'];
                }

                $url[] = '@';
            }

            $url[] = $this->host();
        }

        if ( ! empty( $path = $this->path() )) {
            $url[] = '/' . $path;
        }

        if ( ! empty( $query = $this->queryString() )) {
            $url[] = '?' . $query;
        }

        if ( ! empty( $this->components['fragment'] )) {
            $url[] = '#' . $this->components['fragment'];
        }

        return implode($url);
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


    public function queryString()
    {
        if (empty( $this->components['query'] )) {
            return '';
        }

        $queryString = http_build_query($this->components['query']);

        return $this->encoded ? $queryString : urldecode($queryString);
    }
}
