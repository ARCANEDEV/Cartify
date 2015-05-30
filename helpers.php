<?php

if ( ! function_exists('hash_id')) {
    /**
     * Generate a unique id for the new product
     *
     * @param  string  $id       Unique ID of the product
     * @param  array   $options  Array of additional options
     *
     * @return string
     */
    function hash_id($id, $options) {
        ksort($options);

        return md5($id . serialize($options));
    }
}

if ( ! function_exists('is_multi_array')) {
    /**
     * Check if the array is a multidimensional array
     *
     * @param  array   $array
     *
     * @return boolean
     */
    function is_multi_array(array $array) {
        return is_array(reset($array));
    }
}
