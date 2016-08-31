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


    protected static function beforeOrAfter(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (isset( $array[$key] )) {
            unset( $array[$key] );
        }

        if (is_null($keyNeighbor) || ! is_array($array) || ! array_key_exists($keyNeighbor, $array)) {
            return $after ? static::add($array, $value, $key) : static::prepend($array, $value, $key);
        }

        $keyNeighborIndex = array_search($keyNeighbor, array_keys($array));
        $sliceLength      = $keyNeighborIndex + ( $after ? 1 : 0 );
        $left             = array_slice($array, 0, $sliceLength);
        $right            = array_slice($array, $sliceLength);

        return $array = $left + [ $key => $value ] + $right;
    }


    protected static function beforeOrAfterDot(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (is_null($keyNeighbor)) {
            return $after ? static::set($array, $value, $key) : static::prepend($array, $value, $key);
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
            $leveledArray = &$array;
        }

        $keyNeighborIndex = array_search($keyNeighborLast, array_keys($leveledArray));

        if ($keyNeighborIndex !== false) {
            if (isset( $leveledArray[$key] )) {
                unset( $leveledArray[$key] );
            }

            $left  = array_slice($leveledArray, 0, $keyNeighborIndex + ( $after ? 1 : 0 ));
            $right = array_slice($leveledArray, $keyNeighborIndex);

            $leveledArray = $left + [ $key => $value ] + $right;
        } else {
            array_set($leveledArray, $key, $value);
        }

        return array_set($array, $leveledKey, $leveledArray);
    }
}
