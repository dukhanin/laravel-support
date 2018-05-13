<?php
namespace Dukhanin\Support;

class Arr
{
    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * до элемента с ключем $beforeKey
     *
     * В случае, если $beforeKey не указан — добавляет новый
     * элемент в начало массива
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyBefore
     *
     * @return array
     */
    public static function before(&$array, $key, $value, $keyBefore = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyBefore);
    }

    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * после элемента с ключем $keyAfter
     *
     * В случае, если $keyAfter не указан — добавляет новый
     * элемент в конец массива
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyAfter
     *
     * @return array
     */
    public static function after(&$array, $key, $value, $keyAfter = null)
    {
        return static::beforeOrAfter($array, $key, $value, $keyAfter, true);
    }

    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * до элемента с ключем $beforeKey
     *
     * $beforeKey может быть указан в dot-notation формате, таким
     * образом новый элемент может быть вставлен в любой уровень
     * многомерного массива.
     *
     * При использовании dot-notation в $beforeKey, в $key указывается
     * ключ внутри последнего уровня массива, без dot-notation
     *
     * В случае, если $beforeKey не указан — добавляет новый
     * элемент в начало массива
     *
     * <code>
     * $arr = [
     *     'one' => 'One',
     *     'two' => 'Two',
     *     'three' => [
     *         'a' => 'A',
     *         'c' => 'C'
     *     ],
     * ];
     *
     * Arr::before('b', 'B', 'three.c');
     * </code>
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyBefore
     *
     * @return array
     */
    public static function beforeDotNotation(&$array, $key, $value, $keyBefore = null)
    {
        return static::beforeOrAfterDotNotation($array, $key, $value, $keyBefore);
    }

    /**
     * Добавляет элемент $value с ключем $key в массив $array
     * после элемента с ключем $keyAfter
     *
     * $keyAfter может быть указан в dot-notation формате, таким
     * образом новый элемент может быть вставлен в любой уровень
     * многомерного массива
     *
     * При использовании dot-notation в $keyAfter, в $key указывается
     * ключ внутри последнего уровня массива, без dot-notation
     *
     * В случае, если $keyAfter не указан — добавляет новый
     * элемент в конец массива
     *
     * <code>
     * $arr = [
     *     'one' => 'One',
     *     'two' => 'Two',
     *     'three' => [
     *         'a' => 'A',
     *         'c' => 'C'
     *     ],
     * ];
     *
     * Arr::after('b', 'B', 'three.a');
     * </code>
     *
     * @param mixed $array
     * @param string|integer $key
     * @param mixed $value
     * @param string|integer|null $keyAfter
     *
     * @return array
     */
    public static function afterDotNotation(&$array, $key, $value, $keyAfter = null)
    {
        return static::beforeOrAfterDotNotation($array, $key, $value, $keyAfter, true);
    }

    /**
     * Реализация вставлки элемента $value с ключем $key
     * в массив $array до или после элемента c ключем $keyNeighbor
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyNeighbor
     * @param bool $after
     *auth
     * @return array
     */
    protected static function beforeOrAfter(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (! is_array($array)) {
            $array = [];
        }

        if (is_null($keyNeighbor) || ! array_key_exists($keyNeighbor, $array)) {
            if (is_null($key)) {
                $after ? array_push($array, $value) : array_unshift($array, $value);
            } else {
                $array = $after ? $array + [$key => $value] : [$key => $value] + $array;
            }

            return $array;
        }

        if (array_key_exists($key, $array)) {
            unset($array[$key]);
        }

        $keyNeighborIndex = array_search($keyNeighbor, array_keys($array));
        $sliceLength = $keyNeighborIndex + ($after ? 1 : 0);

        return $array = array_slice($array, 0, $sliceLength) + [$key => $value] + array_slice($array, $sliceLength);
    }

    /**
     * Реализация вставлки элемента $value с ключем $key
     * в массив $array до или после элемента c ключем $keyNeighbor
     * с испольованием dot-notation
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @param string|null $keyNeighbor
     * @param bool $after
     *
     * @return array
     */
    protected static function beforeOrAfterDotNotation(&$array, $key, $value, $keyNeighbor = null, $after = false)
    {
        if (! is_array($array)) {
            $array = [];
        }

        if (is_null($keyNeighbor)) {
            return $array = $after ? static::after($array, $key, $value) : static::before($array, $key, $value);
        }

        $segments = explode('.', $keyNeighbor);
        $keyNeighborLast = array_pop($segments);

        if (count($segments) > 0) {
            $leveledKey = implode('.', $segments);
            $leveledArray = array_get($array, $leveledKey);

            if (! is_array($leveledArray)) {
                return $array;
            }
        } else {
            $leveledKey = null;
            $leveledArray = $array;
        }

        if (! array_key_exists($keyNeighborLast, $leveledArray)) {
            array_forget($leveledArray, $key);

            $leveledArray = $after ? array_set($leveledArray, $key, $value) : array_prepend($leveledArray, $value,
                $key);

            return $array = array_set($array, $leveledKey, $leveledArray);
        }

        if (isset($leveledArray[$key])) {
            unset($leveledArray[$key]);
        }

        $keyNeighborIndex = array_search($keyNeighborLast, array_keys($leveledArray));
        $sliceLength = $keyNeighborIndex + ($after ? 1 : 0);

        $leveledArray = array_slice($leveledArray, 0, $sliceLength) + [$key => $value] + array_slice($leveledArray,
                $sliceLength);

        return array_set($array, $leveledKey, $leveledArray);
    }
}
