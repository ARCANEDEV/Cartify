<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\CartInterface;
use Arcanedev\Cartify\Exceptions\ProductNotFoundException;
use Countable;

/**
 * Class Cart
 * @package Arcanedev\Cartify\Entities
 */
class Cart implements CartInterface, Countable
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Cart instance
     *
     * @var string
     */
    protected $instance = 'main';

    /**
     * The collection of products
     *
     * @var ProductCollection
     */
    protected $products;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Constructor
     *
     * @param string $instance
     */
    public function __construct($instance = 'main')
    {
        $this->setInstance($instance);

        $this->products = new ProductCollection;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    public function instance()
    {
        return $this->instance;
    }

    /**
     * Set cart instance
     *
     * @param  string $instance
     *
     * @return self
     */
    protected function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get all products
     *
     * @return ProductCollection
     */
    public function all()
    {
        return $this->products;
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
        $this->products->put($product->getHashedId(), $product);

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

    /**
     * Get a product
     *
     * @param  $hashedId
     *
     * @return Product|null
     */
    public function get($hashedId)
    {
        // TODO: Add hashedId check
        if ($this->products->has($hashedId)) {
            return $this->products->get($hashedId);
        }

        return null;
    }

    /**
     * @param  string  $hashedId
     * @param  Product $product
     *
     * @return self
     */
    public function update($hashedId, Product $product)
    {
        $this->updateProduct($hashedId, $product->toArray());

        return $this;
    }

    /**
     * Update a product
     *
     * @param  string $hashedId
     * @param  array  $attributes
     *
     * @throws ProductNotFoundException
     *
     * @return self
     */
    public function updateProduct($hashedId, array $attributes)
    {
        $this->findOrFail($hashedId);
        $this->products->updateProduct($hashedId, $attributes);

        return $this;
    }

    /**
     * Delete a product
     *
     * @param  string $hashedId
     *
     * @throws ProductNotFoundException
     *
     * @return self
     */
    public function delete($hashedId)
    {
        $this->findOrFail($hashedId);

        $this->products->deleteProduct($hashedId);

        return $this;
    }

    /**
     * Check if a product exists in the collection
     *
     * @param  string $hashedId
     *
     * @return bool
     */
    public function hasProduct($hashedId)
    {
        return $this->products->has($hashedId);
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function count()
    {
        return $this->products->count();
    }

    /**
     * Delete all products
     *
     * @return $this
     */
    public function clear()
    {
        if ( ! $this->products->isEmpty()) {
            $this->products->clear();
        }

        return $this;
    }

    /**
     * Find a product or fail
     *
     * @param  string $hashedId
     *
     * @throws ProductNotFoundException
     */
    private function findOrFail($hashedId)
    {
        if ( ! $this->hasProduct($hashedId)) {
            throw new ProductNotFoundException('Product not found !');
        }
    }

    /**
     * Get first product
     *
     * @return Product|null
     */
    public function first()
    {
        return $this->products->first();
    }
}
