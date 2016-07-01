<?php
namespace Dukhanin\Support;

use Illuminate\Support\Arr as BaseArr;

class Arr extends BaseArr
{

    public static function before($array, $key, $value, $keyBefore = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyBefore);
    }


    public static function after($array, $key, $value, $keyAfter = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyAfter, true);
    }


    protected static function beforeOrAfter($array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (is_null($keyNeighbor)) {
            return $after ? static::set($array, $value, $key) : static::prepend($array, $value, $key);
        }

        array_set($valueArray, $key, $value);
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

            $leveledArray = $left + $valueArray + $right;
        } else {
            array_set($leveledArray, $key, $value);
        }

        return array_set($array, $leveledKey, $leveledArray);
    }
}
