<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Bases\Collection;
use Arcanedev\Cartify\Contracts\ProductOptionsInterface;

/**
 * Class ProductCollection
 * @package Arcanedev\Cartify\Entities
 */
class ProductCollection extends Collection implements ProductOptionsInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
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
        if ($this->has($newProduct->id)) {
            $newProduct->qty += $this->get($newProduct->id)->qty;
        }

        $this->put($newProduct->id, $newProduct);

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

    public function updateProduct($id, array $attributes)
    {
        if ($this->has($id)) {
            $product = $this->get($id);

            $this->deleteProduct($id);

            $product->update($attributes);
            $this->put($product->id, $product);
        }

        return $this;
    }

    /**
     * Delete a product form collection by id
     *
     * @param  string $id
     *
     * @return self
     */
    public function deleteProduct($id)
    {
        if ($this->has($id)) {
            $this->forget($id);
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
        return $this->deleteProduct($product->id);
    }
}
