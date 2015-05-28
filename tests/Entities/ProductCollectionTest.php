<?php namespace Arcanedev\Cartify\Tests\Entities;

use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Entities\ProductCollection;
use Arcanedev\Cartify\Tests\TestCase;

class ProductCollectionTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var ProductCollection */
    private $products;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->products = new ProductCollection;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->products);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(
            'Arcanedev\\Cartify\\Entities\\ProductCollection',
            $this->products
        );
        $this->assertCount(0, $this->products);
    }

    /** @test */
    public function it_can_add_products()
    {
        $this->assertCount(0, $this->products);

        for ($i = 1; $i <= 10; $i++) {
            $this->products->add($this->makeRandomProduct());
            $this->assertCount($i, $this->products);
        }
    }

    /** @test */
    public function it_cant_add_same_product_but_update_the_quantity()
    {
        $product = $this->makeRandomProduct();
        $qty = 0;

        for ($i = 1; $i <= 10; $i++) {
            $qty += $product->qty;
            $this->products->add($product);
            $this->assertCount(1, $this->products);
            $this->assertEquals($qty, $this->products->get($product->id)->qty);
        }
    }

    /** @test */
    public function it_can_delete_a_product_from_collection()
    {
        $product = $this->makeRandomProduct();

        $this->products->add($product);
        $this->assertCount(1, $this->products);

        $this->products->delete($product);
        $this->assertCount(0, $this->products);

        $this->products->addProduct($this->getRandomProductData());
        $this->assertCount(1, $this->products);

        $product = $this->products->first();
        $this->products->deleteProduct($product->id);
        $this->assertCount(0, $this->products);
    }
}
