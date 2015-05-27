<?php namespace Arcanedev\Cartify\Contracts;

interface ProductOptionsInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray();
}
