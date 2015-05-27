<?php namespace Arcanedev\Cartify\Entities;

use Arcanedev\Cartify\Contracts\ProductInterface;
use Arcanedev\Cartify\Contracts\ProductOptionsInterface;
use Arcanedev\Cartify\Exceptions\InvalidPriceException;
use Arcanedev\Cartify\Exceptions\InvalidProductException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;
use Arcanedev\Cartify\Exceptions\InvalidQuantityException;
use Arcanedev\Cartify\Exceptions\InvalidVatException;
use Arcanedev\Cartify\Traits\CheckerTrait;

/**
 * Class Product
 * @package Arcanedev\Cartify\Entities
 *
 * @property string         id
 * @property string         name
 * @property int            qty
 * @property double          price
 * @property int|double      vat
 * @property double          total
 * @property double          vatPrice
 * @property double          totalPrice
 * @property ProductOptions options
 */
class Product implements ProductInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Traits
     | ------------------------------------------------------------------------------------------------
     */
    use CheckerTrait;

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
     * @var double
     */
    protected $propPrice;

    /**
     * Value-added tax
     *
     * @var double
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
        if (empty($attributes)) {
            throw new InvalidProductException('The product attributes is empty');
        }

        $this->load($attributes);
    }

    /**
     * Load product attributes
     *
     * @param  array $attributes
     *
     * @throws InvalidProductException
     *
     * @return Product
     */
    private function load(array $attributes)
    {
        $this->checkRequiredAttributes($attributes);
        $this->fillOptionalAttributes($attributes, [
            'vat'     => 0,
            'options' => []
        ]);
        $this->setAttributes($attributes);
        $this->generateHashedID();

        return $this;
    }

    /**
     * Set product attributes
     * @todo: Merge the rest of the attribute to options
     *
     * @param  array $attributes
     */
    private function setAttributes(array $attributes)
    {
        $this->setId($attributes['id']);
        $this->setName($attributes['name']);
        $this->setQty($attributes['qty']);
        $this->setPrice($attributes['price']);
        $this->setVat($attributes['vat']);
        $this->setOptions($attributes['options']);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters and Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Magic get method
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($method = $this->hasPropertyOrMethod($name)) {
            return $this->{$method}();
        }

        return $this->options->get($name);
    }

    /**
     * Magic set method
     *
     * @param string $name
     * @param mixed  $value
     */
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
     * @return double
     */
    public function getPrice()
    {
        return doubleval($this->propPrice);
    }

    /**
     * Set product price
     *
     * @param  double $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->checkPrice($price);

        $this->propPrice = doubleval($price);

        return $this;
    }

    /**
     * Get product Value-added tax
     *
     * @return double
     */
    public function getVat()
    {
        return $this->propVat;
    }

    /**
     * Set product Value-added tax
     *
     * @param  double|int $vat
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
     * Get total without VAT
     *
     * @return double
     */
    public function getTotal()
    {
        return doubleval($this->qty * $this->propPrice);
    }

    /**
     * Get Vat price
     *
     * @return double
     */
    public function getVatPrice()
    {
        return doubleval($this->getTotal() * ($this->getVat() / 100));
    }

    /**
     * Get total price
     *
     * @return double
     */
    public function getTotalPrice()
    {
        return doubleval($this->getTotal() + $this->getVatPrice());
    }

    /**
     * Get product options
     *
     * @return ProductOptionsInterface
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
     * @param  string     $id
     * @param  string     $name
     * @param  int        $qty
     * @param  int|double $price
     * @param  int|double $vat
     * @param  array      $options
     *
     * @return Product
     */
    public static function create($id, $name, $qty, $price, $vat = 0.0, array $options = [])
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
     * @param  string $id
     *
     * @throws InvalidProductIDException
     */
    private function checkId($id)
    {
        if (
            $this->checkIsNullOrEmpty($id) ||
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
            $this->checkIsNullOrEmpty($name) ||
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
     * @param  double $price
     *
     * @throws InvalidPriceException
     */
    private function checkPrice(&$price)
    {
        if ( ! $this->checkIsDoubleNumber($price)) {
            throw new InvalidPriceException(
                'The product price must be a numeric|double value.'
            );
        }

        if (doubleval($price) <= 0) {
            throw new InvalidPriceException(
                'The product price must be greater than 0.'
            );
        }

        $price = doubleval($price);
    }

    /**
     * Check the VAT
     *
     * @param  double|int $vat
     *
     * @throws InvalidVatException
     */
    private function checkVat(&$vat)
    {
        if ( ! $this->checkIsDoubleNumber($vat)) {
            throw new InvalidVatException(
                'The product VAT must be a numeric|double value.'
            );
        }

        if (doubleval($vat) < 0) {
            throw new InvalidVatException(
                'The product VAT must be greater than or equal to 0.'
            );
        }

        $vat = doubleval($vat);
    }

    /**
     * Check if product has a method to get or set a property
     *
     * @param  string $name
     * @param  string $methodPrefix
     *
     * @return null|string
     */
    private function hasPropertyOrMethod($name, $methodPrefix = 'get')
    {
        $name     = ucfirst(strtolower($name));
        $method   = $methodPrefix . $name;

        return (
            property_exists($this, 'prop'  . $name) ||
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

    /**
     * Fill optional attributes
     *
     * @param array $attributes
     */
    private function fillOptionalAttributes(array &$attributes, $defaults = [])
    {
        foreach ($defaults as $key => $value) {
            if ( ! array_key_exists($key, $attributes)) {
                $attributes[$key] = $value;
            }
        }
    }
}
