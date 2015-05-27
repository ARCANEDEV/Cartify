<?php namespace Arcanedev\Cartify\Contracts;

interface SessionHandler
{
    public function put($getInstance, $cart);

    public function get($getInstance);

    public function has($getInstance);
}
