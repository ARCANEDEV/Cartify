<?php namespace Arcanedev\Cartify;

use Arcanedev\Cartify\Contracts\CartifyInterface;
use Arcanedev\Cartify\Contracts\EventHandler;
use Arcanedev\Cartify\Contracts\SessionHandler;
use Arcanedev\Cartify\Entities\Cart;
use Arcanedev\Cartify\Entities\CartCollection;
use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Entities\ProductCollection;
use Arcanedev\Cartify\Exceptions\CartNotFoundException;
use Arcanedev\Cartify\Exceptions\InvalidPriceException;
use Arcanedev\Cartify\Exceptions\InvalidProductException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;
use Arcanedev\Cartify\Exceptions\InvalidQuantityException;

class Cartify implements CartifyInterface
{
    /* ------------------------------------------------------------------------------------------------
     |  Constants
     | ------------------------------------------------------------------------------------------------
     */
    const BASE_INSTANCE = 'cartify';

    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Current cart instance
     *
     * @var string
     */
    protected $instance = 'main';

    /**
     * Cart collection (main cart + wishlist cart ...)
     *
     * @var CartCollection
     */
    protected $carts;

    /**
     * Session class instance
     *
     * @var SessionHandler
     */
    protected $session;

    /**
     * Event class instance
     *
     * @var EventHandler
     */
    protected $event;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Constructor
     *
     * @param SessionHandler $session
     * @param EventHandler   $event
     */
    public function __construct(SessionHandler $session, EventHandler $event)
    {
        $this->session  = $session;
        $this->event    = $event;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters and Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the current cart instance
     *
     * @return string
     */
    protected function getInstance()
    {
        return self::BASE_INSTANCE . '.' . $this->instance;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the current cart instance
     *
     * @param  string $instance Cart instance name
     *
     * @throws CartNotFoundException
     *
     * @return self
     */
    public function instance($instance = null)
    {
        if (empty($instance) ) {
            throw new CartNotFoundException;
        }

        $this->instance = $instance;

        return $this;
    }

    /**
     * Add a product to the cart
     * @todo: Add optionnal VAT attribute
     *
     * @param  string|array $id Unique ID of the product|Item formated as array|Array of items
     * @param  string       $name
     * @param  int          $qty
     * @param  double       $price
     * @param  array        $options
     *
     * @return self
     */
    public function add($id, $name = null, $qty = null, $price = null, array $options = [])
    {
        // If the first parameter is an array we need to call the add() function again
        if (is_array($id)) {
            // And if it's not only an array, but a multidimensional array, we need to
            // recursively call the add function
            if (is_multi_array($id)) {
                $this->addMany($id);

                return $this;
            }

            $options = array_get($id, 'options', []);

            $this->fireEvent('add', array_merge($id, compact('options')));
            $result = $this->addRow($id['id'], $id['name'], $id['qty'], $id['price'], $options);
            $this->fireEvent('added', array_merge($id, compact('options')));

            return $this;
        }

        $data = compact('id', 'name', 'qty', 'price', 'options');

        $this->fireEvent('add', $data);
        $result = $this->addRow($id, $name, $qty, $price, $options);
        $this->fireEvent('added', $data);

        return $this;
    }

    /**
     * Add many products to the cart
     *
     * @param  array $items
     *
     * @throws InvalidPriceException
     * @throws InvalidProductException
     * @throws InvalidQuantityException
     */
    private function addMany(array $items)
    {
        $this->fireEvent('batch', $items);
        foreach ($items as $item) {
            $this->addRow(
                $item['id'],
                $item['name'],
                $item['qty'],
                $item['price'],
                array_get($item, 'options', [])
            );
        }
        $this->fireEvent('batched', $items);
    }

    /**
     * Add row to the cart
     *
     * @param  string $id      Unique ID of the item
     * @param  string $name    Name of the item
     * @param  int    $qty     Item qty to add to the cart
     * @param  float  $price   Price of one item
     * @param  array  $options Array of additional options, such as 'size' or 'color'
     *
     * @throws InvalidPriceException
     * @throws InvalidProductException
     * @throws InvalidQuantityException
     *
     * @return self
     */
    private function addRow($id, $name, $qty, $price, array $options = [])
    {
        if (empty($id) || empty($name) || empty($qty) || ! isset($price)) {
            throw new InvalidProductException;
        }

        if ( ! is_numeric($qty)) {
            throw new InvalidQuantityException;
        }

        if ( ! is_numeric($price)) {
            throw new InvalidPriceException;
        }

        $cart     = $this->getContent();
        $hashedId = hash_id($id, $options);

        if ($cart->hasProduct($hashedId)) {
            $product = $cart->get($hashedId);
            $cart    = $this->updateRow($hashedId, ['qty' => $product->qty + $qty]);
        }
        else {
            $cart = $this->createRow($hashedId, $id, $name, $qty, $price, $options);
        }

        $this->updateCart($cart);

        return $this;
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string        $hashedId
     * @param  integer|array $attribute New quantity|Array of attributes
     *
     * @throws InvalidProductIDException
     *
     * @return bool
     */
    public function update($hashedId, $attribute)
    {
        $this->hasProductOrFail($hashedId);

        $this->fireEvent('update', $hashedId);

        $result = is_array($attribute)
            ? $this->updateAttribute($hashedId, $attribute)
            : $this->updateQty($hashedId, $attribute);

        $this->fireEvent('updated', $hashedId);

        return $result;
    }

    /**
     * Remove a row from the cart
     *
     * @param  string $hashedId The hashed Id of the product
     *
     * @throws InvalidProductIDException
     *
     * @return self
     */
    public function remove($hashedId)
    {
        $this->hasProductOrFail($hashedId);

        $cart = $this->getContent();

        $this->fireEvent('remove', $hashedId);
        $cart->removeProduct($hashedId);
        $this->fireEvent('removed', $hashedId);

        $this->updateCart($cart);

        return $this;
    }

    /**
     * Get a row of the cart by its ID
     *
     * @param  string  $hashedId  The ID of the product to fetch
     *
     * @return Product
     */
    public function get($hashedId)
    {
        $cart = $this->getContent();

        return $cart->hasProduct($hashedId)
            ? $cart->get($hashedId)
            : null;
    }

    /**
     * Get the cart content
     *
     * @return ProductCollection|null
     */
    public function content()
    {
        $cart = $this->getContent();

        return empty($cart) ? null : $cart;
    }

    /**
     * Empty the cart
     *
     * @return self
     */
    public function destroy()
    {
        $this->fireEvent('destroy');
        $this->updateCart(new Cart);
        $this->fireEvent('destroyed');

        return $this;
    }

    /**
     * Get the price total
     *
     * @return float
     */
    public function total()
    {
        $products = $this->getContent()->all();

        if ($products->isEmpty()) {
            return 0;
        }

        $total = 0;

        // TODO: Replace by sum method
        foreach($products as $product) {
            /** @var Product $product */
            $total += $product->subtotal;
        }

        return $total;
    }

    /**
     * Get the number of items in the cart
     *
     * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
     *
     * @return int
     */
    public function count($totalItems = true)
    {
        $cart = $this->getContent();

        if ( ! $totalItems) {
            return $cart->count();
        }

        $count = 0;

        // TODO: replace by sum method
        foreach($cart->all() as $product) {
            /** @var Product $product */
            $count += $product->qty;
        }

        return $count;
    }

    /**
     * Search if the cart has a item
     *
     * @param  array         $attributes  An array with the item ID and optional options
     *
     * @return array|boolean
     */
    public function search(array $attributes)
    {
        if (empty($search)) {
            return false;
        }

        $rows = [];

        foreach($this->getContent()->all() as $product) {
            /** @var Product $product */
            if ($product->search($attributes)) {
                $rows[] = $product->id;
            }
        }

        return empty($rows) ? false : $rows;
    }

    /**
     * Check if a hashed id exists in the current cart instance
     *
     * @param  string  $hashedId  Unique ID of the item
     *
     * @return boolean
     */
    protected function hasProductById($hashedId)
    {
        return $this->getContent()->hasProduct($hashedId);
    }

    /**
     * Update the cart
     *
     * @param  Cart $cart  The new cart content
     */
    protected function updateCart($cart)
    {
        $this->updateSessionCart($cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return Cart
     */
    protected function getContent()
    {
        $content = $this->hasSessionCart()
            ? $this->getSessionCart()
            : new Cart;

        return $content;
    }

    /**
     * Update a row if the rowId already exists
     *
     * @param  string $hashedId   The ID of the row to update
     * @param  array  $attributes The quantity to add to the row
     *
     * @return Cart
     */
    protected function updateRow($hashedId, $attributes)
    {
        $cart    = $this->getContent();
        $product = $cart->get($hashedId);

        $cart->update($hashedId, $product->update($attributes));

        return $cart;
    }

    /**
     * Create a new row Object
     *
     * @param  string  $hashedId    The ID of the new row
     * @param  string  $id       Unique ID of the item
     * @param  string  $name     Name of the item
     * @param  int     $qty      Item qty to add to the cart
     * @param  float   $price    Price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return CartCollection
     */
    protected function createRow($hashedId, $id, $name, $qty, $price, array $options)
    {
        $cart = $this->getContent();

        $cart->addProduct(compact('hashedId', 'id', 'name', 'qty', 'price', 'options'));

        return $cart;
    }
    /**
     * Update the quantity of a row
     *
     * @param  string  $rowId  The ID of the row
     * @param  int     $qty    The qty to add
     *
     * @return CartCollection
     */
    protected function updateQty($rowId, $qty)
    {
        if ($qty <= 0) {
            return $this->remove($rowId);
        }

        return $this->updateRow($rowId, ['qty' => $qty]);
    }

    /**
     * Update an attribute of the row
     *
     * @param  string  $rowId       The ID of the row
     * @param  array   $attributes  An array of attributes to update
     *
     * @return CartCollection
     */
    protected function updateAttribute($rowId, $attributes)
    {
        return $this->updateRow($rowId, $attributes);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param $id
     *
     * @throws InvalidProductIDException
     */
    private function hasProductOrFail($id)
    {
        if ( ! $this->hasProductById($id)) {
            throw new InvalidProductIDException;
        }
    }

    /**
     * Check if the array is a multidimensional array
     *
     * @param  array   $array  The array to check
     *
     * @return boolean
     */
    protected function isMultiArray(array $array)
    {
        return is_array(head($array));
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param string $name
     * @param null   $id
     */
    private function fireEvent($name, $id = null)
    {
        $this->event->fire('cart.' . $name, $id);
    }

    /**
     * Update the session
     *
     * @param Cart $cart
     */
    private function updateSessionCart(Cart $cart)
    {
        $this->session->put($this->getInstance(), $cart);
    }

    /**
     * Get from the session
     *
     * @return Cart
     */
    private function getSessionCart()
    {
        return $this->session->get($this->getInstance());
    }

    /**
     * @return bool
     */
    private function hasSessionCart()
    {
        return $this->session->has($this->getInstance());
    }
}
