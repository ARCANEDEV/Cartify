<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\CartInterface;
use Arcanedev\Cartify\Exceptions\ProductNotFoundException;

/**
 * Class Cart
 * @package Arcanedev\Cartify\Entities
 */
class Cart implements CartInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @var ProductCollection
     */
    protected $products;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    public function __construct()
    {
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get a product
     *
     * @param  $id
     *
     * @return Product|null
     */
    public function getProduct($id)
    {
        if ($this->products->has($id)) {
            return $this->products->get($id);
        }

        return null;
    }

    /**
     * Add a product
     *
     * @param Product $product
     *
     * @return self
     */
    public function add(Product $product)
    {
        $this->products->put($product->id, $product);

        return $this;
    }

    /**
     * Add a product
     *
     * @param  array $attributes
     *
     * @return self
     */
    public function addProduct(array $attributes)
    {
        return $this->add(new Product($attributes));
    }

    public function update($hashedId, Product $product)
    {
        // TODO: Add checks
        $this->products->put($hashedId, $product);

        return $this;
    }

    /**
     * Update a product
     *
     * @param  string $id
     * @param  array  $attributes
     *
     * @throws ProductNotFoundException
     *
     * @return self
     */
    public function updateProduct($id, array $attributes)
    {
        if ( ! $this->hasProduct($id)) {
            throw new ProductNotFoundException('Product not found !');
        }

        /** @var Product $product */
        $product = $this->products->get($id);
        $product->update($attributes);

        return $this;
    }

    /**
     * Check if a product exists in the collection
     *
     * @param  string $id
     *
     * @return bool
     */
    public function hasProduct($id)
    {
        return $this->products->has($id);
    }
}
