<?php namespace Arcanedev\Cartify\Tests;

use Arcanedev\Cartify\Cartify;
use Mockery as m;

class CartifyTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    const CARTIFY_CLASS = 'Arcanedev\\Cartify\\Cartify';

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Cartify */
    private $cartify;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $session       = m::mock('Arcanedev\\Cartify\\Contracts\\SessionHandler');
        $event         = m::mock('Arcanedev\\Cartify\\Contracts\\EventHandler');

        $this->cartify = new Cartify($session, $event);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->cartify);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(self::CARTIFY_CLASS, $this->cartify);
    }
}
