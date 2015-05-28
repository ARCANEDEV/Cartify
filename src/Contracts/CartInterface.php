<?php namespace Arcanedev\Cartify\Contracts;

interface CartInterface
{
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
    public function search($search);
}
