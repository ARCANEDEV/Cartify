<?php namespace Arcanedev\Cartify\Contracts;

interface ProductOptionsInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get an option by key
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Set an option
     *
     * @param  string $key
     * @param  string $value
     *
     * @return mixed
     */
    public function put($key, $value);

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray();
}
