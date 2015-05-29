<?php namespace Arcanedev\Cartify\Contracts;

use Arcanedev\Cartify\Entities\Cart;
use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Entities\ProductCollection;
use Arcanedev\Cartify\Exceptions\ProductNotFoundException;

interface CartInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all products
     *
     * @return ProductCollection
     */
    public function all();

    /**
     * Get a product
     *
     * @param  $hashedId
     *
     * @return Product|null
     */
    public function get($hashedId);

    /**
     * Add a product
     *
     * @param Product $product
     *
     * @return self
     */
    public function add(Product $product);

    /**
     * Add a product
     *
     * @param  array $attributes
     *
     * @return self
     */
    public function addProduct(array $attributes);

    /**
     * @param  string  $hashedId
     * @param  Product $product
     *
     * @return self
     */
    public function update($hashedId, Product $product);

    /**
     * Update a product
     *
     * @param  string $hashedId
     * @param  array  $attributes
     *
     * @throws ProductNotFoundException
     *
     * @return Cart
     */
    public function updateProduct($hashedId, array $attributes);

    /**
     * Check if a product exists in the collection
     *
     * @param  string $hashedId
     *
     * @return bool
     */
    public function hasProduct($hashedId);
}
