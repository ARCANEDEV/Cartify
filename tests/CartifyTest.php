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
    }

    /** @test */
    public function it_can_add_one_product_to_cart()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->cartify->add('293ad', 'Product 1', 1, 9.99, ['size' => 'large']);
    }

    /** @test */
    public function it_can_add_many_products_to_cart()
    {
        $times = 10;
        $this->events->shouldReceive('fire')->times($times)->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->times($times)->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        for($i = 1; $i <= $times; $i++) {
            $this->cartify->add('293ad' . $i, 'Product ' . $i, 1, 9.99);
        }
        $this->assertEquals($times, $this->cartify->count());
    }

    /** @test */
    public function it_can_add_with_numeric_id()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->cartify->add(12345, 'Product 1', 1, 9.99, ['size' => 'large']);
    }

    /** @test */
    public function it_can_add_array()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
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
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.batch', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.batched', m::type('array'));
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
        $data = [
            'id'        => '293ad',
            'name'      => 'Product 1',
            'qty'       => 1,
            'price'     => 9.99,
            'options'   => ['size' => 'large', 'color' => 'red']
        ];
        list($id, $name, $qty, $price, $options) = array_values($data);

        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

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
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::any());

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
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::any());

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
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::any());

        $this->cartify->add('293ad', 'Product 1', 1, 'im-not-a-number');
    }

    /** @test */
    public function it_can_update_existing_product()
    {
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 3, 9.99);
        $this->cartify->add('293ad', 'Product 1', 4, 9.99);

        $this->assertEquals(7, $this->cartify->content()->first()->qty);
    }

    /** @test */
    public function it_can_update_quantity()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', 2);

        $this->assertEquals(2, $this->cartify->content()->first()->qty);
    }

    /** @test */
    public function testCartCanUpdateItem()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', ['name' => 'Product 2']);

        $this->assertEquals('Product 2', $this->cartify->content()->first()->name);
    }

    /** @test */
    public function testCartCanUpdateItemToNumericId()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', ['id' => 12345]);

        $this->assertEquals(12345, $this->cartify->content()->first()->id);
    }

    /** @test */
    public function testCartCanUpdateOptions()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99, ['size' => 'S']);
        $this->cartify->update('9be7e69d236ca2d09d2e0838d2c59aeb', ['options' => ['size' => 'L']]);

        $this->assertEquals('L', $this->cartify->content()->first()->options->size);
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\InvalidProductIDException
     */
    public function testCartThrowsExceptionOnInvalidRowId()
    {
        $this->cartify->update('invalidRowId', 1);
    }

    /** @test */
    public function testCartCanRemove()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.delete', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.deleted', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->remove('8cbf215baa3b757e910e5305ab981172');

        $this->assertTrue($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function testCartCanRemoveOnUpdate()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.delete', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.deleted', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', 0);

        $this->assertTrue($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function testCartCanRemoveOnNegativeUpdate()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.update', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.updated', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.delete', m::type('string'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.deleted', m::type('string'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->update('8cbf215baa3b757e910e5305ab981172', -1);

        $this->assertTrue($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function testCartCanGet()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $item = $this->cartify->get('8cbf215baa3b757e910e5305ab981172');

        $this->assertEquals('293ad', $item->id);
    }

    /** @test */
    public function testCartCanGetContent()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Cart', $this->cartify->content());
        $this->assertFalse($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function testCartCanDestroy()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.destroy', null);
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.destroyed', null);

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->destroy();

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Cart', $this->cartify->content());
        $this->assertTrue($this->cartify->content()->isEmpty());
    }

    /** @test */
    public function testCartCanGetTotal()
    {
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 1, 19.99);

        $this->assertEquals(29.98, $this->cartify->total());
    }

    /** @test */
    public function testCartCanGetItemCount()
    {
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 2, 19.99);

        $this->assertEquals(3, $this->cartify->count());
    }

    /** @test */
    public function testCartCanGetRowCount()
    {
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);
        $this->cartify->add('986se', 'Product 2', 2, 19.99);

        $this->assertEquals(2, $this->cartify->count(false));
    }

    /** @test */
    //public function testCartCanSearch()
    //{
    //    $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
    //    $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
    //
    //    $this->cartify->add('293ad', 'Product 1', 1, 9.99);
    //    $searchResult = $this->cartify->search(['id' => '293ad']);
    //
    //    $this->assertEquals('8cbf215baa3b757e910e5305ab981172', $searchResult[0]);
    //}

    /** @test */
    //public function testCartCanHaveMultipleInstances()
    //{
    //    $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
    //    $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
    //
    //    $this->cartify->instance('firstInstance')->add('293ad', 'Product 1', 1, 9.99);
    //    $this->cartify->instance('secondInstance')->add('986se', 'Product 2', 1, 19.99);
    //
    //    $this->assertTrue($this->cartify->instance('firstInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
    //    $this->assertFalse($this->cartify->instance('firstInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
    //    $this->assertTrue($this->cartify->instance('secondInstance')->content()->has('22eae2b9c10083d6631aaa023106871a'));
    //    $this->assertFalse($this->cartify->instance('secondInstance')->content()->has('8cbf215baa3b757e910e5305ab981172'));
    //}

    /** @test */
    //public function testCartCanSearchInMultipleInstances()
    //{
    //    $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
    //    $this->events->shouldReceive('fire')->twice()->with(Cartify::EVENT_KEY . '.added', m::type('array'));
    //
    //    $this->cartify->instance('firstInstance')->add('293ad', 'Product 1', 1, 9.99);
    //    $this->cartify->instance('secondInstance')->add('986se', 'Product 2', 1, 19.99);
    //
    //    $this->assertEquals($this->cartify->instance('firstInstance')->search(['id' => '293ad']), ['8cbf215baa3b757e910e5305ab981172']);
    //    $this->assertEquals($this->cartify->instance('secondInstance')->search(['id' => '986se']), ['22eae2b9c10083d6631aaa023106871a']);
    //}

    /**
     * @test
     *
     * @expectedException \Arcanedev\Cartify\Exceptions\InvalidCartInstanceException
     */
    public function testCartThrowsExceptionOnEmptyInstance()
    {
        $this->cartify->instance();
    }

    /** @test */
    public function testCartReturnsCartCollection()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);

        $this->assertInstanceOf('Arcanedev\Cartify\Entities\Cart', $this->cartify->content());
    }

    /** @test */
    public function testCartCollectionHasCartRowCollection()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);

        $this->assertInstanceOf('Arcanedev\\Cartify\\Entities\\Product', $this->cartify->content()->first());
    }

    /** @test */
    public function testCartRowCollectionHasCartRowOptionsCollection()
    {
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.add', m::type('array'));
        $this->events->shouldReceive('fire')->once()->with(Cartify::EVENT_KEY . '.added', m::type('array'));

        $this->cartify->add('293ad', 'Product 1', 1, 9.99);

        $this->assertInstanceOf(
            'Arcanedev\Cartify\Entities\ProductOptions',
            $this->cartify->content()->first()->options
        );
    }
}
