<?php namespace Arcanedev\Cartify\Tests\Entities;

use Arcanedev\Cartify\Entities\CartCollection;
use Arcanedev\Cartify\Tests\TestCase;

class CartCollectionTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    private $cartCollection;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->cartCollection = new CartCollection;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->cartCollection);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            'Arcanedev\\Cartify\\Entities\\CartCollection',
            $this->cartCollection
        );
    }
}
