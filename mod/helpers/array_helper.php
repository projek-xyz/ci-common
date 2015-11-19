<?php

if (! function_exists('array_set_defaults')) {
    /**
     * Set default key => value in an array, it's useful to prevent unseted array keys
     * but you'd use that in next code.
     *
     * @param   array  $field       An array that will recieve default key
     * @param   array  $defaults    Array of keys which be default key of $field
     *                              Array must be associative array, which have
     *                              key and value. Key used as default key and
     *                              Value used as default value for $field param
     * @return  array
     */
    function array_set_defaults(array $array, array $defaults) {
        foreach ($defaults as $key => $val) {
            if (!array_key_exists($key, $array) AND !isset($array[$key])) {
                $array[$key] = $val;
            }
        }

        return $array;
    }
}

if (! function_exists('is_array_assoc')) {
    /**
     * Determine is $array an associative
     *
     * @param  array $array Array to check
     * @return bool
     */
    function is_array_assoc(array $array) {
        $array = array_keys($array);
        $array = array_filter($array, 'is_string');

        return (bool) count($array);
    }
}
