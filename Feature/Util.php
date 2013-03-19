<?php

/**
 * Utility functions.
 */
class Feature_Util {

    /*
     * Get the value from an array if it is in fact an array and
     * contain the key, a default value otherwise.
     */
    public static function arrayGet($array, $key, $default = null) {
        return is_array($array) && array_key_exists($key, $array) ? $array[$key] : $default;
    }

}