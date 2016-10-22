<?php
// Turn on error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required classes
require_once('classes/Inventory.php');
require_once('classes/Cart.php');
require_once('classes/Product.php');

// Load json inventory from server
$inventory = new Inventory();
$cart = new Cart($inventory, 'cart', false);
$wishlist = new Cart($inventory, 'wishlist', true);
?>

<html>
    <head>
        <title>My Store</title>
        <link href="css/index.css" rel="stylesheet">
    <body>
        <h2 class="center"><span><a href="../../public/index.php">Back Home</a></span></h2>
        <div class="container">

            <div class="wrapper">
                <h1>My Store</h1>
                <div id="cart_preview">
                    <?php
                    if ($cart) {
                        $count = 0;
                        foreach ($cart->items_in_cart as $key => $value) {
                            $count += $cart->itemInCart($key);
                        }
                    }
                    echo '<p class="lead rightalign">' . $count . ' item' . ($count == 1 ? ' ' : 's ') . ' in Cart | $' . number_format($cart->getSubTotal(), 2) . '</p>';
                    ?>  
                    <p class="lead rightalign"><a href="checkout.php" title="Checkout">Checkout Now >></a></p> 
                    <p class="lead rightalign"><a href="wishlist.php" title="Wishlist">My Wishlist >></a></p>
                </div>

                <ul id="inventory_list" class="borderline">
                    <?php
                    // Display inventory items
                    $items = $inventory->getItems();
                    foreach ($items as $key => $value) {
                        $quant_in_cart = $cart->itemInCart($value->id);
                        $is_in_stock = $value->quantity_in_stock ? true : false;
                        $is_on_sale = $value->is_on_sale ? true : false;

                        $is_in_wishlist = $wishlist->itemInCart($value->id);

                        echo '<li><h3>' .
                        $value->name . ($is_in_stock ? '<span class="price">' .
                                ($is_on_sale ? '<s class="onsale">$' . number_format($value->price, 2) . '</s>' . '<span class="price"> OnSale $' . number_format($value->finalPrice(), 2) . '</span>' : '<span>$' . number_format($value->price, 2)) . '</span>' : '<span class="alert">Out of Stock</span>') . '</h3>' .
                        '<p class="italic_style">' . $value->description . '</p>' .
                        '<p>' . ($quant_in_cart ? ($quant_in_cart == 1 ? '1 item ' : $quant_in_cart . ' items ') . 'in Cart| <a href="checkout.php?action=remove&id=' . $value->id . '&target=cart" title="Remove from Cart">Remove from Cart</a>' : ($is_in_stock ? '<a href="checkout.php?action=add&id=' . $value->id . '&target=cart" title="Add to Cart">Add to Cart</a>' : '')) . '</p>
                         <p>' . ($is_in_wishlist ? 'In Wishlist | <a href="checkout.php?action=remove&id=' . $value->id . '&target=wishlist" title="Remove from Wishlist">Remove from Wishlist</a>' : ('<a href="checkout.php?action=add&id=' . $value->id . '&target=wishlist" title="Add to Wishlist">Add to Wishlist</a>')) . '</p>
                      </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </body>
</html>