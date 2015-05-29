<?php namespace Arcanedev\Cartify\Contracts;

interface Arrayable
{
    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray();
}
