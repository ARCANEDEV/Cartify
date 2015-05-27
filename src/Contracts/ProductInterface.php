<?php namespace Arcanedev\Cartify\Contracts;

/**
 * Interface ProductInterface
 * @package Arcanedev\Cartify\Contracts
 *
 * @property string                  id
 * @property string                  name
 * @property int                     qty
 * @property double                  price
 * @property int|double              vat
 * @property double                  total
 * @property double                  vatPrice
 * @property double                  totalPrice
 * @property ProductOptionsInterface options
 */
interface ProductInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get product id
     *
     * @return string
     */
    public function getId();

    /**
     * Set product id
     *
     * @param  string $id
     *
     * @return self
     */
    public function setId($id);

    /**
     * Get product name
     *
     * @return string
     */
    public function getName();

    /**
     * Set product name
     *
     * @param  string $name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get product quantity
     *
     * @return int
     */
    public function getQty();

    /**
     * Set product quantity
     *
     * @param  int $qty
     *
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get product price
     *
     * @return double
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param  double $price
     *
     * @return self
     */
    public function setPrice($price);

    /**
     * Get product Value-added tax
     *
     * @return int
     */
    public function getVat();

    /**
     * Set product Value-added tax
     *
     * @param  double|int $vat
     *
     * @return self
     */
    public function setVat($vat);

    /**
     * Get
     * @return double
     */
    public function getTotal();

    /**
     * Get Vat price
     *
     * @return double
     */
    public function getVatPrice();

    /**
     * Get total price
     *
     * @return double
     */
    public function getTotalPrice();

    /**
     * Get product options
     *
     * @return ProductOptionsInterface
     */
    public function getOptions();

    /**
     * Set product options
     *
     * @param  array $options
     *
     * @return self
     */
    public function setOptions(array $options);

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
     * @return ProductInterface
     */
    public static function create($id, $name, $qty, $price, $vat = 0, array $options = []);
}
