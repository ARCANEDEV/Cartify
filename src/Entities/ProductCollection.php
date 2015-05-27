<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\ProductInterface;
use Arcanedev\Cartify\Support\Collection;

/**
 * Class ProductCollection
 * @package Arcanedev\Cartify\Entities
 */
class ProductCollection extends Collection
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function push(ProductInterface $product)
    {
        $this->put($product->getId(), $product);
    }
}
