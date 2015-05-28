<?php namespace Arcanedev\Cartify;

use Arcanedev\Cartify\Contracts\EventHandler;
use Arcanedev\Cartify\Contracts\SessionHandler;
use Arcanedev\Cartify\Entities\Cart;
use Arcanedev\Cartify\Entities\CartCollection;
use Arcanedev\Cartify\Entities\Product;
use Arcanedev\Cartify\Entities\ProductCollection;
use Arcanedev\Cartify\Entities\ProductOptions;
use Arcanedev\Cartify\Exceptions\CartNotFoundException;
use Arcanedev\Cartify\Exceptions\InvalidPriceException;
use Arcanedev\Cartify\Exceptions\InvalidProductException;
use Arcanedev\Cartify\Exceptions\InvalidProductIDException;
use Arcanedev\Cartify\Exceptions\InvalidQuantityException;

class Cartify
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

    /**
     * Current cart instance
     *
     * @var string
     */
    protected $instance = 'main';

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
     * Add a row to the cart
     *
     * @param string|array  $id       Unique ID of the product|Item formated as array|Array of items
     * @param string        $name
     * @param int           $qty
     * @param float         $price
     * @param array         $options
     */
    public function add($id, $name = null, $qty = null, $price = null, array $options = [])
    {
        // If the first parameter is an array we need to call the add() function again
        if (is_array($id)) {
            // And if it's not only an array, but a multidimensional array, we need to
            // recursively call the add function
            if ($this->isMultiArray($id)) {
                $this->fireEvent('batch', $id);
                foreach($id as $item) {
                    $this->addRow(
                        $item['id'],
                        $item['name'],
                        $item['qty'],
                        $item['price'],
                        array_get($item, 'options', [])
                    );
                }
                $this->fireEvent('batched', $id);

                return;
            }

            $options = array_get($id, 'options', []);

            $this->fireEvent('add', array_merge($id, ['options' => $options]));
            $result = $this->addRow($id['id'], $id['name'], $id['qty'], $id['price'], $options);
            $this->fireEvent('added', array_merge($id, ['options' => $options]));

            return $result;
        }

        $data = compact('id', 'name', 'qty', 'price', 'options');

        $this->fireEvent('add', $data);
        $result = $this->addRow($id, $name, $qty, $price, $options);
        $this->fireEvent('added', $data);

        return $result;
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string        $rowId
     * @param  integer|array $attribute New quantity|Array of attributes
     *
     * @throws InvalidProductIDException
     *
     * @return bool
     */
    public function update($rowId, $attribute)
    {
        if ( ! $this->hasRowId($rowId)) {
            throw new InvalidProductIDException;
        }

        if (is_array($attribute)) {
            $this->fireEvent('update', $rowId);
            $result = $this->updateAttribute($rowId, $attribute);
            $this->fireEvent('updated', $rowId);

            return $result;
        }

        // Fire the cart.update event
        $this->event->fire('cart.update', $rowId);
        $result = $this->updateQty($rowId, $attribute);
        // Fire the cart.updated event
        $this->event->fire('cart.updated', $rowId);

        return $result;
    }

    /**
     * Remove a row from the cart
     *
     * @param  string $rowId The rowid of the item
     *
     * @throws InvalidProductIDException
     *
     * @return bool
     */
    public function remove($rowId)
    {
        if ( ! $this->hasRowId($rowId)) {
            throw new InvalidProductIDException;
        }

        $cart = $this->getContent();

        $this->fireEvent('remove', $rowId);

        $cart->forget($rowId);

        $this->fireEvent('removed', $rowId);

        return $this->updateCart($cart);
    }

    /**
     * Get a row of the cart by its ID
     *
     * @param  string  $rowId  The ID of the row to fetch
     *
     * @return CartCollection
     */
    public function get($rowId)
    {
        $cart = $this->getContent();

        return $cart->has($rowId) ? $cart->get($rowId) : null;
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
     * @return boolean
     */
    public function destroy()
    {
        $this->fireEvent('destroy');

        $result = $this->updateCart(null);

        $this->fireEvent('destroyed');

        return $result;
    }

    /**
     * Get the price total
     *
     * @return float
     */
    public function total()
    {
        $total = 0;
        $cart = $this->getContent();

        if (empty($cart)) {
            return $total;
        }

        foreach($cart as $row) {
            $total += $row->subtotal;
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

        foreach($cart as $row) {
            $count += $row->qty;
        }

        return $count;
    }

    /**
     * Search if the cart has a item
     *
     * @param  array  $search  An array with the item ID and optional options
     *
     * @return array|boolean
     */
    public function search(array $search)
    {
        if (empty($search)) {
            return false;
        }

        $rows = [];

        foreach($this->getContent() as $item) {
            /** @var Cart $item */
            if ($item->search($search)) {
                $rows[] = $item->rowid;
            }
        }

        return empty($rows) ? false : $rows;
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
     * @return
     */
    protected function addRow($id, $name, $qty, $price, array $options = [])
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

        $cart  = $this->getContent();
        $rowId = hash_id($id, $options);

        if ($cart->has($rowId)) {
            $row  = $cart->get($rowId);
            $cart = $this->updateRow($rowId, ['qty' => $row->qty + $qty]);
        }
        else {
            $cart = $this->createRow($rowId, $id, $name, $qty, $price, $options);
        }

        return $this->updateCart($cart);
    }

    /**
     * Check if a rowid exists in the current cart instance
     *
     * @param  string  $id  Unique ID of the item
     *
     * @return boolean
     */
    protected function hasRowId($id)
    {
        return $this->getContent()->has($id);
    }

    /**
     * Update the cart
     *
     * @param  CartCollection  $cart  The new cart content
     */
    protected function updateCart($cart)
    {
        return $this->session->put($this->getInstance(), $cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return CartCollection
     */
    protected function getContent()
    {
        $content = $this->session->has($this->getInstance())
            ? $this->session->get($this->getInstance())
            : new CartCollection;

        return $content;
    }

    /**
     * Update a row if the rowId already exists
     *
     * @param  string $rowId      The ID of the row to update
     * @param  array  $attributes The quantity to add to the row
     *
     * @return CartCollection
     */
    protected function updateRow($rowId, $attributes)
    {
        $cart = $this->getContent();
        $row  = $cart->get($rowId);

        foreach($attributes as $key => $value) {
            if ($key == 'options') {
                $options = $row->options->merge($value);
                $row->put($key, $options);
            }
            else {
                $row->put($key, $value);
            }
        }

        if ( ! is_null(array_keys($attributes, ['qty', 'price']))) {
            $row->put('subtotal', $row->qty * $row->price);
        }

        $cart->put($rowId, $row);

        return $cart;
    }

    /**
     * Create a new row Object
     *
     * @param  string  $rowId    The ID of the new row
     * @param  string  $id       Unique ID of the item
     * @param  string  $name     Name of the item
     * @param  int     $qty      Item qty to add to the cart
     * @param  float   $price    Price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return CartCollection
     */
    protected function createRow($rowId, $id, $name, $qty, $price, $options)
    {
        $cart = $this->getContent();

        $newRow = new Product([
            'rowid'     => $rowId,
            'id'        => $id,
            'name'      => $name,
            'qty'       => $qty,
            'price'     => $price,
            'options'   => new ProductOptions($options),
        ]);

        $cart->put($rowId, $newRow);

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
}
