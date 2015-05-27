<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Support\Collection;

/**
 * Class ProductOptions
 * @package Arcanedev\Cartify\Entities
 */
class ProductOptions extends Collection
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /* ------------------------------------------------------------------------------------------------
     |  Getters and Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get an attribute from option collection
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return null;
    }

    /**
     * Get an attribute from option collection
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function __set($key, $value)
    {
        $this->put($key, $value);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Function
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Update options
     *
     * @param  array $options
     *
     * @return self
     */
    public function update(array $options)
    {
        $this->items = array_merge($this->items, $options);

        return $this;
    }

    /**
     * Delete options
     */
    public function delete()
    {
        if (count($keys = func_get_args())) {
            foreach ($keys as $key) {
                $this->forget($key);
            }
        }
        else {
            $this->clear();
        }
    }

    /**
     * Delete all options
     *
     * @return $this
     */
    public function clear()
    {
        $this->items = [];

        return $this;
    }
}
