<?php

namespace App\Models;

/**
 * Base enumerator class
 */
class BaseEnum
{
    /**
     * Constructs an array from constants present in subclass
     * Note that constants defined in base class are also defined in subclass
     * @return array
     */
    public static function getArray()
    {
        return self::getArraySuffix('');
    }

    /**
     * Retrieves array of items with specified suffix
     * @param string $suffix
     * @return array
     */
    public static function getArraySuffix($suffix)
    {
        $arr = explode('\\',get_called_class());
        $prefix = strtoupper(end($arr))."_";

        return self::getArrayPrefixSuffix($prefix, $suffix);
    }

    /**
     * Retrieves array of items with specified prefix and suffix
     * @param string $prefix
     * @param string $suffix
     * @return array
     */
    public static function getArrayPrefixSuffix($prefix, $suffix)
    {
        $class = new \ReflectionClass(get_called_class());
        $constants = $class->getConstants();

        $retarray = array();
        foreach ($constants as $key => $value)
            $retarray[$value] = ($prefix.$key.$suffix);

        return $retarray;
    }

    /**
     * Retrieves array of item values
     * @return array
     */
    public static function getValueArray()
    {
        $class = new \ReflectionClass(get_called_class());
        $constants = $class->getConstants();

        $retarray = array();
        foreach ($constants as $key => $value)
            $retarray[$value] = $value;

        return $retarray;
    }
}
