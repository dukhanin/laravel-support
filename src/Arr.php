<?php
namespace Dukhanin\Support;

use Illuminate\Support\Arr as BaseArr;

class Arr extends BaseArr
{

    public static function before(&$array, $key, $value, $keyBefore = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyBefore);
    }


    public static function after(&$array, $key, $value, $keyAfter = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyAfter, true);
    }


    public static function beforeDotNotation(&$array, $key, $value, $keyBefore = null)
    {
        return static::beforeOrAfterDotNotation($array, $key, $value, $keyBefore);
    }


    public static function afterDotNotation(&$array, $key, $value, $keyAfter = null)
    {
        return static::beforeOrAfterDotNotation($array, $key, $value, $keyAfter, true);
    }


    protected static function beforeOrAfter(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if ( ! is_array($array)) {
            $array = [ ];
        }

        if (is_null($keyNeighbor) || ! array_key_exists($keyNeighbor, $array)) {
            if (is_null($key)) {
                $after ? array_push($array, $value) : array_unshift($array, $value);
            } else {
                $array = $after ? $array + [ $key => $value ] : [ $key => $value ] + $array;
            }

            return $array;
        }

        if (array_key_exists($key, $array)) {
            unset( $array[$key] );
        }

        $keyNeighborIndex = array_search($keyNeighbor, array_keys($array));
        $sliceLength      = $keyNeighborIndex + ( $after ? 1 : 0 );

        return $array = array_slice($array, 0, $sliceLength) + [ $key => $value ] + array_slice($array, $sliceLength);
    }


    protected static function beforeOrAfterDotNotation(array &$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (is_null($keyNeighbor)) {
            return $array = $after ? static::after($array, $key, $value) : static::before($array, $key, $value);
        }

        $segments        = explode('.', $keyNeighbor);
        $keyNeighborLast = array_pop($segments);

        if (count($segments) > 0) {
            $leveledKey   = implode('.', $segments);
            $leveledArray = array_get($array, $leveledKey);

            if ( ! is_array($leveledArray)) {
                return $array;
            }
        } else {
            $leveledKey   = null;
            $leveledArray = $array;
        }

        if ( ! array_key_exists($keyNeighborLast, $leveledArray)) {
            array_forget($leveledArray, $key);

            $leveledArray = $after ? array_set($leveledArray, $key, $value) : array_prepend($leveledArray, $value, $key);

            return $array = array_set($array, $leveledKey, $leveledArray);
        }

        if (isset( $leveledArray[$key] )) {
            unset( $leveledArray[$key] );
        }

        $keyNeighborIndex = array_search($keyNeighborLast, array_keys($leveledArray));
        $sliceLength      = $keyNeighborIndex + ( $after ? 1 : 0 );

        $leveledArray = array_slice($leveledArray, 0, $sliceLength) + [ $key => $value ] + array_slice($leveledArray,
                $sliceLength);

        return array_set($array, $leveledKey, $leveledArray);
    }
}
