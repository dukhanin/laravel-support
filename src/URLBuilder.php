<?php
namespace Dukhanin\Support;

use Illuminate\Support\Facades\Request;

class URLBuilder
{

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

        if ( ! is_null($url)) {
            $this->components = array_merge($this->components, parse_url($url));

            $this->components['query'] = $this->sanitizeQuery($this->components['query']);
            $this->components['path']  = $this->sanitizePath($this->components['path']);
            $this->components['host']  = $this->sanitizeHost($this->components['host']);
        }
    }


    public function caseSensitive($caseSensitive = null)
    {
        $this->caseSensitive = (bool) $caseSensitive;

        return $this;
    }


    public function encoded($encoded = true)
    {
        $this->encoded = (bool) $encoded;

        return $this;
    }


    public function scheme($scheme = null)
    {
        if (is_null($scheme)) {
            return $this->components['scheme'];
        }

        $this->components['scheme'] = $this->sanitizeScheme($scheme);

        return $this;
    }


    public function secure($secure = true)
    {
        return $this->scheme($secure ? 'https' : 'http');
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


    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->components['path'];
        }

        $this->components['path'] = $this->sanitizePath($path);

        return $this;
    }


    public function segments()
    {
        return $this->sanitizeSegments(explode('/', $this->components['path']));
    }


    public function segment($index)
    {
        return array_get($this->segments(), $index, false);
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
        if ($path !== null) {
            $this->shiftPath($path);
        } else {
            $this->shiftSegment();
        }

        return $this;
    }


    public function shiftPath($path)
    {
        $strict = mb_substr($path, -1) === '/';

        $path = $this->sanitizePath($path);

        $regexp = '#^' . preg_quote($path) . ( $strict ? '(/|$)' : '' ) . '#u' . ( $this->caseSensitive ? '' : 'i' );

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));
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
        if ($path !== null) {
            $this->popPath($path);
        } else {
            $this->popSegment();
        }

        return $this;
    }


    public function popPath($path)
    {
        $strict = mb_substr($path, 0, 1) === '/';

        $path = $this->sanitizePath($path);

        $regexp = '#' . ( $strict ? '(^|/)' : '' ) . preg_quote($path) . '$#u' . ( $this->caseSensitive ? '' : 'i' );

        $this->components['path'] = $this->sanitizePath(preg_replace($regexp, '', $this->components['path']));
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


    public function queryString()
    {
        if (empty( $this->components['query'] )) {
            return '';
        }

        return http_build_query($this->components['query']);
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

            $url[] = $this->encoded ? Punycode::instance()->encode($this->components['host']) : $this->components['host'];
        }

        if ( ! empty( $path = $this->components['path'] )) {
            if ($this->encoded) {
                $path = implode('/', array_map('urlencode', explode('/', $path)));
            }

            $url[] = '/' . $path;
        }

        if ( ! empty( $query = $this->queryString() )) {
            $query = $this->encoded ? $query : urldecode($query);

            $url[] = '?' . $query;
        }

        if ( ! empty( $this->components['fragment'] )) {
            $url[] = '#' . $this->components['fragment'];
        }

        $this->encoded = true;

        return implode($url);
    }


    public function copy()
    {
        return clone $this;
    }


    public function __toString()
    {
        return $this->compile();
    }


    protected function sanitizeScheme($scheme)
    {
        return preg_replace('#://$#', '', $scheme);
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
        return empty( $host ) ? false : Punycode::instance()->decode($host);
    }


    protected function sanitizePath($path)
    {
        if (is_array($path)) {
            $path = implode('/', $this->sanitizeSegments($path));
        }

        return urldecode(trim($path, '/'));
    }


    protected function sanitizeSegments($segments)
    {
        return array_filter($segments, function ($segment) {
            return ! in_array($segment, [ '', null, false ], true);
        });
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


    protected function sanitizeFragment($fragment)
    {
        return str_replace('#', '', $fragment);
    }
}
