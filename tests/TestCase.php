<?php namespace Arcanedev\Cartify\Tests;

use Arcanedev\Cartify\Entities\Product;
use PHPUnit_Framework_TestCase;
use Faker\Factory;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    protected function makeRandomProduct()
    {
        return new Product($this->getRandomProductData());
    }

    /**
     * Get a random product data for tests
     *
     * @param  bool $onlyValues
     * @param  bool $withOptions
     *
     * @return array
     */
    protected function getRandomProductData($onlyValues = false, $withOptions = true)
    {
        $data = [
            'id'        => $this->faker->randomNumber(5),
            'name'      => $this->faker->sentence(3),
            'qty'       => $this->faker->numberBetween(1, 25),
            'price'     => $this->faker->randomFloat(null, 1, 2000),
            'vat'       => $this->getRandomVAT(),
            'options'   => $withOptions ? $this->getRandomOptions() : [],
        ];

        return $onlyValues ? array_values($data) : $data;
    }

    /**
     * Get random options data for tests
     *
     * @return array
     */
    protected function getRandomOptions()
    {
        $sizes = [
            'x-small', 'small', 'medium', 'large', 'x-large', 'xx-large'
        ];

        return [
            'brand' => $this->faker->company,
            'color' => $this->faker->colorName,
            'size'  => $sizes[array_rand($sizes, 1)],
        ];
    }

    protected function getRandomVAT()
    {
        $vats = [2,1, 5.5, 10, 14, 20];

        return (float) $vats[array_rand($vats, 1)];
    }
}
