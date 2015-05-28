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
