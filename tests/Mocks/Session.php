<?php namespace Arcanedev\Cartify\Tests\Mocks;

use Arcanedev\Cartify\Contracts\SessionHandler;

class Session implements SessionHandler
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    protected $session = [];

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function has($key)
    {
        return isset($this->session[$key]);
    }

    public function get($key)
    {
        return $this->session[$key];
    }

    public function put($key, $value)
    {
        $this->session[$key] = $value;
    }
}
