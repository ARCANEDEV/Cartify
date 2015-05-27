<?php namespace Arcanedev\Cartify\Contracts;

interface EventHandler
{
    public function fire($name, $id = null);
}
