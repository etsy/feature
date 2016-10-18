<?php

namespace CafeMedia\Feature;

/**
 * Utility functions.
 *
 * Class Util
 * @package CafeMedia\Feature
 */
class Util
{
    /**
     * Get the value from an array if it is in fact an array and
     * contain the key, a default value otherwise.
     *
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public static function arrayGet($array, $key, $default = null)
    {
        return is_array($array) && isset($array[$key]) ? $array[$key] : $default;
    }
}
