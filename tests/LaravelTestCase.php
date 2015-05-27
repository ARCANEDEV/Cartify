<?php namespace Arcanedev\NoCaptcha\Tests;

use Arcanedev\Cartify\Tests\TestCase;

abstract class LaravelTestCase extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register Service Providers
     *
     * @return array
     */
    protected function getPackageProviders()
    {
        return [
            'Arcanedev\\Cartify\\Laravel\\ServiceProvider',
        ];
    }

    /**
     * Get package aliases.
     *
     * @return array
     */
    protected function getPackageAliases()
    {
        return [
            'Cartify' => 'Arcanedev\\Cartify\\Laravel\\Facade',
        ];
    }
}
