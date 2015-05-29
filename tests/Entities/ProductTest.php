<?php namespace Arcanedev\Cartify\Tests\Entities;

use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Exceptions\InvalidPriceException;
use Arcanedev\Cartify\Exceptions\InvalidProductException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;
use Arcanedev\Cartify\Exceptions\InvalidQuantityException;
use Arcanedev\Cartify\Exceptions\InvalidVatException;
use Arcanedev\Cartify\Tests\TestCase;

class ProductTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    const PRODUCT_CLASS = 'Arcanedev\\Cartify\\Entities\\Product';

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Product */
    private $product;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->product = new Product($this->getRandomProductData());
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->product);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(self::PRODUCT_CLASS, $this->product);
    }

    /** @test */
    public function it_can_create_product()
    {
        // Create
        list($id, $name, $qty, $price, $options) = $this->getRandomProductData(true);

        $vat            = $vat = $this->getRandomVAT();

        // Then
        $this->product  = Product::create($id, $name, $qty, $price, $vat, $options);
        $hashedId       = $this->hashId($id, $options);
        $total          = $qty * $price;
        $vatPrice       = $total * ($vat / 100);
        $totalPrice     = $total + $vatPrice;

        // Assert
        $this->assertInstanceOf(self::PRODUCT_CLASS, $this->product);
        $this->assertEquals($hashedId,      $this->product->getHashedId());
        $this->assertEquals($id,            $this->product->getId());
        $this->assertEquals($name,          $this->product->getName());
        $this->assertEquals($qty,           $this->product->getQty());
        $this->assertEquals($price,         $this->product->getPrice());
        $this->assertEquals($total,         $this->product->getTotal());
        $this->assertEquals($vat,           $this->product->getVat());
        $this->assertEquals($vatPrice,      $this->product->getVatPrice());
        $this->assertEquals($totalPrice,    $this->product->getTotalPrice());
        $this->assertCount(count($options), $this->product->getOptions());
        $this->assertEquals($options,       $this->product->getOptions()->toArray());
    }

    /** @test */
    public function it_can_create_product_with_optional_attributes()
    {
        // Create
        $productData = $this->getRandomProductData();
        $productData['isbn10'] = $this->faker->isbn10;

        // Assert
        $this->makeAndAssertProduct($productData);
    }

    /** @test */
    public function it_can_get_product_attribute()
    {
        $productData = $this->makeAndGetProduct();

        // TODO: Refactor Calculations
        $vat        = 0;
        $hashedId   = $this->hashId($productData['id'], $productData['options']);
        $total      = $productData['qty'] * $productData['price'];
        $vatPrice   = $total * ($vat / 100);
        $options    = $productData['options'];

        $this->assertEquals($productData['id'],     $this->product->id);
        $this->assertEquals($hashedId,              $this->product->hashedId);
        $this->assertEquals($productData['name'],   $this->product->name);
        $this->assertEquals($productData['qty'],    $this->product->qty);
        $this->assertEquals($productData['price'],  $this->product->price);
        $this->assertEquals($vat,                   $this->product->vat);
        $this->assertEquals($total,                 $this->product->total);
        $this->assertEquals($vatPrice,              $this->product->vatPrice);
        $this->assertEquals($total + $vatPrice,     $this->product->totalPrice);

        $this->assertInstanceOf(
            'Arcanedev\\Cartify\\Entities\\ProductOptions',
            $this->product->options
        );
        $this->assertCount(count($options),         $this->product->options);
        $this->assertEquals($options['brand'],      $this->product->brand);
        $this->assertEquals($options['color'],      $this->product->color);
        $this->assertEquals($options['size'],       $this->product->size);
    }

    /** @test */
    public function it_can_set_product_attribute()
    {
        $productData = $this->makeAndGetProduct();

        $this->product->name = $productData['name'] = 'Product name';
        $this->product->qty  = $productData['qty']  = 10;

        $this->assertEquals($productData['name'],   $this->product->name);
        $this->assertEquals($productData['qty'],    $this->product->qty);
        $this->assertEquals($productData['price'],  $this->product->price);
    }

    /** @test */
    public function it_can_update_product()
    {
        $productData = $this->makeAndGetProduct();
        $updatedData = [
            'name'      => 'Awesome Product',
            'qty'       => 10,
            'options'   => array_merge($productData['options'], [
                'size'      => 'small',
                'isbn10'    => $this->faker->isbn13
            ]),
        ];
        $productData = array_merge($productData, $updatedData);

        $this->product->update($updatedData);

        $this->assertEquals($productData['name'], $this->product->name);
        $this->assertEquals($productData['qty'], $this->product->qty);
        $this->assertEquals($productData['options']['size'], $this->product->size);
        $this->assertEquals($productData['options']['isbn10'], $this->product->isbn10);
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\Cartify\Exceptions\InvalidProductException
     * @expectedExceptionMessage The product attributes is empty
     */
    public function it_must_throw_invalid_product_on_empty_attribute()
    {
        new Product();
    }

    /**
     * @test
     *
     * @expectedException        \Arcanedev\Cartify\Exceptions\InvalidProductException
     * @expectedExceptionMessage These attributes are missing: price
     */
    public function it_must_trow_invalid_product_on_missing_attributes()
    {
        $data = $this->getRandomProductData();
        unset($data['price']);

        new Product($data);
    }

    /** @test */
    public function it_must_trow_invalid_product_id_exception_on_id()
    {
        $mistakes = [
            [
                'value'     => null,
                'message'   => 'The product id is empty or equal to 0.'
            ],[
                'value'     => '   ',
                'message'   => 'The product id is empty or equal to 0.'
            ],[
                'value'     => 0,
                'message'   => 'The product id is empty or equal to 0.'
            ]
        ];

        foreach ($mistakes as $mistake) {
            try {
                $this->createModifiedProduct('id', $mistake['value']);
            }
            catch(InvalidProductIDException $e) {
                $this->assertEquals($mistake['message'], $e->getMessage());
            }
        }
    }

    /** @test */
    public function it_must_trow_invalid_product_exception_on_name()
    {
        $mistakes = [
            [
                'value'     => null,
                'message'   => 'The product name is empty.'
            ],[
                'value'     => '   ',
                'message'   => 'The product name is empty.'
            ]
        ];

        foreach ($mistakes as $mistake) {
            try {
                $this->createModifiedProduct('name', $mistake['value']);
            }
            catch(InvalidProductException $e) {
                $this->assertEquals($mistake['message'], $e->getMessage());
            }
        }
    }

    /** @test */
    public function it_must_throw_invalid_quantity_exception()
    {
        $mistakes = [
            [
                'value'     => null,
                'message'   => 'The product quantity must be a numeric value.'
            ],[
                'value'     => 0,
                'message'   => 'The product quantity must be an integer and greater than 0.'
            ]
        ];

        foreach ($mistakes as $mistake) {
            $thrown = false;
            try {
                $this->createModifiedProduct('qty', $mistake['value']);
            }
            catch(InvalidQuantityException $e) {
                $thrown = true;
                $this->assertEquals($mistake['message'], $e->getMessage());
            }
            $this->assertTrue($thrown, 'Fail to throw an InvalidQuantityException.');
        }
    }

    /** @test */
    public function it_must_throw_invalid_price_exception()
    {
        $mistakes = [
            [
                'value'     => null,
                'message'   => 'The product price must be a numeric|double value.'
            ],[
                'value'     => 0,
                'message'   => 'The product price must be greater than 0.'
            ]
        ];

        foreach ($mistakes as $mistake) {
            $thrown = false;
            try {
                $this->createModifiedProduct('price', $mistake['value']);
            }
            catch(InvalidPriceException $e) {
                $thrown = true;
                $this->assertEquals($mistake['message'], $e->getMessage());
            }
            $this->assertTrue($thrown, 'Fail to throw an InvalidPriceException.');
        }
    }

    /** @test */
    public function it_must_throw_invalid_vat_exception()
    {
        $mistakes = [
            [
                'value'     => null,
                'message'   => 'The product VAT must be a numeric|double value.'
            ],[
                'value'     => - 1,
                'message'   => 'The product VAT must be greater than or equal to 0.'
            ],[
                'value'     => - 0.000001,
                'message'   => 'The product VAT must be greater than or equal to 0.'
            ]
        ];

        foreach ($mistakes as $mistake) {
            $thrown = false;
            try {
                $this->createModifiedProduct('vat', $mistake['value']);
            }
            catch(InvalidVatException $e) {
                $thrown = true;
                $this->assertEquals($mistake['message'], $e->getMessage());
            }
            $this->assertTrue($thrown, 'Fail to throw an InvalidVatException.');
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Make the product options collection and get the raw options for tests
     *
     * @return array
     */
    private function makeAndGetProduct($withVat = false)
    {
        $data = $this->getRandomProductData();

        $this->makeAndAssertProduct($data, $withVat);

        $this->calculateData($data);

        return $data;
    }

    /**
     * Make and assert a product
     *
     * @param array $data
     */
    private function makeAndAssertProduct(array $data, $withVat = false)
    {
        // Create
        if ( ! isset($data['options'])) {
            $data['options'] = [];
        }

        list($id, $name, $qty, $price, $options) = array_values($data);

        if ($withVat) {
            $data['vat'] = $vat = $this->getRandomVAT();
        } else {
            $vat = 0;
        }

        $this->product   = new Product($data);
        $data['options'] = $options = array_merge(
            array_diff_key($data, array_merge(
                array_flip(['id', 'name', 'qty', 'price']),
                array_flip(['vat', 'options'])
            )),
            $options
        );
        $hashedId       = $this->hashId($id, $options);
        $total          = $qty * $price;
        $vatPrice       = $total * ($vat / 100);
        $totalPrice     = $total + $vatPrice;

        // Assert
        $this->assertInstanceOf(self::PRODUCT_CLASS, $this->product);
        $this->assertEquals($hashedId,              $this->product->getHashedId());
        $this->assertEquals($id,                    $this->product->getId());
        $this->assertEquals($name,                  $this->product->getName());
        $this->assertEquals($qty,                   $this->product->getQty());
        $this->assertEquals($price,                 $this->product->getPrice());
        $this->assertEquals($total,                 $this->product->getTotal());
        $this->assertEquals($vat,                   $this->product->getVat());
        $this->assertEquals($vatPrice,              $this->product->getVatPrice());
        $this->assertEquals($totalPrice,            $this->product->getTotalPrice());
        $this->assertCount(count($data['options']), $this->product->getOptions());
        $this->assertEquals($data['options'],       $this->product->getOptions()->toArray());
    }

    /**
     * Create a modified product
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return Product
     */
    private function createModifiedProduct($name, $value)
    {
        $data = array_merge($this->getRandomProductData(), [
            $name => $value
        ]);

        return new Product($data);
    }

    /**
     * Calculate data
     *
     * @param array $data
     */
    private function calculateData(array &$data)
    {
        if ( ! isset($data['vat'])) {
            $data['vat'] = 0;
        }

        $data['hashed-id'] = $this->hashId($data['id'], $data['options']);
        $data['total']     = $data['qty'] * $data['price'];
        $data['vatPrice']  = $data['total'] * ($data['vat'] / 100);
    }

    /**
     * Get hashed Id
     *
     * @param  string $id
     * @param  array  $options
     *
     * @return string
     */
    private function hashId($id, $options = [])
    {
        ksort($options);

        return md5($id . serialize($options));
    }
}
