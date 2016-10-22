<?php
// Turn on error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required classes
require_once('classes/Inventory.php');
require_once('classes/Cart.php');
require_once('classes/Product.php');

// Make inventory
$inventory = new Inventory();
$cart = new Cart($inventory, 'cart');
$wishlist = new Cart($inventory, 'wishlist');
?>

<html>
    <head>
        <title>My Store</title>
        <link href="css/index.css" rel="stylesheet">
        <link href="css/checkout.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            <div class="wrapper">
            <h1>My Store</h1>
            <div class="borderline"></div>
            <p  class="lead pinkybackground" id="continue"><a href="index.php" title="Continue Shopping">Continue Shopping >></a></p>
            <h2>Shopping Cart</h2>
            <form action="checkout.php" method="POST" id="shoppingCartForm">
                <input type="hidden" name="target" value="cart" />
                <ul id="cart_items">
                    <?php
                    if ($cart->items_in_cart) {
                       
                        $errors = $cart->errors;
                        foreach ($cart->items_in_cart as $key => $value) {
                            $product = $inventory->getItem($key);
                            echo '<li><span class="title">' . $product->name.'</span>'.
                                    '<a href="checkout.php?action=remove&target=cart&id=' . $product->id . '" title="Remove">Remove</a>' .
                                    '<a href="checkout.php?action=move&source=cart&target=wishlist&id=' . $product->id . '" title="Move to Wishlist">Move to Wishlist</a>' .
                                    '<input name="item' . $key . '" type="text" value="' . $value . '" maxlength="2" size="3" />' .
                                    '<span class="itemPrice"> $' . number_format($value*$product->finalPrice(), 2) . 
                                    '</span>@ $' . number_format($product->finalPrice(), 2) . 
                                  '</li>';
                            if (isset($errors['item' . $key])) {
                                echo '<p>' . $errors['item'.$key] . '</p>';
                            }
                        }
                        echo '<button type="submit" value="save">Save</button>';
                    } 
                    ?>
                </ul>
            
            <div class="borderline"></div>
            <div id="cart_totals" class="clearer">
                <?php
                if ($cart->items_in_cart) {
                    echo '<p class="clearer">Subtotal: <span class="price">'. number_format($cart->getSubTotal(), 2) . '</span></p>' .
                    '<p class="clearer">Sales Tax @8%: <span class="price">' . $cart->getSalesTax() . '</span></p>'.
                    '<p class="clearer">Total: <span class="price">' . $cart->getTotal() .
                    '</span></p>';
                    echo '<button type="submit" value="checkout" id="checkout">Checkout Now</button>';
                }
                ?> 
            </div>
            </form>
            <h2>My Wishlist</h2>
            <div id="wishlist" class="clearer">
                <ul>
                    <?php
                    if ($wishlist->items_in_cart) {
                        $errors = $wishlist->errors;
                        foreach ($wishlist->items_in_cart as $key => $value) {
                            $product = $inventory->getItem($key);
                            $is_in_stock = $product->quantity_in_stock? true: false; 
                            echo '<li><span class="title">' . $product->name . '</span>' . 
                                    '<a href="checkout.php?action=remove&target=wishlist&id=' . $product->id . '" title="Remove">Remove</a> '
                                    .($is_in_stock? '<a href="checkout.php?action=move&source=wishlist&target=cart&id=' . $product->id . '" title="Move to Cart">Move to Cart</a> ': '<span class="alert" id="outOfStock">Out of Stock</span>')
                                    . '<span class="price"> $' . number_format($value * $product->finalPrice(), 2) 
                                    .'</span></li>';
                            if (isset($errors['item' . $key])) {
                                echo '<p class="error">' . $errors['item' . $key] . '</p>';
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
      </div>
    </body>
</html>