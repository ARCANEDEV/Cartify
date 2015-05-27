<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\ProductInterface;
use Arcanedev\Cartify\Exceptions\InvalidPriceException;
use Arcanedev\Cartify\Exceptions\InvalidProductException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;
use Arcanedev\Cartify\Exceptions\InvalidQuantityException;
use Arcanedev\Cartify\Exceptions\InvalidVatException;

/**
 * Class Product
 * @package Arcanedev\Cartify\Entities
 *
 * @property string         id
 * @property string         name
 * @property int            qty
 * @property float          price
 * @property int|float      vat
 * @property float          total
 * @property float          vatPrice
 * @property float          totalPrice
 * @property ProductOptions options
 */
class Product implements ProductInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Id
     *
     * @var string
     */
    protected $propId;

    /**
     * Name
     *
     * @var string
     */
    protected $propName;

    /**
     * Quantity
     *
     * @var int
     */
    protected $propQty;

    /**
     * Price in cents
     *
     * @var float
     */
    protected $propPrice;

    /**
     * Value-added tax
     *
     * @var float
     */
    protected $propVat;

    /**
     * Product options (metadata)
     *
     * @var ProductOptions
     */
    protected $propOptions;

    /**
     * Required attributes
     *
     * @var array
     */
    private $required = ['id', 'name', 'qty', 'price'];

    /**
     * Optional attributes
     *
     * @var array
     */
    private $optional = ['vat', 'options'];

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Constructor
     *
     * @param  array $attributes
     *
     * @throws InvalidProductException
     */
    public function __construct(array $attributes = [])
    {
        $this->load($attributes);
    }

    /**
     * Load product attributes
     *
     * @param array $attributes
     *
     * @throws InvalidProductException
     *
     * @return Product
     */
    private function load(array $attributes)
    {
        if (empty($attributes)) {
            throw new InvalidProductException(
                'The product attributes is empty'
            );
        }

        $this->checkRequiredAttributes($attributes);
        $this->setId($attributes['id']);
        $this->setName($attributes['name']);
        $this->setQty($attributes['qty']);
        $this->setPrice($attributes['price']);
        $this->setVat(array_key_exists('vat', $attributes) ? $attributes['vat'] : 0);

        // TODO: Merge the rest of the attribute to options
        $this->setOptions(array_key_exists('options', $attributes) ? $attributes['options'] : []);

        $this->generateHashedID();

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters and Setters
     | ------------------------------------------------------------------------------------------------
     */
    public function __get($name)
    {
        if ($method = $this->hasPropertyOrMethod($name)) {
            return $this->{$method}();
        }

        return $this->options->get($name);
    }

    public function __set($name, $value)
    {
        if ($method = $this->hasPropertyOrMethod($name, 'set')) {
            $this->{$method}($value);
        }

        $this->options->put($name, $value);
    }

    /**
     * Get product id
     *
     * @return string
     */
    public function getId()
    {
        return $this->propId;
    }

    /**
     * Set product id
     *
     * @param  string $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->checkId($id);

        $this->propId = $id;

        return $this;
    }

    /**
     * Get product name
     *
     * @return string
     */
    public function getName()
    {
        return $this->propName;
    }

    /**
     * Set product name
     *
     * @param  string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->checkName($name);

        $this->propName = $name;

        return $this;
    }

    /**
     * Get product quantity
     *
     * @return int
     */
    public function getQty()
    {
        return intval($this->propQty);
    }

    /**
     * Set product quantity
     *
     * @param  int $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {
        $this->checkQuantity($qty);

        $this->propQty = intval($qty);

        return $this;
    }

    /**
     * Get product price
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->propPrice);
    }

    /**
     * Set product price
     *
     * @param  float $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->checkPrice($price);

        $this->propPrice = floatval($price);

        return $this;
    }

    /**
     * Get product Value-added tax
     *
     * @return int
     */
    public function getVat()
    {
        return $this->propVat;
    }

    /**
     * Set product Value-added tax
     *
     * @param  int $vat
     *
     * @return self
     */
    public function setVat($vat)
    {
        $this->checkVat($vat);

        $this->propVat = $vat;

        return $this;
    }

    /**
     * Get
     * @return float
     */
    public function getTotal()
    {
        return floatval($this->qty * $this->propPrice);
    }

    /**
     * Get Vat price
     *
     * @return float
     */
    public function getVatPrice()
    {
        return floatval($this->getTotal() * ($this->getVat() / 100));
    }

    /**
     * Get total price
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return floatval($this->getTotal() + $this->getVatPrice());
    }

    /**
     * Get product options
     *
     * @return ProductOptions
     */
    public function getOptions()
    {
        return $this->propOptions;
    }

    /**
     * Set product options
     *
     * @param  array $options
     *
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->propOptions = new ProductOptions($options);

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new product
     *
     * @param  string    $id
     * @param  string    $name
     * @param  int       $qty
     * @param  int|float $price
     * @param  int|float $vat
     * @param  array     $options
     *
     * @return Product
     */
    public static function create($id, $name, $qty, $price, $vat = 0, array $options = [])
    {
        return new self(compact('id', 'name', 'qty', 'price', 'vat', 'options'));
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    private function checkRequiredAttributes(array $attributes)
    {
        $found = array_intersect($this->required, array_keys($attributes));

        if (count($found) !== count($this->required)) {
            $missing = implode(', ', array_diff($this->required, $found));

            throw new InvalidProductException(
                "These attributes are missing: $missing."
            );
        }
    }

    /**
     * Check the id attribute
     *
     * @param  int|string $id
     *
     * @throws InvalidProductIDException
     */
    private function checkId($id)
    {
        if (
            $this->checkIsNullOrEmpty($id) or
            $this->checkIsEmptyString($id)
        ) {
            throw new InvalidProductIDException(
                'The product id is empty or equal to 0.'
            );
        }
    }

    /**
     * Check name attribute
     *
     * @param  string $name
     *
     * @throws InvalidProductException
     */
    private function checkName($name)
    {
        if (
            $this->checkIsNullOrEmpty($name) or
            $this->checkIsEmptyString($name)
        ) {
            throw new InvalidProductException('The product name is empty.');
        }
    }

    /**
     * Check the quantity
     *
     * @param  int $qty
     *
     * @throws InvalidQuantityException
     */
    private function checkQuantity(&$qty)
    {
        if ( ! $this->checkIsIntegerNumber($qty)) {
            throw new InvalidQuantityException(
                'The product quantity must be a numeric value.'
            );
        }

        if (intval($qty) <= 0) {
            throw new InvalidQuantityException(
                'The product quantity must be an integer and greater than 0.'
            );
        }

        $qty = intval($qty);
    }

    /**
     * Check the price
     *
     * @param  float $price
     *
     * @throws InvalidPriceException
     */
    private function checkPrice(&$price)
    {
        if ( ! $this->checkIsFloatNumber($price)) {
            throw new InvalidPriceException(
                'The product price must be a numeric|float value.'
            );
        }

        if (floatval($price) <= 0) {
            throw new InvalidPriceException(
                'The product price must be greater than 0.'
            );
        }

        $price = floatval($price);
    }

    /**
     * Check the VAT
     *
     * @param  int $vat
     *
     * @throws InvalidVatException
     */
    private function checkVat(&$vat)
    {
        if ( ! $this->checkIsFloatNumber($vat)) {
            throw new InvalidVatException(
                'The product VAT must be a numeric|float value.'
            );
        }

        if (floatval($vat) < 0) {
            throw new InvalidVatException(
                'The product VAT must be greater than or equal to 0.'
            );
        }

        $vat = floatval($vat);
    }

    /**
     * Check the value is not empty
     *
     * @param  mixed $value
     *
     * @return bool
     */
    private function checkIsNullOrEmpty($value)
    {
        return is_null($value) or empty($value);
    }

    /**
     * Check is a string value
     *
     * @param  string $value
     *
     * @return bool
     */
    private function checkIsEmptyString($value)
    {
        return is_string($value) and trim($value) === '';
    }

    /**
     * Check is a float value
     *
     * @param  float $value
     *
     * @return bool
     */
    private function checkIsFloatNumber($value)
    {
        return is_numeric($value) or is_float($value);
    }

    /**
     * Check is an integer value
     *
     * @param  float $value
     *
     * @return bool
     */
    private function checkIsIntegerNumber($value)
    {
        return is_numeric($value) or is_int($value);
    }

    /**
     * Check if product has a method to get or set a property
     *
     * @param  string $name
     * @param  string $prefix
     *
     * @return null|string
     */
    private function hasPropertyOrMethod($name, $prefix = 'get')
    {
        $name     = ucfirst(strtolower($name));
        $method   = $prefix . $name;

        return (
            property_exists($this, 'prop'  . $name) or
            method_exists($this, $method)
        ) ? $method : null;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Generate product id
     */
    private function generateHashedID()
    {
        $options = $this->propOptions->toArray();
        ksort($options);

        $this->propId = md5($this->propId . serialize($options));
    }
}
