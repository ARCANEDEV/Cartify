<?php namespace Arcanedev\Cartify\Tests\Entities;

use Arcanedev\Cartify\Entities\ProductOptions;
use Arcanedev\Cartify\Tests\TestCase;

class ProductOptionsTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    const OPTIONS_CLASS = 'Arcanedev\\Cartify\\Entities\\ProductOptions';

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var ProductOptions */
    protected $options;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->options = new ProductOptions;
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->options);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        // Create
        $this->makeAndAssertOptions([]);

        // Assert
        $this->assertEmpty($this->options->toArray());
    }

    /** @test */
    public function it_can_create_product_options()
    {
        // Create
        $options       = $this->makeAndGetOptions();

        // Then
        $this->options = ProductOptions::make($options);

        // Assert
        $this->assertCount(count($options), $this->options);
        $this->assertEquals($options, $this->options->toArray());
    }

    /** @test */
    public function it_can_get_an_option()
    {
        // Create
        $options    = $this->makeAndGetOptions();

        // Assert
        $this->assertEachOption($options);
    }

    /** @test */
    public function it_can_add_an_option()
    {
        // Create
        $options            = $this->makeAndGetOptions();
        $rating             = 5;
        $isbn               = $this->faker->ean13;

        // Then
        $options['rating']  = $this->options['rating'] = $rating;
        $options['isbn']    = $this->options->isbn     = $isbn;

        // Assert
        $this->assertEachOption($options);
    }

    /** @test */
    public function it_can_update_options_one_by_one()
    {
        // Create
        $options            = $this->makeAndGetOptions();
        $color              = 'transparent';
        $size               = 'titan';

        // Then
        $options['color']   = $this->options['color'] = $color;
        $options['size']    = $this->options->size    = $size;

        // Assert
        $this->assertEachOption($options);
    }

    /** @test */
    public function it_can_update_options()
    {
        // Create
        $options = $this->makeAndGetOptions();
        $updated = [
            'color' => 'transparent',
            'size'  => 'titan',
        ];

        // Then
        $this->options->update($updated);

        // Assert
        $this->assertEachOption(array_merge($options, $updated));
    }

    /** @test */
    public function it_can_forget_options_one_by_one()
    {
        // Create
        $options = $this->makeAndGetOptions();

        foreach (array_keys($options) as $key) {
            // Then
            unset($options[$key]);
            $this->options->forget($key);

            // Assert
            $this->assertEachOption($options);
        }

        // Assert
        $this->assertCount(0, $this->options);
        $this->assertEquals([], $this->options->toArray());
    }

    /** @test */
    public function it_can_delete_options_one_by_one()
    {
        // Create
        $options = $this->makeAndGetOptions();

        foreach (array_keys($options) as $key) {
            // Then
            unset($options[$key]);
            $this->options->delete($key);

            // Assert
            $this->assertEachOption($options);
        }

        // Assert
        $this->assertCount(0,   $this->options);
        $this->assertEquals([], $this->options->toArray());
    }

    /** @test */
    public function it_can_delete_options()
    {
        // Create
        $options = $this->makeAndGetOptions();

        // Then
        $this->options->delete('size', 'color');

        // Assert
        $this->assertEachOption(['brand' => $options['brand']]);

        // Then
        $this->options->delete('brand');

        // Assert
        $this->assertCount(0,   $this->options);
        $this->assertEquals([], $this->options->toArray());
    }

    /** @test */
    public function it_can_delete_all_options()
    {
        // Create
        $this->makeAndGetOptions();

        // Then
        $this->options->delete();

        // Assert
        $this->assertCount(0,   $this->options);
        $this->assertEquals([], $this->options->toArray());
    }

    /** @test */
    public function it_can_clear_all_options()
    {
        // Create
        $this->makeAndGetOptions();

        // Then
        $this->options->clear();

        // Assert
        $this->assertCount(0,   $this->options);
        $this->assertEquals([], $this->options->toArray());
    }

    /** @test */
    public function it_must_return_null_value_on_not_founded_attribute()
    {
        // Create
        $this->makeAndGetOptions();

        // Assert
        $this->assertFalse($this->options->has('isbn'));
        $this->assertNull($this->options->isbn);
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
    private function makeAndGetOptions()
    {
        $this->makeAndAssertOptions($options = $this->getRandomOptions());

        return $options;
    }

    /**
     * Make and Assert the product options
     *
     * @param array $options
     */
    private function makeAndAssertOptions(array $options)
    {
        // Create
        $this->options = new ProductOptions($options);

        // Assert
        $this->assertInstanceOf(self::OPTIONS_CLASS, $this->options);
        $this->assertCount(count($options), $this->options);
        $this->assertEquals($options,       $this->options->toArray());
    }

    /**
     * Assert each option attribute
     *
     * @param array $options
     */
    private function assertEachOption(array $options)
    {
        foreach ($options as $key => $value) {
            $this->assertTrue(
                $this->options->has($key),
                'The [' . $key .'] option does not exist.'
            );

            $this->assertEquals($value, $this->options[$key]);
            $this->assertEquals($value, $this->options->get($key));
            $this->assertEquals($value, $this->options->{$key});
        }
    }
}
