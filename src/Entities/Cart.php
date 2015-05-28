<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\CartInterface;
use Arcanedev\Cartify\Contracts\ProductInterface;
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
        // TODO: Implement __construct() method.
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function getProduct($id)
    {
        if ($this->products->has($id)) {
            return $this->products->get($id);
        }

        return null;
    }

    public function add(ProductInterface $product)
    {
        $this->products->put($product->id, $product);

        return $this;
    }

    public function addProduct(array $attributes)
    {
        return $this->addProduct(new Product($attributes));
    }

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
     * @param  string $id
     *
     * @return bool
     */
    public function hasProduct($id)
    {
        return $this->products->has($id);
    }
}
