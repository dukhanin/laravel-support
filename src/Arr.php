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
        if (isset( $array[$key] )) {
            unset( $array[$key] );
        }

        if (is_null($keyNeighbor) || ! is_array($array) || ! array_key_exists($keyNeighbor, $array)) {
            $array = $after ? static::add($array, $key, $value) : static::prepend($array, $value, $key);
        }

        $keyNeighborIndex = array_search($keyNeighbor, array_keys($array));
        $sliceLength      = $keyNeighborIndex + ( $after ? 1 : 0 );

        return $array = array_slice($array, 0, $sliceLength) + [ $key => $value ] + array_slice($array, $sliceLength);
    }


    protected static function beforeOrAfterDotNotation(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (is_null($keyNeighbor)) {
            return $after ? static::set($array, $key, $value) : static::prepend($array, $value, $key);
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

            $leveledArray = array_slice($leveledArray, 0,
                    $keyNeighborIndex + ( $after ? 1 : 0 )) + [ $key => $value ] + array_slice($leveledArray,
                    $keyNeighborIndex);
        } else {
            array_set($leveledArray, $key, $value);
        }

        return array_set($array, $leveledKey, $leveledArray);
    }
}
