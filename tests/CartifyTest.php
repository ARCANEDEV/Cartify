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

    /**
     * @var \Mockery\MockInterface
     */
    private $events;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();
        $session       = new \Arcanedev\Cartify\Tests\Mocks\Session;
        $this->events  = m::mock('Arcanedev\\Cartify\\Contracts\\EventHandler');

        $this->cartify = new Cartify($session, $this->events);
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
        $this->assertInstanceOf(self::CARTIFY_CLASS, $this->cartify->instance('main'));
        $this->assertEquals(0, $this->cartify->total());
    }

    /** @test */
    public function it_can_add_one_product_to_cart()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99, ['size' => 'large']);
    }

    /** @test */
    public function it_can_add_many_products_to_cart()
    {
        $times = 10;

        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
            'delete'    => m::type('string'),
            'deleted'   => m::type('string'),
        ], $times);

        for($i = 1; $i <= $times; $i++) {
            $this->cartify->add('293ad' . $i, 'Product ' . $i, 1, 9.99);
        }

        $this->assertEquals($times, $this->cartify->count());
    }

    /** @test */
    public function it_can_add_with_numeric_id()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $this->cartify->add(12345, 'Product 1', 1, 9.99, ['size' => 'large']);
    }

    /** @test */
    public function it_can_add_array()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $this->cartify->add([
            'id'        => '293ad',
            'name'      => 'Product 1',
            'qty'       => 1,
            'price'     => 9.99,
            'options'   => [
                'size' => 'large'
            ]
        ]);
    }

    /** @test */
    public function it_can_add_batch()
    {
        $this->registerEvents([
            'batch'     => m::type('array'),
            'batched'   => m::type('array'),
        ]);

        $this->cartify->add([
            [
                'id'        => '293ad',
                'name'      => 'Product 1',
                'qty'       => 1,
                'price'     => 10.00,
                'vat'       => 20.00,
            ],[
                'id'        => '4832k',
                'name'      => 'Product 2',
                'qty'       => 1,
                'price'     => 10.00,
                'options'   => [
                    'size'  => 'large',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_add_multiple_options()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $data = [
            'id'        => '293ad',
            'name'      => 'Product 1',
            'qty'       => 1,
            'price'     => 9.99,
            'options'   => ['size' => 'large', 'color' => 'red']
        ];
        list($id, $name, $qty, $price, $options) = array_values($data);

        $this->cartify->add($id, $name, $qty, $price, $options);
        $cart = $this->cartify->get(hash_id($id, $options));

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\ProductOptions', $cart->options);
        $this->assertEquals('large', $cart->options->size);
        $this->assertEquals('red', $cart->options->color);
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\Cartify\Exceptions\InvalidProductException
     * @expectedExceptionMessage These attributes are missing or empty: id, name, qty, price.
     */
    public function must_throw_exception_on_empty_values()
    {
        $this->registerEvents(['add' => m::any()]);

        $this->cartify->add('', '', '', '');
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\Cartify\Exceptions\InvalidQuantityException
     * @expectedExceptionMessage The product quantity must be a numeric value.
     */
    public function must_throw_exception_on_none_numeric_quantity()
    {
        $this->registerEvents(['add' => m::any()]);

        $this->cartify->add('293ad', 'Product 1', 'im-not-a-number', 9.99);
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\Cartify\Exceptions\InvalidPriceException
     * @expectedExceptionMessage The product price must be a numeric|double value.
     */
    public function must_throw_exception_on_none_numeric_price()
    {
        $this->registerEvents(['add' => m::any()]);

        $this->cartify->add('293ad', 'Product 1', 1, 'im-not-a-number');
    }

    /** @test */
    public function it_can_update_existing_product()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ], 2);

        $this->cartify->add('293ad', 'Product 1', 3, 9.99);
        $this->cartify->add('293ad', 'Product 1', 4, 9.99);
        $cart = $this->cartify->content();

        $this->assertEquals(7, $cart->first()->qty);
    }

    /** @test */
    public function it_can_update_quantity()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', 2);
        $cart = $this->cartify->content();

        $this->assertEquals(2, $cart->first()->qty);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', ['name' => 'Product 2']);
        $cart = $this->cartify->content();

        $this->assertEquals('Product 2', $cart->first()->name);
    }

    /** @test */
    public function it_can_update_a_product_to_numeric_id()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', ['id' => 12345]);

        $this->assertEquals(12345, $this->cartify->content()->first()->id);
    }

    /** @test */
    public function it_can_update_product_options()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99, [
            'size' => 'S'
        ]);
        $this->cartify->update('9be7e69d236ca2d09d2e0838d2c59aeb', [
            'options' => [
                'size' => 'L'
            ]
        ]);
        $cart = $this->cartify->content();

        $this->assertEquals('L', $cart->first()->options->size);
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\InvalidProductIDException
     */
    public function it_must_throws_invalid_product_id_exception()
    {
        $this->cartify->update('invalid-id', 1);
    }

    /** @test */
    public function it_can_remove_a_product()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'delete'    => m::type('string'),
            'deleted'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->remove('8cbf215baa3b757e910e5305ab981172');
        $cart = $this->cartify->content();

        $this->assertTrue($cart->isEmpty());
    }

    /** @test */
    public function it_can_remove_on_update()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
            'delete'    => m::type('string'),
            'deleted'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', 0);

        $this->assertTrue($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function it_can_remove_on_negative_update()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'update'    => m::type('string'),
            'updated'   => m::type('string'),
            'delete'    => m::type('string'),
            'deleted'   => m::type('string'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', -1);

        $cart = $this->cartify->content();
        $this->assertTrue($cart->isEmpty());
    }

    /** @test */
    public function it_can_get_a_product()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $product = $this->cartify->get('8cbf215baa3b757e910e5305ab981172');

        $this->assertEquals('293ad', $product->id);
    }

    /** @test */
    public function it_can_get_content()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $cart = $this->cartify->content();

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Cart', $cart);
        $this->assertFalse($cart->isEmpty());
        $this->assertCount(1, $cart);
    }

    /** @test */
    public function it_can_destroy_all()
    {
        $this->registerEvents([
            'add'       => m::type('array'),
            'added'     => m::type('array'),
            'destroy'   => m::type('null'),
            'destroyed' => m::type('null'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->destroy();

        $cart = $this->cartify->content();
        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Cart', $cart);
        $this->assertTrue($cart->isEmpty());
        $this->assertCount(0, $cart);
    }

    /** @test */
    public function it_can_get_total()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ], 2);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 1, 19.99);

        $this->assertEquals(29.98, $this->cartify->total());
    }

    /** @test */
    public function it_can_get_product_count()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ], 2);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 2, 19.99);

        $this->assertEquals(3, $this->cartify->count());
    }

    /** @test */
    public function testCartCanGetRowCount()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ], 2);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 2, 19.99);

        $this->assertEquals(2, $this->cartify->count(false));
    }

    /** @test */
    public function it_can_have_multiple_instances()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ], 2);

        $this->cartify->instance('main')->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->instance('whishlist')->add('986se', 'Product 2', 1, 19.99);

        $mainCart       = $this->cartify->instance('main')->content();
        $wishlistCart   = $this->cartify->instance('whishlist')->content();

        $this->assertTrue($mainCart->hasProduct('8cbf215baa3b757e910e5305ab981172'));
        $this->assertFalse($mainCart->hasProduct('22eae2b9c10083d6631aaa023106871a'));
        $this->assertTrue($wishlistCart->hasProduct('22eae2b9c10083d6631aaa023106871a'));
        $this->assertFalse($wishlistCart->hasProduct('8cbf215baa3b757e910e5305ab981172'));
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\InvalidCartInstanceException
     */
    public function it_must_invalid_cart_instance_exception_on_empty_instance()
    {
        $this->cartify->instance();
    }

    /** @test */
    public function it_can_return_cart()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $cart = $this->cartify->content();

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Cart', $cart);
        $this->assertCount(1, $cart);
    }

    /** @test */
    public function it_can_get_product_and_product_options()
    {
        $this->registerEvents([
            'add'   => m::type('array'),
            'added' => m::type('array'),
        ]);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $product = $this->cartify->content()->first();

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Product',        $product);
        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\ProductOptions', $product->options);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    private function registerEvents($events, $times = 1)
    {
        foreach ($events as $event => $type) {
            $this->events
                ->shouldReceive('fire')
                ->times($times)
                ->with(Cartify::EVENT_KEY . '.'. $event, $type);
        }
    }
}
