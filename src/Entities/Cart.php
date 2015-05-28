<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\CartInterface;

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
    /**
     * An array with the item ID and optional options
     *
     * @param $search
     *
     * @return mixed
     */
    public function search($search)
    {
        // TODO: Implement search() method.
    }
}
