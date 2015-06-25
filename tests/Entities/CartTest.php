<?php namespace Arcanedev\Cartify\Tests\Entities;

use Arcanedev\Cartify\Entities\Cart;
use Arcanedev\Cartify\Tests\TestCase;

class CartTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    const CART_CLASS = 'Arcanedev\\Cartify\\Entities\\Cart';
    /** @var Cart */
    private $cart;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->cart = new Cart;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->cart);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(self::CART_CLASS, $this->cart);
        $this->assertEquals('main', $this->cart->instance());
        $this->assertCount(0, $this->cart);
        $this->assertCount(0, $this->cart->all());
    }

    /** @test */
    public function it_can_add_and_get_and_delete_a_product()
    {
        $this->assertCount(0, $this->cart);

        $productData = $this->getRandomProductData();
        $this->cart->addProduct($productData);

        $this->assertCount(1, $this->cart);

        $hashedId = hash_id($productData['id'], $productData['options']);

        $product = $this->cart->get($this->faker->word);
        $this->assertNull($product);

        $product = $this->cart->get($hashedId);

        $this->assertNotNull($product);
        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Product', $product);

        $this->cart->delete($hashedId);
        $this->assertCount(0, $this->cart);
        $product = $this->cart->get($this->faker->word);
        $this->assertNull($product);
    }

    /** @test */
    public function it_can_add_products_and_clear_all()
    {
        $this->assertCount(0, $this->cart);

        for ($i = 1; $i <= 10; $i++) {
            $this->cart->add($this->makeRandomProduct());
            $this->assertCount($i, $this->cart);
        }

        $this->cart->clear();
        $this->assertCount(0, $this->cart);
    }

    /** @test */
    public function it_can_get_first_product()
    {
        $this->assertCount(0, $this->cart);

        $productData = $this->getRandomProductData();
        $this->cart->addProduct($productData);

        $this->assertCount(1, $this->cart);
        $product = $this->cart->first();
        $this->assertNotNull($product);
        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Product', $product);
    }

    /** @test */
    public function it_can_update_a_product_one()
    {
        $this->assertCount(0, $this->cart);

        $productData = $this->getRandomProductData();
        $this->cart->addProduct($productData);

        $this->assertCount(1, $this->cart);

        $hashedId = hash_id($productData['id'], $productData['options']);
        $product = $this->cart->get($hashedId);
        $this->assertEquals($productData['name'], $product->name);
        $updatedProduct = [
            'name'  => 'Awesome product',
            'qty'   => 400
        ];
        $productData = array_merge($productData, $updatedProduct);

        $this->cart->updateProduct($hashedId, $updatedProduct);
        $product = $this->cart->get($hashedId);
        $this->assertEquals($productData['name'], $product->name);
        $this->assertEquals($productData['qty'],  $product->qty);
    }

    /** @test */
    public function it_can_update_a_product_two()
    {
        $this->assertCount(0, $this->cart);

        $productData = $this->getRandomProductData();
        $this->cart->addProduct($productData);

        $this->assertCount(1, $this->cart);

        $hashedId = hash_id($productData['id'], $productData['options']);
        $product = $this->cart->get($hashedId);
        $this->assertEquals($productData['name'], $product->name);
        $updatedProduct = [
            'name'  => 'Awesome product',
            'qty'   => 400
        ];
        $productData = array_merge($productData, $updatedProduct);

        $product->update($productData);
        $this->cart->update($hashedId, $product);

        $product = $this->cart->get($product->hashedId);
        $this->assertEquals($productData['name'], $product->name);
        $this->assertEquals($productData['qty'],  $product->qty);
    }

    /** @test */
    public function it_can_get_total_and_total_price()
    {
        $total = 0;
        $totalPrice = 0;

        for ($i = 1; $i <= 10; $i++) {
            $product     = $this->makeRandomProduct();
            $total      += $product->getTotal();
            $totalPrice += $product->getTotalPrice();
            $this->cart->add($product);
        }

        $this->assertEquals($total, $this->cart->getTotal());
        $this->assertEquals($totalPrice, $this->cart->getTotalPrice());
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\ProductNotFoundException
     */
    public function it_must_throw_product_not_found_exception_on_update()
    {
        $this->cart->updateProduct('asdf2015', []);
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\ProductNotFoundException
     */
    public function it_must_throw_product_not_found_exception_on_update_object()
    {
        $this->cart->update('asdf2015', $this->makeRandomProduct());
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\ProductNotFoundException
     */
    public function it_must_throw_product_not_found_exception_on_delete()
    {
        $this->cart->delete('asdf2015');
    }
}
