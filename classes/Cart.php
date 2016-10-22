<?php

class Cart {

    private $errors = array();
    private $cartname;
    private $valid_actions = array('add', 'remove', 'move');
    private $inventory;
    private $items_in_cart = array();
    private $allow_out_of_stock_items = false;

    const TAX_RATE = .0635;

    public function __construct($inventory, $cartname = 'cart', $allow_out_of_stock_items = false) {
        $this->cartname = $cartname;
        $this->inventory = $inventory;
        $this->allow_out_of_stock_items = $allow_out_of_stock_items;
        $this->getCurrentCart();
        $this->checkChanges();
        $this->saveCart();
    }

    public function __get($prop) {
        if (property_exists(__CLASS__, $prop)) {
            return $this->$prop;
        } else {
            return null;
        }
    }

    public function allowedOutOfStockItems() {
        return $this->cartname->allow_out_of_stock_items ? true : false;
    }

    // Add items to our local cart
    public function addToCart($id, $quantity) {
        $this->items_in_cart[$id] = $quantity;
    }
   
    // Remove items from our local cart
    public function removeFromCart($id) {
        unset($this->items_in_cart[$id]);
    }

    // Save our current local cart items to the cookie
    private function saveCart() {
        setCookie($this->cartname, json_encode($this->items_in_cart), $_SERVER['REQUEST_TIME'] + 2592000, "/", "localhost");// cookie expires after 280 days: 24*60*60*days
    }

    // Get our current cart items from the cookie and save locally
    private function getCurrentCart() {
        if (isset($_COOKIE[$this->cartname])) {
            $cart = $_COOKIE[$this->cartname];
            $decoded = json_decode($cart);
            if ($decoded) {
                foreach ($decoded as $key => $value) {
                    $this->items_in_cart[$key] = $value;
                }
            }
        }
    }

    // Print out our entire cart
    public function __toString() {
        $str = '';
        if ($this->items_in_cart) {
            foreach ($this->items_in_cart as $key => $value) {
                $product = $this->inventory->getItem($key);
                $str .= '<li>' . $product->name . '</li>';
            }
        }
        return $str;
    }

    // Return an item from the cart
    public function itemInCart($id) {
        if (isset($this->items_in_cart[$id])) {
            return $this->items_in_cart[$id];
        } else {
            return null;
        }
    }

    public function getSubTotal() {
        $subTotal = 0;
        //   setLocale(LC_MONETARY, 'en_US.UTF-8');
        foreach ($this->items_in_cart as $key => $value) {
            $product = $this->inventory->getItem($key);
            $subTotal += $value * $product->finalPrice();
        }
        return $subTotal;
        //return money_format('%.2n',$subTotal);
    }

    public function getSalesTax() {
        setLocale(LC_MONETARY, 'en_US.UTF-8');
        $sales_tax = $this->getSubTotal() * self::TAX_RATE;
        return money_format('%.2n', $sales_tax);
    }

    public function getTotal() {
        setLocale(LC_MONETARY, 'en_US.UTF-8');
        $total = $this->getSubTotal() * (1 + self::TAX_RATE);
        return money_format('%.2n', $total);
    }

    // Check for users actions sent through GET
    private function checkChanges() {
        // Process possible quantity changes
        if (isset($_POST) && $_POST) { // post is set and has items:// Check if this post is for this instance of Cart
            if (isset($_POST['target']) && $_POST['target'] == $this->cartname) {
                $errors = array();                                       //set up variable errors to store errors for display
                foreach ($_POST as $key => $value) {                    // Loop through all products POSTed
                    $id = substr($key, 4);                          // Use substr to remove 'item' text from product id
                    $product = $this->inventory->getItem($id);          // Check if product id exists
                    if ($product) {                                   //product exists: validate products
                        if (!preg_match('/^[0-9]+$/', $value)) {            // Check if quantity passed is an integer
                            $errors[$key] = 'Please enter an integer'; // Set error message
                        } elseif (!$value) {                              // Check if the quantity is 0
                            $this->removeFromCart($id);             // Remove product from items_in_cart
                        } elseif ($product->quantity_in_stock == 0) {   //product is out of stock
                            if ($this->allow_out_of_stock_items == true) {
                                $this->addToCart($id, 0);        //can be added to the cart that accepts out of stocked product
                            } else {
                                $errors[$key] = 'This item is out of stock';
                            }
                        } elseif ($value > $product->quantity_in_stock) {// Check if quantity exceeds number in stock
                            $this->addToCart($id, $product->quantity_in_stock); // Update quant to max in quantity and return message
                            $errors[$key] = 'We\'ve updated the quantity to the maximum we have in stock';
                        } else { // Update items_in_cart with new quant
                            $this->addToCart($id, $value); // Update quant to max in quantity
                        }
                    } else {
                        $this->removeFromCart($id); // Remove bad item from cart
                    }
                }
                $this->errors = $errors; // Store possible errors
            }

            // Process possible add/remove action
        } elseif (isset($_GET['action']) && in_array($_GET['action'], $this->valid_actions) && isset($_GET['id'])) {

            // Store some vars
            $source = isset($_GET['source']) ? $_GET['source'] : null;
            $target = isset($_GET['target']) ? $_GET['target'] : null;

            // Validate vars
            if ($target && ($source || $_GET['action'] != 'move') && $source != $target) { // We always have to have a target, we need a source if this is a move, and the source and target should never be the same
                // Check if this action is for this instance of Cart
                if ($source == $this->cartname || $target == $this->cartname) { // This action is for this cart
                    // Get the product that was sent
                    $product = $this->inventory->getItem($_GET['id']);

                    // Check if the id of this product exists
                    if ($product != null) { // Product exists
                        if ($_GET['action'] == 'add') { // This is an add
                            if(!$this->itemInCart($_GET['id'])) {
                                if($product->quantity_in_stock > 0) {
                                      $this->addToCart($product->id, 1);
                                } elseif ($product->quantity_in_stock == 0) {
                                      $this->addToCart($product->id, 0);
                                }
                            }
//                         
                        } elseif ($_GET['action'] == 'remove') { // This is a remove
                            $this->removeFromCart($_GET['id']); // Perform cart action
                            
                        } elseif ($source == $this->cartname) { // This is a move and the source is this cart
                            $this->removeFromCart($_GET['id']); // Remove product from items_in_cart
                            
                        } else { // This is a move and the target is this cart
                            if ($product->quantity_in_stock > 0) {// Check if item is in stock
                                // Check if the item is already in the cart
                                if (!$this->itemInCart($_GET['id'])) { // Not in cart
                                    $this->addToCart($_GET['id'], 1); // Add product to items_in_cart
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}

?>