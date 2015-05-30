<?php namespace Arcanedev\Cartify\Contracts;

use Arcanedev\Cartify\Cartify;
use Arcanedev\Cartify\Entities\Cart;
use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Exceptions\CartNotFoundException;
use Arcanedev\Cartify\Exceptions\InvalidCartInstanceException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;

interface CartifyInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the current cart instance
     *
     * @param  string $instance Cart instance name
     *
     * @throws InvalidCartInstanceException
     *
     * @return Cartify
     */
    public function instance($instance = null);

    /**
     * Add a product to the cart
     *
     * @param  string|array  $id       Unique ID of the product|Item formated as array|Array of items
     * @param  string        $name
     * @param  int           $qty
     * @param  double        $price
     * @param  array         $options
     *
     * @return Cartify
     */
    public function add($id, $name = null, $qty = null, $price = null, array $options = []);

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string        $hashedId
     * @param  integer|array $attribute New quantity|Array of attributes
     *
     * @throws InvalidProductIDException
     *
     * @return Cart
     */
    public function update($hashedId, $attribute);

    /**
     * Remove a row from the cart
     *
     * @param  string $hashedId The hashed Id of the product
     *
     * @throws InvalidProductIDException
     *
     * @return Cartify
     */
    public function remove($hashedId);

    /**
     * Get a row of the cart by its ID
     *
     * @param  string  $hashedId  The ID of the row to fetch
     *
     * @return Product
     */
    public function get($hashedId);
}
