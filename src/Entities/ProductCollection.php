<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Bases\Collection;
use Arcanedev\Cartify\Contracts\Arrayable;
use Arcanedev\Cartify\Contracts\ProductOptionsInterface;
use Closure;

/**
 * Class ProductCollection
 * @package Arcanedev\Cartify\Entities
 */
class ProductCollection extends Collection implements ProductOptionsInterface, Arrayable
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the first product from the collection
     *
     * @param  Closure   $callback
     * @param  mixed      $default
     *
     * @return Product|null
     */
    public function first(Closure $callback = null, $default = null)
    {
        return parent::first($callback, $default);
    }

    /**
     * Get a product from the collection by hashed id.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     *
     * @return Product|null
     */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }

    /**
     * Add a product
     *
     * @param Product $newProduct
     *
     * @return self
     */
    public function add(Product $newProduct)
    {
        if ($this->has($newProduct->hashedId)) {
            $newProduct->qty += $this->get($newProduct->hashedId)->qty;
        }

        $this->put($newProduct->hashedId, $newProduct);

        return $this;
    }

    /**
     * Add a new product to collection
     *
     * @param  array $attribute
     *
     * @return self
     */
    public function addProduct(array $attribute)
    {
        return $this->add(new Product($attribute));
    }

    /**
     * Update a product
     *
     * @param  string $hashedId
     * @param  array  $attributes
     *
     * @return $this
     */
    public function updateProduct($hashedId, array $attributes)
    {
        if ($this->has($hashedId)) {
            $product = $this->get($hashedId);

            $this->deleteProduct($hashedId);

            $product->update($attributes);
            $this->put($product->hashedId, $product);
        }

        return $this;
    }

    /**
     * Delete a product form collection by id
     *
     * @param  string $hashedId
     *
     * @return self
     */
    public function deleteProduct($hashedId)
    {
        if ($this->has($hashedId)) {
            $this->forget($hashedId);
        }

        return $this;
    }

    /**
     * Delete a product from collection
     *
     * @param  Product $product
     *
     * @return self
     */
    public function delete(Product $product)
    {
        return $this->deleteProduct($product->hashedId);
    }

    /**
     * Get the number of items
     *
     * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
     *
     * @return int
     */
    public function count($totalItems = false)
    {
        if ( ! $totalItems) {
            return parent::count();
        }

        return $this->sum(function(Product $product) {
            return $product->getQty();
        });
    }

    /**
     * Get products total without VAT
     *
     * @return double
     */
    public function getTotal()
    {
        return $this->sum(function(Product $product) {
            return $product->getTotal();
        });
    }

    /**
     * Get total price
     *
     * @return double
     */
    public function getTotalPrice()
    {
        return $this->sum(function(Product $product) {
            return $product->getTotalPrice();
        });
    }

    /**
     * Delete all products
     */
    public function clear()
    {
        if ( ! $this->isEmpty()) {
            $this->items = [];
        }

        return $this;
    }
}
